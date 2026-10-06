<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    /**
     * Confirm payment and enroll student (hardened with lockForUpdate and idempotency)
     */
    public function markAsPaid(Order $order): ?Enrollment
    {
        return $this->confirm(
            $order,
            $order->payment_method ?: 'mock',
            $order->transaction_id,
            $order->sender_phone
        );
    }

    public function confirm(
        Order $order,
        string $method = 'mock',
        ?string $transactionId = null,
        ?string $senderPhone = null,
        ?array $rawResponse = null
    ): ?Enrollment {
        $alreadyPaid = $order->status === 'paid';
        $enrollment = DB::transaction(function () use ($order, $method, $transactionId, $senderPhone, $rawResponse) {
            // Lock order for update to prevent race conditions
            $lockedOrder = Order::where('id', $order->id)->lockForUpdate()->firstOrFail();

            // Idempotency: if already paid, return existing enrollment immediately
            if ($lockedOrder->status === 'paid') {
                $existing = Enrollment::where('user_id', $lockedOrder->user_id)
                    ->where('course_id', $lockedOrder->course_id)
                    ->first();

                if ($existing) {
                    return $existing;
                }
            }

            $finalTrx = $transactionId ?: ('TXN-'.strtoupper(Str::random(10)));

            // Prevent duplicate transaction ID usage
            $trxExists = Payment::where('transaction_id', $finalTrx)
                ->where('status', 'success')
                ->lockForUpdate()
                ->exists();

            if ($trxExists) {
                throw new \RuntimeException('এই ট্রানজ্যাকশন আইডি (TrxID) দিয়ে ইতোমধ্যে একটি পেমেন্ট সম্পন্ন হয়েছে।');
            }

            // Lock and validate coupon limit if used
            if ($lockedOrder->coupon_code) {
                $coupon = Coupon::where('code', $lockedOrder->coupon_code)->lockForUpdate()->first();
                if ($coupon) {
                    if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
                        throw new \RuntimeException('এই কুপনের সর্বোচ্চ ব্যবহারের সীমা শেষ হয়ে গেছে।');
                    }
                    $coupon->increment('used_count');
                }
            }

            $lockedOrder->update([
                'status' => 'paid',
                'payment_method' => $method,
                'sender_phone' => $senderPhone,
                'transaction_id' => $finalTrx,
            ]);

            Payment::create([
                'order_id' => $lockedOrder->id,
                'transaction_id' => $finalTrx,
                'payment_method' => $method,
                'amount' => $lockedOrder->final_payable_amount,
                'status' => 'success',
                'raw_response' => $rawResponse ?? [
                    'gateway' => $method,
                    'verified_at' => now()->toIso8601String(),
                    'phone' => $senderPhone,
                    'trx_id' => $finalTrx,
                ],
            ]);

            // Record transaction event
            PaymentTransaction::create([
                'order_id' => $lockedOrder->id,
                'gateway' => $method,
                'type' => 'verify',
                'gateway_ref' => $finalTrx,
                'amount' => $lockedOrder->final_payable_amount,
                'currency' => 'BDT',
                'status' => 'success',
                'payload' => [
                    'method' => $method,
                    'phone' => $senderPhone,
                    'verified_at' => now()->toIso8601String(),
                ],
                'ip_address' => request()->ip(),
            ]);

            // Audit log
            AuditLoggerService::log(
                action: 'order.payment_confirmed',
                modelType: Order::class,
                modelId: $lockedOrder->id,
                payload: [
                    'method' => $method,
                    'transaction_id' => $finalTrx,
                    'amount' => $lockedOrder->final_payable_amount,
                    'user_id' => $lockedOrder->user_id,
                    'course_id' => $lockedOrder->course_id,
                ]
            );

            try {
                app(EventTracker::class)->trackCheckoutCompleted(
                    $lockedOrder->user,
                    $lockedOrder->id,
                    $lockedOrder->course_id,
                    (float) $lockedOrder->final_payable_amount,
                    $method
                );
            } catch (\Throwable $e) {
                // Non-blocking tracking
            }

            if ($lockedOrder->order_type === 'scholar_session' || $lockedOrder->live_class_id) {
                app(ScholarSessionService::class)->completePaidRegistration($lockedOrder);

                return null;
            }

            // Phase 9: Credit instructor pending revenue in wallet
            try {
                app(TeacherWalletService::class)->creditPendingRevenue($lockedOrder);
            } catch (\Throwable $e) {
                Log::error("Teacher revenue credit failed for order #{$lockedOrder->id}: {$e->getMessage()}");
            }

            return $lockedOrder->course ? $this->enroll($lockedOrder->user, $lockedOrder->course) : null;
        });

        if (! $alreadyPaid && $order->course_id) {
            app(NotificationDispatcher::class)->paymentVerified($order->fresh(['user', 'course']));
        }

        return $enrollment;
    }

    /**
     * Directly enroll student in a course
     */
    public function enroll(User $user, Course $course): Enrollment
    {
        $existing = Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->first();
        if ($existing) {
            return $existing;
        }

        $enrollment = Enrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress' => 0,
            'enrolled_at' => now(),
        ]);

        try {
            app(EventTracker::class)->trackCourseEnrolled($user, $course->id);
        } catch (\Throwable $e) {
            // Non-blocking tracking
        }

        app(NotificationDispatcher::class)->enrollment($enrollment->load(['user', 'course']));

        return $enrollment;
    }
}
