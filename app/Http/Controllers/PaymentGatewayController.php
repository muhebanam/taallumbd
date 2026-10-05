<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Payments\PaymentGatewayManager;
use App\Services\CourseEnrollmentService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class PaymentGatewayController extends Controller
{
    public function __construct(
        protected PaymentGatewayManager $gatewayManager,
        protected PaymentService $paymentService,
        protected CourseEnrollmentService $enrollmentService
    ) {}

    /**
     * Initiate payment session for a given gateway and order.
     * Route: GET|POST /payments/{gateway}/init/{order}
     */
    public function initiate(Request $request, string $gateway, Order $order)
    {
        // 1. Gateway must be enabled (otherwise 404)
        abort_unless($this->gatewayManager->isGatewayEnabled($gateway), 404, 'নির্বাচিত পেমেন্ট গেটওয়ে বর্তমানে নিষ্ক্রিয় বা অনুপলব্ধ।');

        // 2. Authorization check
        $user = $request->user();
        abort_unless($user && ($order->user_id === $user->id || $user->role === 'admin'), 403);

        // 3. If order is already paid, redirect to invoice
        if ($order->status === 'paid') {
            return redirect()->route('orders.invoice', $order)->with('info', 'এই অর্ডারটি ইতোমধ্যে পরিশোধিত।');
        }

        // 4. Initiate payment with driver
        try {
            $driver = $this->gatewayManager->driver($gateway);
            $result = $driver->initiate($order);

            if (! empty($result['success']) && ! empty($result['redirect_url'])) {
                if ($request->wantsJson() || $request->header('X-Inertia')) {
                    return Inertia::location($result['redirect_url']);
                }

                return redirect()->away($result['redirect_url']);
            }

            return redirect()->route('checkout.show', $order->course->slug ?? $order->course_id)
                ->with('error', $result['message'] ?? 'পেমেন্ট গেটওয়ে সেশন তৈরিতে ত্রুটি ঘটেছে।');
        } catch (Throwable $e) {
            return redirect()->route('checkout.show', $order->course->slug ?? $order->course_id)
                ->with('error', 'পেমেন্ট সংযোগ ব্যর্থ হয়েছে: '.$e->getMessage());
        }
    }

    /**
     * Handle return callback from gateway (browser redirect or form POST).
     * Route: GET|POST /payments/{gateway}/callback/{order}/{status?}
     */
    public function callback(Request $request, string $gateway, Order $order, ?string $status = null)
    {
        // 1. Gateway must be enabled (otherwise 404)
        abort_unless($this->gatewayManager->isGatewayEnabled($gateway), 404, 'গেটওয়ে অনুপলব্ধ।');

        // Merge status from route param if present and not in query
        if ($status && ! $request->has('status')) {
            $request->merge(['status' => $status]);
        }

        try {
            $driver = $this->gatewayManager->driver($gateway);
            $result = $driver->handleCallback($request, $order);

            if (! empty($result['success'])) {
                return redirect()->route('orders.invoice', $order)
                    ->with('success', $result['message'] ?? 'পেমেন্ট সফলভাবে সম্পন্ন হয়েছে!');
            }

            return redirect()->route('checkout.show', $order->course->slug ?? $order->course_id)
                ->with('error', $result['message'] ?? 'পেমেন্ট সম্পন্ন হয়নি বা বাতিল করা হয়েছে।');
        } catch (Throwable $e) {
            return redirect()->route('checkout.show', $order->course->slug ?? $order->course_id)
                ->with('error', 'পেমেন্ট যাচাইয়ে সমস্যা: '.$e->getMessage());
        }
    }

    /**
     * Handle asynchronous IPN webhook from payment gateway.
     * Route: POST /webhooks/{gateway}/ipn
     */
    public function ipn(Request $request, string $gateway)
    {
        // Gateway must be enabled (otherwise 404)
        abort_unless($this->gatewayManager->isGatewayEnabled($gateway), 404, 'Gateway not enabled.');

        try {
            $driver = $this->gatewayManager->driver($gateway);
            $result = $driver->handleIpn($request);

            return response()->json($result, (! empty($result['success'])) ? 200 : 400);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
