<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use App\Payments\PaymentGatewayManager;
use App\Services\CourseEnrollmentService;
use App\Services\EventTracker;
use App\Services\FileUploadService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(
        private PaymentService $payments,
        private CourseEnrollmentService $enrollmentService
    ) {}

    public function show(Request $request, Course $course)
    {
        $user = $request->user();
        try {
            $this->enrollmentService->validateEnrollment($user, $course);
        } catch (\Exception $e) {
            if ($user->isEnrolled($course)) {
                return redirect()->route('student.courses.show', $course)->with('success', $e->getMessage());
            }

            return redirect()->route('courses.show', $course)->with('error', $e->getMessage());
        }

        $pendingOrder = Order::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', 'pending')
            ->first();

        $availableGateways = app(PaymentGatewayManager::class)->getAvailableGateways();

        try {
            app(EventTracker::class)->trackCheckoutStarted($user, $course->id, (float) $course->price);
        } catch (\Throwable $e) {
        }

        return Inertia::render('Payment/Checkout', [
            'course' => $course->load('instructor:id,name'),
            'pendingOrder' => $pendingOrder,
            'availableGateways' => $availableGateways,
        ]);
    }

    public function checkCoupon(Request $request, Course $course)
    {
        $code = strtoupper(trim($request->input('code', '')));
        $coupon = Coupon::where('code', $code)->first();

        $baseAmount = (float) $course->price;

        if (! $coupon || ! $coupon->isValidForAmount($baseAmount)) {
            return back()->withErrors(['coupon' => 'কুপন কোডটি অবৈধ বা মেয়াদোত্তীর্ণ।']);
        }

        $discount = $coupon->calculateDiscount($baseAmount);
        $finalAmount = max(0, $baseAmount - $discount);

        return back()->with([
            'coupon_success' => true,
            'coupon_code' => $coupon->code,
            'discount_amount' => $discount,
            'final_amount' => $finalAmount,
        ]);
    }

    public function process(Request $request, Course $course)
    {
        $user = $request->user();
        try {
            $this->enrollmentService->validateEnrollment($user, $course);
        } catch (\Exception $e) {
            if ($user->isEnrolled($course)) {
                return redirect()->route('student.courses.show', $course);
            }

            return redirect()->route('courses.show', $course)->with('error', $e->getMessage());
        }

        $couponCode = $request->input('coupon_code');
        $gateway = $request->input('gateway', 'manual');

        // Free course: enroll directly
        if ($course->is_free || (float) $course->price <= 0) {
            $this->payments->enroll($user, $course);

            return redirect()->route('student.courses.show', $course)
                ->with('success', 'কোর্সে সফলভাবে ভর্তি সম্পন্ন হয়েছে, আলহামদুলিল্লাহ!');
        }

        $order = $this->payments->createOrder($user, $course, $couponCode);

        // If coupon gave 100% discount
        if ($order->final_payable_amount <= 0) {
            $this->payments->confirm($order, 'free_coupon');

            return redirect()->route('student.courses.show', $course)
                ->with('success', 'কুপনের মাধ্যমে শতভাগ ছাড়ে কোর্সে ভর্তি সম্পন্ন হয়েছে!');
        }

        $gatewayManager = app(PaymentGatewayManager::class);
        if ($gateway !== 'manual' && $gateway !== 'mock' && $gatewayManager->isGatewayEnabled($gateway)) {
            return redirect()->route('payments.init', ['gateway' => $gateway, 'order' => $order->id]);
        }

        return redirect()->route('payment.mock', $order);
    }

    public function mockPayment(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id && in_array($order->status, ['pending', 'pending_verification']), 403);

        $defaultMethods = config('payments.gateways.manual.methods', []);
        $paymentSettings = [];

        foreach (['bkash', 'nagad', 'rocket'] as $method) {
            $def = $defaultMethods[$method] ?? [];
            $paymentSettings[$method] = [
                'enabled' => filter_var(Setting::get("payment_manual_{$method}_enabled", 'true'), FILTER_VALIDATE_BOOLEAN),
                'number' => Setting::get("payment_manual_{$method}_number", $def['number'] ?? '01700000000'),
                'type' => Setting::get("payment_manual_{$method}_type", $def['type'] ?? 'personal'),
                'instructions' => Setting::get("payment_manual_{$method}_instructions", $def['instructions'] ?? ''),
            ];
        }

        return Inertia::render('Payment/MockPayment', [
            'order' => $order->load([
                'course:id,title,slug,price,thumbnail',
                'user:id,name,email,phone',
            ]),
            'paymentSettings' => $paymentSettings,
            'isMockAllowed' => app()->environment('local', 'testing') || (bool) config('payments.mock_enabled'),
        ]);
    }

    public function submitManualPayment(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_if($order->status === 'paid', 400, 'এই অর্ডারটি ইতোমধ্যে পরিশোধিত।');

        $validated = $request->validate([
            'method' => 'required|string|in:bkash,nagad,rocket,manual_bkash,manual_nagad,manual_rocket',
            'sender_phone' => ['required', 'string', 'regex:/^(?:\+88|88)?01[3-9]\d{8}$/'],
            'transaction_id' => 'required|string|min:6|max:40|regex:/^[A-Za-z0-9]+$/',
            'screenshot' => 'nullable|file|image|mimes:jpeg,png,webp,jpg|max:3072',
        ], [
            'sender_phone.required' => 'প্রেরকের মোবাইল নম্বর প্রদান আবশ্যক।',
            'sender_phone.regex' => 'সঠিক বাংলাদেশি মোবাইল নম্বর প্রদান করুন (উদা: 017xxxxxxxx)।',
            'transaction_id.required' => 'ট্রানজ্যাকশন আইডি (TrxID) প্রদান আবশ্যক।',
            'transaction_id.regex' => 'TrxID শুধুমাত্র ইংরেজি বর্ণ ও সংখ্যা বিশিষ্ট হতে হবে।',
            'screenshot.max' => 'স্ক্রিনশটের আকার সর্বোচ্চ ৩ মেগাবাইট হতে পারবে।',
        ]);

        $trxId = strtoupper(trim($validated['transaction_id']));
        $phone = trim($validated['sender_phone']);
        $method = $validated['method'];

        // Prevent TrxID reuse across successful payments or existing pending verifications
        $alreadyUsedInPayments = Payment::where('transaction_id', $trxId)->where('status', 'success')->exists();
        $alreadyUsedInOrders = Order::where('transaction_id', $trxId)
            ->whereIn('status', ['paid', 'pending_verification'])
            ->where('id', '!=', $order->id)
            ->exists();

        if ($alreadyUsedInPayments || $alreadyUsedInOrders) {
            return back()->withErrors(['transaction_id' => 'এই ট্রানজ্যাকশন আইডি (TrxID) দিয়ে ইতোমধ্যে একটি পেমেন্ট সম্পন্ন বা অপেক্ষমাণ রয়েছে।']);
        }

        // Allow instant simulation ONLY in non-production if requested
        if (! app()->isProduction() && $request->boolean('instant_mock')) {
            $this->payments->confirm($order, $method, $trxId, $phone);

            return redirect()->route('student.courses.show', $order->course)
                ->with('success', 'পেমেন্ট সফলভাবে যাচাই হয়েছে! কোর্সে আজীবন প্রবেশাধিকার নিশ্চিত করা হলো।');
        }

        // Handle optional screenshot upload via FileUploadService
        $screenshotPath = $order->screenshot_path;
        if ($request->hasFile('screenshot')) {
            $screenshotPath = FileUploadService::storePrivateImage(
                $request->file('screenshot'),
                'payment_screenshots'
            );
        }

        // Production / Manual verification flow: save details for admin moderation
        $order->update([
            'status' => 'pending_verification',
            'payment_method' => $method,
            'sender_phone' => $phone,
            'transaction_id' => $trxId,
            'screenshot_path' => $screenshotPath,
        ]);

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => $method,
            'type' => 'manual_submit',
            'gateway_ref' => $trxId,
            'amount' => $order->final_payable_amount,
            'currency' => 'BDT',
            'status' => 'pending_verification',
            'payload' => [
                'sender_phone' => $phone,
                'screenshot_path' => $screenshotPath,
                'submitted_at' => now()->toIso8601String(),
                'ip' => $request->ip(),
            ],
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('invoice', $order)
            ->with('success', 'আপনার পেমেন্ট তথ্য সফলভাবে জমা হয়েছে! অ্যাডমিন ভেরিফিকেশন সম্পন্ন হলে (সর্বোচ্চ ১৫-৩০ মিনিটের মধ্যে) কোর্সটি সক্রিয় হয়ে যাবে।');
    }

    public function mockSuccess(Request $request, Order $order)
    {
        // 1. Mock payment only in local/testing or if mock_enabled; in production: 404
        if (! (app()->environment('local', 'testing') || config('payments.mock_enabled'))) {
            abort(404);
        }

        abort_unless($order->user_id === $request->user()->id && in_array($order->status, ['pending', 'pending_verification']), 403);

        $method = $request->input('method', 'bkash');
        $trxId = $request->input('transaction_id');
        $phone = $request->input('sender_phone', $request->user()->phone);

        $this->payments->confirm($order, $method, $trxId, $phone);

        return redirect()->route('student.courses.show', $order->course)
            ->with('success', 'পেমেন্ট সফলভাবে যাচাই হয়েছে! কোর্সে আজীবন প্রবেশাধিকার নিশ্চিত করা হলো।');
    }

    public function invoice(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->role === 'admin', 403);

        $order->load(['course.instructor:id,name', 'user:id,name,email,phone', 'payments']);

        return Inertia::render('Payment/Invoice', [
            'order' => $order,
        ]);
    }
}
