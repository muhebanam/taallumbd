<?php

namespace App\Services;

use App\Models\LiveClass;
use App\Models\Order;
use App\Models\ScholarSessionRegistration;
use App\Models\TeacherWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScholarSessionService
{
    public function __construct(
        protected NotificationDispatcher $dispatcher,
        protected TeacherWalletService $walletService
    ) {}

    /**
     * Create a new scholar session (live class / webinar / 1-on-1 consultation).
     */
    public function createSession(User $instructor, array $data): LiveClass
    {
        $data['instructor_id'] = $instructor->id;
        $data['registered_count'] = 0;
        $data['status'] = $data['status'] ?? 'scheduled';

        $session = LiveClass::create($data);

        AuditLoggerService::log(
            action: 'scholar_session.created',
            modelType: LiveClass::class,
            modelId: $session->id,
            payload: [
                'title' => $session->title,
                'session_type' => $session->session_type,
                'fee' => $session->fee,
                'instructor_id' => $instructor->id,
            ]
        );

        return $session;
    }

    /**
     * Register a student for a free session.
     */
    public function registerFree(User $student, LiveClass $session, ?string $notes = null): ScholarSessionRegistration
    {
        if (! $session->isFree()) {
            throw new \InvalidArgumentException('এই সেশনটিতে অংশগ্রহণের জন্য পেমেন্ট বুকিং সম্পন্ন করতে হবে।');
        }

        if ($session->max_participants && $session->registered_count >= $session->max_participants) {
            throw new \RuntimeException('দুঃখিত, এই সেশনের সর্বোচ্চ আসন পূরণ হয়ে গেছে।');
        }

        $registration = ScholarSessionRegistration::firstOrCreate(
            [
                'live_class_id' => $session->id,
                'user_id' => $student->id,
            ],
            [
                'status' => 'registered',
                'fee_paid' => 0,
                'booking_notes' => $notes,
            ]
        );

        $session->increment('registered_count');

        AuditLoggerService::log(
            action: 'scholar_session.registered_free',
            modelType: ScholarSessionRegistration::class,
            modelId: $registration->id,
            payload: [
                'session_id' => $session->id,
                'user_id' => $student->id,
            ]
        );

        return $registration;
    }

    /**
     * Initiate booking order for a paid session or consultation.
     */
    public function createBookingOrder(User $student, LiveClass $session, ?string $notes = null): Order
    {
        if ($session->isFree()) {
            throw new \InvalidArgumentException('এটি একটি উন্মুক্ত ফ্রি সেশন, সরাসরি নিবন্ধন করুন।');
        }

        if ($session->max_participants && $session->registered_count >= $session->max_participants) {
            throw new \RuntimeException('দুঃখিত, এই সেশনের আসন ইতোমধ্যে পূর্ণ হয়ে গেছে।');
        }

        return DB::transaction(function () use ($student, $session, $notes) {
            $order = Order::create([
                'user_id' => $student->id,
                'course_id' => $session->course_id,
                'live_class_id' => $session->id,
                'order_type' => 'scholar_session',
                'amount' => $session->fee,
                'status' => 'pending',
            ]);

            ScholarSessionRegistration::updateOrCreate(
                [
                    'live_class_id' => $session->id,
                    'user_id' => $student->id,
                ],
                [
                    'order_id' => $order->id,
                    'status' => 'registered',
                    'fee_paid' => $session->fee,
                    'booking_notes' => $notes,
                ]
            );

            return $order;
        });
    }

    /**
     * Confirm paid registration and credit teacher wallet when payment is verified.
     */
    public function completePaidRegistration(Order $order): ScholarSessionRegistration
    {
        $session = LiveClass::findOrFail($order->live_class_id);

        return DB::transaction(function () use ($order, $session) {
            $registration = ScholarSessionRegistration::where('live_class_id', $session->id)
                ->where('user_id', $order->user_id)
                ->first();

            if (! $registration) {
                $registration = ScholarSessionRegistration::create([
                    'live_class_id' => $session->id,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'status' => 'registered',
                    'fee_paid' => $order->final_payable_amount,
                ]);
            } else {
                $registration->update([
                    'status' => 'registered',
                    'fee_paid' => $order->final_payable_amount,
                    'order_id' => $order->id,
                ]);
            }

            $session->increment('registered_count');

            // Credit instructor wallet (Phase 9 Integration)
            if ($session->instructor_id) {
                try {
                    $instructor = User::find($session->instructor_id);
                    if ($instructor) {
                        $wallet = $this->walletService->getOrCreateWallet($instructor);
                        $grossPoisha = (int) round((float) $order->final_payable_amount * 100);

                        // Platform split: 80% instructor, 20% platform
                        $instructorSharePercent = 80;
                        $instructorPoisha = (int) round($grossPoisha * ($instructorSharePercent / 100));

                        $holdDays = (int) config('wallet.hold_days', 7);
                        $holdUntil = now()->addDays($holdDays);

                        $lockedWallet = TeacherWallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();
                        $lockedWallet->pending_balance = (int) bcadd((string) $lockedWallet->pending_balance, (string) $instructorPoisha, 0);
                        $lockedWallet->save();

                        WalletTransaction::create([
                            'wallet_id' => $lockedWallet->id,
                            'type' => 'credit',
                            'balance_type' => 'pending',
                            'amount' => $instructorPoisha,
                            'balance_after' => $lockedWallet->balance,
                            'reference_type' => 'scholar_session',
                            'reference_id' => $session->id,
                            'description' => "স্কলার সেশন ফি: {$session->title} (অর্ডার #{$order->id})",
                            'metadata' => [
                                'order_id' => $order->id,
                                'session_id' => $session->id,
                                'session_title' => $session->title,
                                'gross_poisha' => $grossPoisha,
                                'instructor_poisha' => $instructorPoisha,
                            ],
                            'hold_until' => $holdUntil,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error("Scholar session revenue credit failed for order #{$order->id}: {$e->getMessage()}");
                }
            }

            AuditLoggerService::log(
                action: 'scholar_session.booking_confirmed',
                modelType: ScholarSessionRegistration::class,
                modelId: $registration->id,
                payload: [
                    'session_id' => $session->id,
                    'order_id' => $order->id,
                    'amount' => $order->final_payable_amount,
                ]
            );

            return $registration;
        });
    }

    /**
     * Send reminders to all registered students.
     */
    public function sendReminders(LiveClass $session): int
    {
        $registrations = $session->registrations()->with('user')->get();
        $count = 0;

        foreach ($registrations as $reg) {
            if ($reg->user) {
                $this->dispatcher->scholarSessionReminder($session, $reg->user);
                $count++;
            }
        }

        $session->update(['reminder_sent_at' => now()]);

        return $count;
    }
}
