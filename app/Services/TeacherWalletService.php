<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\RevenueShare;
use App\Models\Teacher;
use App\Models\TeacherWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TeacherWalletService
{
    /**
     * Get or create wallet for a teacher or instructor user.
     */
    public function getOrCreateWallet(Teacher|User $entity): TeacherWallet
    {
        $teacher = null;
        $user = null;

        if ($entity instanceof Teacher) {
            $teacher = $entity;
            $user = $entity->user;
        } else {
            $user = $entity;
            $teacher = $entity->teacher;

            if (! $teacher) {
                // Ensure teacher profile exists for instructor
                $slug = Str::slug($user->name ?: 'instructor');
                $count = Teacher::where('slug', 'like', "{$slug}%")->count();
                if ($count > 0) {
                    $slug .= '-'.($count + 1);
                }

                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'name' => $user->name ?: 'Instructor',
                    'slug' => $slug,
                    'status' => 'active',
                ]);
            }
        }

        return TeacherWallet::firstOrCreate(
            ['teacher_id' => $teacher->id],
            [
                'user_id' => $user ? $user->id : $teacher->user_id,
                'balance' => 0,
                'pending_balance' => 0,
                'currency' => config('wallet.currency', 'BDT'),
            ]
        );
    }

    /**
     * Get active revenue share rates for a course and teacher.
     *
     * @return array{instructor_percentage: float, platform_percentage: float}
     */
    public function getRevenueShareRates(?Course $course = null, ?Teacher $teacher = null): array
    {
        // 1. Check Course-specific override
        if ($course) {
            $courseShare = RevenueShare::where('course_id', $course->id)
                ->where('is_active', true)
                ->first();

            if ($courseShare) {
                return [
                    'instructor_percentage' => (float) $courseShare->instructor_share_percentage,
                    'platform_percentage' => (float) $courseShare->platform_share_percentage,
                ];
            }
        }

        // 2. Check Teacher-specific override
        if ($teacher) {
            $teacherShare = RevenueShare::whereNull('course_id')
                ->where('teacher_id', $teacher->id)
                ->where('is_active', true)
                ->first();

            if ($teacherShare) {
                return [
                    'instructor_percentage' => (float) $teacherShare->instructor_share_percentage,
                    'platform_percentage' => (float) $teacherShare->platform_share_percentage,
                ];
            }
        }

        // 3. Fallback to Platform Default
        return [
            'instructor_percentage' => (float) config('wallet.default_instructor_percentage', 70.00),
            'platform_percentage' => (float) config('wallet.default_platform_percentage', 30.00),
        ];
    }

    /**
     * Credit pending revenue to instructor wallet when order payment is confirmed.
     */
    public function creditPendingRevenue(Order $order): ?WalletTransaction
    {
        if ($order->status !== 'paid') {
            return null;
        }

        $course = $order->course;
        if (! $course || ! $course->instructor_id) {
            return null;
        }

        $instructorUser = User::find($course->instructor_id);
        if (! $instructorUser) {
            return null;
        }

        $wallet = $this->getOrCreateWallet($instructorUser);

        // Convert gross payable amount to integer poisha
        $grossPoisha = (int) bcmul((string) $order->final_payable_amount, '100', 0);
        if ($grossPoisha <= 0) {
            return null;
        }

        // Check idempotency: avoid duplicate credit for same order
        $exists = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('type', 'credit')
            ->exists();

        if ($exists) {
            return null;
        }

        $teacher = $wallet->teacher;
        $rates = $this->getRevenueShareRates($course, $teacher);
        $instructorRate = (string) $rates['instructor_percentage'];

        // Exact integer poisha split calculation
        $instructorPoisha = (int) bcdiv(bcmul((string) $grossPoisha, $instructorRate, 2), '100', 0);
        $platformPoisha = $grossPoisha - $instructorPoisha;

        $holdDays = (int) config('wallet.hold_days', 7);
        $holdUntil = now()->addDays($holdDays);

        return DB::transaction(function () use ($wallet, $instructorPoisha, $grossPoisha, $platformPoisha, $instructorRate, $order, $course, $holdUntil) {
            $lockedWallet = TeacherWallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            $lockedWallet->pending_balance = (int) bcadd((string) $lockedWallet->pending_balance, (string) $instructorPoisha, 0);
            $lockedWallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $lockedWallet->id,
                'type' => 'credit',
                'balance_type' => 'pending',
                'amount' => $instructorPoisha,
                'balance_after' => $lockedWallet->balance, // available balance remains unchanged during hold
                'reference_type' => 'order',
                'reference_id' => $order->id,
                'description' => "কোর্স বিক্রয় কমিশন: {$course->title} (অর্ডার #{$order->id})",
                'metadata' => [
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'gross_amount_poisha' => $grossPoisha,
                    'gross_amount_bdt' => $order->final_payable_amount,
                    'instructor_share_percentage' => (float) $instructorRate,
                    'instructor_amount_poisha' => $instructorPoisha,
                    'platform_fee_poisha' => $platformPoisha,
                ],
                'hold_until' => $holdUntil,
                'matured_at' => null,
            ]);

            AuditLoggerService::log(
                action: 'wallet.revenue_credited',
                modelType: TeacherWallet::class,
                modelId: $lockedWallet->id,
                payload: [
                    'order_id' => $order->id,
                    'instructor_poisha' => $instructorPoisha,
                    'gross_poisha' => $grossPoisha,
                    'rate' => $instructorRate,
                    'hold_until' => $holdUntil->toIso8601String(),
                ]
            );

            return $transaction;
        });
    }

    /**
     * Mature and release pending balances whose hold window has elapsed.
     */
    public function releaseMaturedPendingBalances(): int
    {
        $now = now();
        $pendingTransactions = WalletTransaction::where('balance_type', 'pending')
            ->where('type', 'credit')
            ->whereNotNull('hold_until')
            ->where('hold_until', '<=', $now)
            ->whereNull('matured_at')
            ->get();

        $releasedCount = 0;

        foreach ($pendingTransactions as $pendingTxn) {
            DB::transaction(function () use ($pendingTxn, &$releasedCount) {
                $lockedTxn = WalletTransaction::where('id', $pendingTxn->id)->lockForUpdate()->firstOrFail();
                if ($lockedTxn->matured_at !== null) {
                    return;
                }

                $wallet = TeacherWallet::where('id', $lockedTxn->wallet_id)->lockForUpdate()->firstOrFail();

                // Deduct from pending, credit to available balance
                $amount = $lockedTxn->amount;
                $wallet->pending_balance = max(0, (int) bcsub((string) $wallet->pending_balance, (string) $amount, 0));
                $wallet->balance = (int) bcadd((string) $wallet->balance, (string) $amount, 0);
                $wallet->save();

                // Create available balance credit ledger transaction
                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'balance_type' => 'available',
                    'amount' => $amount,
                    'balance_after' => $wallet->balance,
                    'reference_type' => 'matured_release',
                    'reference_id' => $lockedTxn->id,
                    'description' => "স্থগিত ব্যালেন্স অবমুক্তকরণ: {$lockedTxn->description}",
                    'metadata' => [
                        'original_transaction_id' => $lockedTxn->id,
                        'order_id' => $lockedTxn->reference_id,
                    ],
                    'hold_until' => null,
                    'matured_at' => now(),
                ]);

                $lockedTxn->update(['matured_at' => now()]);

                AuditLoggerService::log(
                    action: 'wallet.balance_matured',
                    modelType: TeacherWallet::class,
                    modelId: $wallet->id,
                    payload: [
                        'matured_amount_poisha' => $amount,
                        'new_balance_poisha' => $wallet->balance,
                        'original_transaction_id' => $lockedTxn->id,
                    ]
                );

                $releasedCount++;
            });
        }

        return $releasedCount;
    }

    /**
     * Reverse earnings when an order is refunded.
     */
    public function reverseRefund(Order $order, string $reason = ''): ?WalletTransaction
    {
        $origCredit = WalletTransaction::where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('type', 'credit')
            ->first();

        if (! $origCredit) {
            return null;
        }

        // Avoid double reversal
        $alreadyReversed = WalletTransaction::where('wallet_id', $origCredit->wallet_id)
            ->where('reference_type', 'refund_reversal')
            ->where('reference_id', $order->id)
            ->exists();

        if ($alreadyReversed) {
            return null;
        }

        return DB::transaction(function () use ($origCredit, $order, $reason) {
            $wallet = TeacherWallet::where('id', $origCredit->wallet_id)->lockForUpdate()->firstOrFail();
            $refundPoisha = $origCredit->amount;

            // Determine if funds were already released or still in pending
            if ($origCredit->matured_at !== null) {
                // Deduct from available balance
                $wallet->balance = max(0, (int) bcsub((string) $wallet->balance, (string) $refundPoisha, 0));
                $balanceType = 'available';
            } else {
                // Deduct from pending balance
                $wallet->pending_balance = max(0, (int) bcsub((string) $wallet->pending_balance, (string) $refundPoisha, 0));
                $balanceType = 'pending';
                // Invalidate hold so it won't be released later
                $origCredit->update(['matured_at' => now()]);
            }

            $wallet->save();

            $reversal = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'balance_type' => $balanceType,
                'amount' => $refundPoisha,
                'balance_after' => $wallet->balance,
                'reference_type' => 'refund_reversal',
                'reference_id' => $order->id,
                'description' => "রিফান্ড কর্তন: অর্ডার #{$order->id}".($reason ? " ({$reason})" : ''),
                'metadata' => [
                    'order_id' => $order->id,
                    'refund_reason' => $reason,
                    'original_transaction_id' => $origCredit->id,
                    'original_amount_poisha' => $refundPoisha,
                ],
            ]);

            AuditLoggerService::log(
                action: 'wallet.refund_reversed',
                modelType: TeacherWallet::class,
                modelId: $wallet->id,
                payload: [
                    'order_id' => $order->id,
                    'reversed_poisha' => $refundPoisha,
                    'balance_type' => $balanceType,
                    'reason' => $reason,
                ]
            );

            return $reversal;
        });
    }

    /**
     * Submit payout withdrawal request with strict balance and concurrency checks.
     */
    public function requestPayout(
        Teacher $teacher,
        int $amountPoisha,
        string $method,
        array $accountInfo,
        ?string $notes = null
    ): PayoutRequest {
        $minPoisha = (int) config('wallet.min_payout_amount_poisha', 50000);
        if ($amountPoisha < $minPoisha) {
            $minBdt = bcdiv((string) $minPoisha, '100', 0);
            throw new \InvalidArgumentException("সর্বনিম্ন পেআউট উত্তোলনের পরিমাণ {$minBdt} টাকা।");
        }

        return DB::transaction(function () use ($teacher, $amountPoisha, $method, $accountInfo, $notes) {
            $wallet = TeacherWallet::where('teacher_id', $teacher->id)->lockForUpdate()->firstOrFail();

            if ($wallet->balance < $amountPoisha) {
                $availableBdt = $wallet->balance_bdt;
                throw new \RuntimeException("ওয়ালেটে পর্যাপ্ত ব্যালেন্স নেই। আপনার বর্তমান উত্তোলনের যোগ্য ব্যালেন্স: {$availableBdt} ৳");
            }

            // Immediately deduct from available balance into locked payout status
            $wallet->balance = (int) bcsub((string) $wallet->balance, (string) $amountPoisha, 0);
            $wallet->save();

            $payout = PayoutRequest::create([
                'teacher_id' => $teacher->id,
                'user_id' => $teacher->user_id,
                'wallet_id' => $wallet->id,
                'amount' => $amountPoisha,
                'method' => $method,
                'account_info' => $accountInfo,
                'status' => 'pending',
                'requested_at' => now(),
                'notes' => $notes,
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'balance_type' => 'available',
                'amount' => $amountPoisha,
                'balance_after' => $wallet->balance,
                'reference_type' => 'payout_request',
                'reference_id' => $payout->id,
                'description' => 'পেআউট উত্তোলন অনুরোধ ('.strtoupper($method).')',
                'metadata' => [
                    'payout_id' => $payout->id,
                    'method' => $method,
                ],
            ]);

            AuditLoggerService::log(
                action: 'payout.requested',
                modelType: PayoutRequest::class,
                modelId: $payout->id,
                payload: [
                    'teacher_id' => $teacher->id,
                    'amount_poisha' => $amountPoisha,
                    'method' => $method,
                ]
            );

            return $payout;
        });
    }

    /**
     * Admin approves and marks payout request as paid.
     */
    public function approvePayout(
        PayoutRequest $payout,
        User $admin,
        ?string $transactionReference = null,
        ?string $notes = null
    ): PayoutRequest {
        return DB::transaction(function () use ($payout, $admin, $transactionReference, $notes) {
            $locked = PayoutRequest::where('id', $payout->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, ['pending', 'processing'])) {
                throw new \RuntimeException('এই পেআউট অনুরোধটি ইতোমধ্যে সম্পন্ন বা বাতিল করা হয়েছে।');
            }

            $locked->update([
                'status' => 'paid',
                'transaction_reference' => $transactionReference,
                'processed_by' => $admin->id,
                'processed_at' => now(),
                'notes' => $notes ?: $locked->notes,
            ]);

            AuditLoggerService::log(
                action: 'payout.approved',
                modelType: PayoutRequest::class,
                modelId: $locked->id,
                payload: [
                    'admin_id' => $admin->id,
                    'transaction_reference' => $transactionReference,
                    'amount_poisha' => $locked->amount,
                ]
            );

            return $locked;
        });
    }

    /**
     * Admin rejects payout request and restores funds back to teacher wallet.
     */
    public function rejectPayout(PayoutRequest $payout, User $admin, string $reason): PayoutRequest
    {
        return DB::transaction(function () use ($payout, $admin, $reason) {
            $locked = PayoutRequest::where('id', $payout->id)->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, ['pending', 'processing'])) {
                throw new \RuntimeException('এই পেআউট অনুরোধটি বাতিল করার যোগ্য অবস্থায় নেই।');
            }

            $wallet = TeacherWallet::where('id', $locked->wallet_id)->lockForUpdate()->firstOrFail();

            // Refund amount back to available balance
            $wallet->balance = (int) bcadd((string) $wallet->balance, (string) $locked->amount, 0);
            $wallet->save();

            $locked->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'balance_type' => 'available',
                'amount' => $locked->amount,
                'balance_after' => $wallet->balance,
                'reference_type' => 'payout_rejected',
                'reference_id' => $locked->id,
                'description' => "পেআউট বাতিল ও ব্যালেন্স ফেরত: {$reason}",
                'metadata' => [
                    'payout_id' => $locked->id,
                    'reason' => $reason,
                ],
            ]);

            AuditLoggerService::log(
                action: 'payout.rejected',
                modelType: PayoutRequest::class,
                modelId: $locked->id,
                payload: [
                    'admin_id' => $admin->id,
                    'rejection_reason' => $reason,
                    'refunded_poisha' => $locked->amount,
                ]
            );

            return $locked;
        });
    }

    /**
     * Reverse instructor revenue share when an order is refunded.
     */
    public function reverseRevenueForRefund(Order $order, string $reason = 'অর্ডার রিফান্ড'): ?WalletTransaction
    {
        $creditTx = WalletTransaction::where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->where('type', 'credit')
            ->first();

        if (! $creditTx) {
            return null;
        }

        // Avoid double-reversal
        $reversed = WalletTransaction::where('reference_type', 'order_refund')
            ->where('reference_id', $order->id)
            ->where('type', 'debit')
            ->exists();

        if ($reversed) {
            return null;
        }

        return DB::transaction(function () use ($creditTx, $order, $reason) {
            $wallet = TeacherWallet::where('id', $creditTx->wallet_id)->lockForUpdate()->firstOrFail();
            $amountToDeduct = $creditTx->amount;

            // Check if credit was matured or still in pending_balance
            $wasMatured = $creditTx->matured_at !== null || ($creditTx->hold_until && $creditTx->hold_until->isPast());

            if ($wasMatured) {
                $wallet->balance = (int) bcsub((string) $wallet->balance, (string) $amountToDeduct, 0);
                $balanceAfter = $wallet->balance;
                $balanceType = 'available';
            } else {
                $wallet->pending_balance = (int) bcsub((string) $wallet->pending_balance, (string) $amountToDeduct, 0);
                if ($wallet->pending_balance < 0) {
                    $wallet->pending_balance = 0;
                }
                $balanceAfter = $wallet->pending_balance;
                $balanceType = 'pending';
            }

            $wallet->save();

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'debit',
                'balance_type' => $balanceType,
                'amount' => $amountToDeduct,
                'balance_after' => $balanceAfter,
                'reference_type' => 'order_refund',
                'reference_id' => $order->id,
                'description' => "অর্ডার #{$order->id} রিফান্ড বাবদ রেভিনিউ সমন্বয়: {$reason}",
                'metadata' => [
                    'order_id' => $order->id,
                    'original_credit_id' => $creditTx->id,
                    'reason' => $reason,
                ],
            ]);
        });
    }
}
