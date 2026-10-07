<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RefundService
{
    public function __construct(
        protected TeacherWalletService $teacherWalletService,
        protected NotificationDispatcher $notificationDispatcher
    ) {}

    /**
     * Process a full refund for an order adhering to business policies.
     *
     * @param  Order  $order
     * @param  string  $reason
     * @param  User|null  $adminUser
     * @param  bool  $force  Admin override to bypass window & progress limits
     * @return Order
     *
     * @throws InvalidArgumentException
     */
    public function processRefund(Order $order, string $reason = 'শিক্ষার্থীর অনুরোধে রিফান্ড', ?User $adminUser = null, bool $force = false): Order
    {
        if ($order->status !== 'paid') {
            throw new InvalidArgumentException("শুধুমাত্র পরিশোধিত (paid) অর্ডার রিফান্ড করা সম্ভব। বর্তমান স্ট্যাটাস: {$order->status}");
        }

        // 1. Business Policy: Refund Window (Default: 7 Days)
        $windowDays = (int) config('payments.refund_window_days', 7);
        if (! $force && $order->created_at && $order->created_at->diffInDays(now()) > $windowDays) {
            throw new InvalidArgumentException("রিফান্ডের সময়সীমা ({$windowDays} দিন) উত্তীর্ণ হয়েছে। অর্ডারটি তৈরি হয়েছিল: " . $order->created_at->format('Y-m-d'));
        }

        // 2. Business Policy: Progress Threshold (Default: Max 20% course completed)
        if (! $force && $order->course_id && $order->user_id) {
            $enrollment = Enrollment::where('user_id', $order->user_id)
                ->where('course_id', $order->course_id)
                ->first();

            $maxProgress = (float) config('payments.refund_max_progress_percentage', 20.0);
            if ($enrollment && (float) $enrollment->progress > $maxProgress) {
                throw new InvalidArgumentException("কোর্সের {$maxProgress}%-এর বেশি সম্পন্ন করায় এই অর্ডারে রিফান্ড নীতিমালা প্রযোজ্য নয়। বর্তমান অগ্রগতি: {$enrollment->progress}%");
            }
        }

        return DB::transaction(function () use ($order, $reason, $adminUser) {
            // 1. Update Order status
            $oldStatus = $order->status;
            $order->status = 'refunded';
            $order->save();

            // 2. Revoke student Enrollment
            if ($order->course_id && $order->user_id) {
                Enrollment::where('user_id', $order->user_id)
                    ->where('course_id', $order->course_id)
                    ->update(['status' => 'cancelled']);
            }

            // 3. Reconcile / Deduct instructor revenue share
            $walletReversalTx = $this->teacherWalletService->reverseRevenueForRefund($order, $reason);

            // 4. Record Payment Transaction for Audit
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => $order->payment_method ?? 'manual',
                'transaction_id' => 'REFUND-' . ($order->transaction_id ?? $order->id) . '-' . time(),
                'type' => 'refund',
                'status' => 'success',
                'amount' => $order->amount,
                'currency' => $order->currency ?? 'BDT',
                'metadata' => [
                    'reason' => $reason,
                    'processed_by' => $adminUser ? $adminUser->id : null,
                    'wallet_reversal_id' => $walletReversalTx?->id,
                ],
            ]);

            // 5. System Audit Log
            AuditLoggerService::log(
                action: 'order.refunded',
                modelType: Order::class,
                modelId: $order->id,
                payload: [
                    'old_status' => $oldStatus,
                    'new_status' => 'refunded',
                    'refund_amount' => $order->amount,
                    'reason' => $reason,
                    'admin_id' => $adminUser?->id,
                ],
                userId: $adminUser?->id
            );

            // 6. Notify Student
            if ($order->user) {
                try {
                    $this->notificationDispatcher->send(
                        user: $order->user,
                        type: 'payment.refunded',
                        title: 'অর্ডার রিফান্ড সম্পন্ন হয়েছে',
                        message: "আপনার অর্ডার #{$order->id}-এর কোর্স ফি ({$order->amount} {$order->currency}) রিফান্ড করা হয়েছে। কারণ: {$reason}",
                        actionUrl: route('orders.invoice', $order)
                    );
                } catch (\Throwable $e) {
                    // Avoid failing transaction if notification delivery encounters issues
                }
            }

            return $order;
        });
    }
}
