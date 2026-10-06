<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Payments\PaymentGatewayManager;
use App\Services\CourseEnrollmentService;
use App\Services\EventTracker;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected CourseEnrollmentService $enrollmentService
    ) {}

    /**
     * Initiate payment / order for a course.
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'coupon_code' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|in:bkash,nagad,rocket,sslcommerz,manual,free',
        ]);

        $user = $request->user();
        $course = Course::findOrFail($request->course_id);

        // Check if already enrolled
        if ($user->isEnrolledIn($course->id)) {
            return response()->json([
                'success' => false,
                'message' => 'আপনি ইতোমধ্যে এই কোর্সে এনরোল করেছেন।',
            ], 400);
        }

        // Free course instant enrollment
        if ($course->is_free || (float) $course->price <= 0) {
            $enrollment = $this->enrollmentService->enrollFree($user, $course);

            return response()->json([
                'success' => true,
                'message' => 'ফ্রি কোর্সে আপনার এনরোলমেন্ট সফলভাবে সম্পন্ন হয়েছে।',
                'is_free' => true,
                'status' => 'completed',
                'enrollment_id' => $enrollment->id,
            ]);
        }

        $couponCode = $request->input('coupon_code');
        $order = $this->paymentService->createOrder($user, $course, $couponCode);

        // If coupon makes order 100% free
        if ((float) $order->final_payable_amount <= 0) {
            $enrollment = $this->paymentService->confirm($order, 'coupon_free');

            return response()->json([
                'success' => true,
                'message' => '১০০% কুপন ডিসকাউন্টে এনরোলমেন্ট সফল হয়েছে।',
                'is_free' => true,
                'status' => 'completed',
                'enrollment_id' => $enrollment->id,
            ]);
        }

        $method = $request->input('payment_method', 'manual');
        $availableGateways = app(PaymentGatewayManager::class)->getAvailableGateways();

        try {
            app(EventTracker::class)->trackCheckoutStarted($user, $course->id, (float) $order->final_payable_amount);
        } catch (\Throwable $e) {
            // Non-blocking
        }

        $manualConfig = [
            'bkash' => [
                'number' => Setting::get('bkash_number', config('payments.gateways.bkash.manual_number', '01700000000')),
                'type' => Setting::get('bkash_type', config('payments.gateways.bkash.manual_type', 'personal')),
            ],
            'nagad' => [
                'number' => Setting::get('nagad_number', config('payments.gateways.nagad.manual_number', '01700000000')),
                'type' => Setting::get('nagad_type', config('payments.gateways.nagad.manual_type', 'personal')),
            ],
            'rocket' => [
                'number' => Setting::get('rocket_number', config('payments.gateways.rocket.manual_number', '01700000000')),
                'type' => Setting::get('rocket_type', config('payments.gateways.rocket.manual_type', 'personal')),
            ],
        ];

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'order_number' => $order->order_number ?? ('ORD-'.$order->id),
            'course_title' => $course->title,
            'base_amount' => (float) $order->amount,
            'amount' => (float) $order->final_payable_amount,
            'discount_amount' => (float) ($order->discount_amount ?? 0),
            'payable_amount' => (float) $order->final_payable_amount,
            'currency' => 'BDT',
            'gateway' => $method,
            'gateway_url' => url("/checkout/{$course->id}"),
            'status' => $order->status,
            'available_gateways' => $availableGateways,
            'manual_payment_info' => $manualConfig,
            'redirect_url' => url("/checkout/{$course->id}"),
        ]);
    }

    /**
     * Submit manual payment transaction ID and sender details.
     */
    public function submitManual(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|in:bkash,nagad,rocket,bank',
            'sender_phone' => 'required|string|max:30',
            'transaction_id' => 'required|string|max:100',
        ]);

        $user = $request->user();
        $order = Order::where('id', $request->order_id)->where('user_id', $user->id)->firstOrFail();

        if ($order->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'এই অর্ডারটি ইতিমধ্যে পরিশোধিত হয়েছে।',
            ], 400);
        }

        $trx = strtoupper(trim($request->transaction_id));

        $exists = Payment::where('transaction_id', $trx)->where('status', 'success')->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'এই ট্রানজ্যাকশন আইডি (TrxID) দিয়ে ইতোমধ্যে একটি সফল পেমেন্ট সম্পন্ন হয়েছে।',
            ], 422);
        }

        $order->update([
            'payment_method' => $request->payment_method,
            'sender_phone' => $request->sender_phone,
            'transaction_id' => $trx,
            'status' => 'pending',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => $request->payment_method,
            'amount' => $order->final_payable_amount,
            'transaction_id' => $trx,
            'status' => 'pending',
            'payment_type' => 'manual',
            'raw_response' => [
                'sender_phone' => $request->sender_phone,
                'method' => $request->payment_method,
                'submitted_via' => 'api_v1',
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'আপনার পেমেন্ট তথ্য গৃহীত হয়েছে। অ্যাডমিন ভেরিফিকেশনের পর কোর্সটি সক্রিয় হবে।',
            'order_id' => $order->id,
            'status' => 'pending_verification',
        ]);
    }

    /**
     * Check order / payment verification status.
     */
    public function status(Request $request, int $orderId): JsonResponse
    {
        $user = $request->user();
        $order = Order::where('id', $orderId)->where('user_id', $user->id)->with('course')->firstOrFail();

        $isEnrolled = $user->isEnrolledIn($order->course_id);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'course_id' => $order->course_id,
            'course_title' => $order->course?->title,
            'status' => $order->status,
            'is_paid' => $order->status === 'paid',
            'is_enrolled' => $isEnrolled,
            'amount' => (float) $order->final_payable_amount,
            'payment_method' => $order->payment_method,
            'transaction_id' => $order->transaction_id,
            'created_at' => $order->created_at?->toIso8601String(),
        ]);
    }
}
