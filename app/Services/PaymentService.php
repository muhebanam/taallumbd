<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Create or reuse pending order with optional coupon code
     */
    public function createOrder(User $user, Course $course, ?string $couponCode = null): Order
    {
        $order = Order::firstOrNew([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'pending',
        ]);

        $baseAmount = (float) $course->price;
        $discount = 0;
        $validCoupon = null;

        if ($couponCode) {
            $coupon = Coupon::where('code', strtoupper(trim($couponCode)))->first();
            if ($coupon && $coupon->isValidForAmount($baseAmount)) {
                $discount = $coupon->calculateDiscount($baseAmount);
                $validCoupon = $coupon->code;
            }
        }

        $order->amount = $baseAmount;
        $order->discount_amount = $discount;
        $order->coupon_code = $validCoupon;
        $order->payment_method = 'pending';
        $order->save();

        return $order;
    }

    /**
     * Confirm payment and enroll student
     */
    public function confirm(
        Order $order,
        string $method = 'mock',
        ?string $transactionId = null,
        ?string $senderPhone = null,
        ?array $rawResponse = null
    ): Enrollment {
        return DB::transaction(function () use ($order, $method, $transactionId, $senderPhone, $rawResponse) {
            $finalTrx = $transactionId ?: ('TXN-'.strtoupper(Str::random(10)));

            $order->update([
                'status' => 'paid',
                'payment_method' => $method,
                'sender_phone' => $senderPhone,
                'transaction_id' => $finalTrx,
            ]);

            // Increment coupon usage if used
            if ($order->coupon_code) {
                Coupon::where('code', $order->coupon_code)->increment('used_count');
            }

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $finalTrx,
                'payment_method' => $method,
                'amount' => $order->final_payable_amount,
                'status' => 'success',
                'raw_response' => $rawResponse ?? [
                    'gateway' => $method,
                    'verified_at' => now()->toIso8601String(),
                    'phone' => $senderPhone,
                    'trx_id' => $finalTrx,
                ],
            ]);

            return $this->enroll($order->user, $order->course);
        });
    }

    /**
     * Directly enroll student in a course
     */
    public function enroll(User $user, Course $course): Enrollment
    {
        return Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => 'active', 'progress' => 0, 'enrolled_at' => now()]
        );
    }
}
