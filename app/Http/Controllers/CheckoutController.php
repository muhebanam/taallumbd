<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\Payment;
use App\Services\CourseEnrollmentService;
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

        return Inertia::render('Payment/Checkout', [
            'course' => $course->load('instructor:id,name'),
            'pendingOrder' => $pendingOrder,
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

        return redirect()->route('payment.mock', $order);
    }

    public function mockPayment(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id && $order->status === 'pending', 403);

        return Inertia::render('Payment/MockPayment', [
            'order' => $order->load([
                'course:id,title,slug,price,thumbnail',
                'user:id,name,email,phone',
            ]),
        ]);
    }

    public function submitManualPayment(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_if($order->status === 'paid', 400, 'এই অর্ডারটি ইতোমধ্যে পরিশোধিত।');

        $validated = $request->validate([
            'method' => 'required|string|in:bkash,nagad,rocket,manual_bkash,manual_nagad,manual_rocket',
            'sender_phone' => 'required|string|min:11|max:15',
            'transaction_id' => 'required|string|min:6|max:40',
        ], [
            'sender_phone.required' => 'প্রেরকের মোবাইল নম্বর প্রদান আবশ্যক।',
            'transaction_id.required' => 'ট্রানজ্যাকশন আইডি (TrxID) প্রদান আবশ্যক।',
        ]);

        $trxId = strtoupper(trim($validated['transaction_id']));
        $phone = trim($validated['sender_phone']);
        $method = $validated['method'];

        // Prevent TrxID reuse
        $alreadyUsed = Payment::where('transaction_id', $trxId)->where('status', 'success')->exists();
        if ($alreadyUsed) {
            return back()->withErrors(['transaction_id' => 'এই ট্রানজ্যাকশন আইডি (TrxID) দিয়ে ইতোমধ্যে একটি পেমেন্ট সম্পন্ন হয়েছে।']);
        }

        // Allow instant simulation ONLY in non-production if requested
        if (! app()->isProduction() && $request->boolean('instant_mock')) {
            $this->payments->confirm($order, $method, $trxId, $phone);

            return redirect()->route('student.courses.show', $order->course)
                ->with('success', 'পেমেন্ট সফলভাবে যাচাই হয়েছে! কোর্সে আজীবন প্রবেশাধিকার নিশ্চিত করা হলো।');
        }

        // Production / Manual verification flow: save details for admin moderation
        $order->update([
            'payment_method' => $method,
            'sender_phone' => $phone,
            'transaction_id' => $trxId,
        ]);

        return redirect()->route('invoice', $order)
            ->with('success', 'আপনার পেমেন্ট তথ্য সফলভাবে জমা হয়েছে! অ্যাডমিন ভেরিফিকেশন সম্পন্ন হলে (সর্বোচ্চ ১৫-৩০ মিনিটের মধ্যে) কোর্সটি সক্রিয় হয়ে যাবে।');
    }

    public function mockSuccess(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id && $order->status === 'pending', 403);

        // Security safeguard: never allow open instant mock confirmation in production
        if (app()->isProduction()) {
            abort(403, 'প্রোডাকশনে সরাসরি মক পেমেন্ট অনুমোদন নিষ্ক্রিয়। অনুগ্রহ করে মোবাইল নম্বর ও TrxID প্রদান করুন।');
        }

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
