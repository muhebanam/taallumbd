<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\PayoutRequest;
use App\Models\RevenueShare;
use App\Models\Teacher;
use App\Models\TeacherWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\PaymentService;
use App\Services\TeacherWalletService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherEconomyTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $instructorUser;

    protected Teacher $teacher;

    protected User $admin;

    protected Course $course;

    protected TeacherWalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->walletService = app(TeacherWalletService::class);

        $this->student = User::factory()->create(['role' => 'student', 'phone' => '01711223344']);
        $this->admin = User::factory()->create(['role' => 'admin', 'phone' => '01811223344']);

        $this->instructorUser = User::factory()->create([
            'role' => 'instructor',
            'name' => 'মাওলানা আব্দুল্লাহ',
            'phone' => '01911223344',
        ]);

        $this->teacher = Teacher::create([
            'user_id' => $this->instructorUser->id,
            'name' => 'মাওলানা আব্দুল্লাহ',
            'slug' => 'mawlana-abdullah',
            'bio' => 'মুফতি ও সিনিয়র শিক্ষক',
            'designation' => 'সিনিয়র লেকচারার',
            'status' => 'approved',
            'consultation_fee' => 150000, // ৳1,500 in poisha
            'consultation_session_duration' => 45,
            'consultation_enabled' => true,
        ]);

        $this->course = Course::create([
            'instructor_id' => $this->instructorUser->id,
            'title' => 'উসুলুল ফিকহ ও আধুনিক প্রয়োগ',
            'slug' => 'usul-al-fiqh',
            'short_description' => 'উসুলুল ফিকহ এর নিয়মাবলী',
            'description' => 'উসুলুল ফিকহ বিস্তারিত কোর্স',
            'price' => 1000, // ৳1,000
            'is_free' => false,
            'status' => 'published',
        ]);
    }

    public function test_wallet_is_created_automatically_for_teacher(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);

        $this->assertNotNull($wallet);
        $this->assertEquals($this->teacher->id, $wallet->teacher_id);
        $this->assertEquals(0, $wallet->balance);
        $this->assertEquals(0, $wallet->pending_balance);
        $this->assertEquals('BDT', $wallet->currency);

        $this->assertDatabaseHas('teacher_wallets', [
            'teacher_id' => $this->teacher->id,
            'balance' => 0,
            'pending_balance' => 0,
        ]);
    }

    public function test_payment_confirmation_credits_pending_balance_with_default_70_30_share(): void
    {
        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentService::class);
        $paymentService->confirm($order, 'bkash', 'TXN-WALLET-001', '01711223344');

        $wallet = TeacherWallet::where('teacher_id', $this->teacher->id)->first();
        $this->assertNotNull($wallet);

        // 70% of 1000 BDT = 700 BDT = 70,000 poisha
        $this->assertEquals(70000, $wallet->pending_balance);
        $this->assertEquals(0, $wallet->balance);
        $this->assertEquals('700.00', $wallet->pending_balance_bdt);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'balance_type' => 'pending',
            'amount' => 70000,
            'reference_type' => 'order',
            'reference_id' => $order->id,
        ]);
    }

    public function test_custom_course_revenue_share_overrides_platform_default(): void
    {
        // 85% instructor share on this course
        RevenueShare::create([
            'course_id' => $this->course->id,
            'instructor_share_percentage' => 85.00,
            'platform_share_percentage' => 15.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 2000,
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentService::class);
        $paymentService->confirm($order, 'sslcommerz', 'TXN-WALLET-002', '01711223344');

        $wallet = TeacherWallet::where('teacher_id', $this->teacher->id)->first();
        // 85% of 2000 BDT = 1700 BDT = 170,000 poisha
        $this->assertEquals(170000, $wallet->pending_balance);
    }

    public function test_custom_teacher_revenue_share_overrides_platform_default(): void
    {
        // 80% instructor share for this specific teacher
        RevenueShare::create([
            'teacher_id' => $this->teacher->id,
            'instructor_share_percentage' => 80.00,
            'platform_share_percentage' => 20.00,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentService::class);
        $paymentService->confirm($order, 'bkash', 'TXN-WALLET-003', '01711223344');

        $wallet = TeacherWallet::where('teacher_id', $this->teacher->id)->first();
        // 80% of 1000 BDT = 800 BDT = 80,000 poisha
        $this->assertEquals(80000, $wallet->pending_balance);
    }

    public function test_release_pending_command_moves_matured_earnings_to_available_balance(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'paid',
        ]);

        $tx = $this->walletService->creditPendingRevenue($order);
        $this->assertNotNull($tx);

        $tx->update(['hold_until' => Carbon::now()->subDays(1)]);

        $wallet->refresh();
        $this->assertEquals(70000, $wallet->pending_balance);
        $this->assertEquals(0, $wallet->balance);

        // Run artisan command
        $this->artisan('wallet:release-pending')
            ->expectsOutputToContain('Successfully released 1 matured pending transactions')
            ->assertExitCode(0);

        $wallet->refresh();
        $this->assertEquals(0, $wallet->pending_balance);
        $this->assertEquals(70000, $wallet->balance);
        $this->assertEquals('700.00', $wallet->balance_bdt);

        $tx->refresh();
        $this->assertNotNull($tx->matured_at);
    }

    public function test_refund_reversal_deducts_pending_revenue_if_still_held(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'paid',
        ]);

        $this->walletService->creditPendingRevenue($order);

        $wallet->refresh();
        $this->assertEquals(70000, $wallet->pending_balance);

        // Refund order
        $reversalTx = $this->walletService->reverseRefund($order, 'স্টুডেন্টের অনুরোধে রিফান্ড');

        $this->assertNotNull($reversalTx);
        $wallet->refresh();
        $this->assertEquals(0, $wallet->pending_balance);
        $this->assertEquals(0, $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'debit',
            'balance_type' => 'pending',
            'amount' => 70000,
            'reference_type' => 'refund_reversal',
        ]);
    }

    public function test_refund_reversal_deducts_available_balance_if_already_released(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'paid',
        ]);

        $tx = $this->walletService->creditPendingRevenue($order);
        $tx->update(['hold_until' => Carbon::now()->subDays(1)]);
        $this->walletService->releaseMaturedPendingBalances();

        $wallet->refresh();
        $this->assertEquals(70000, $wallet->balance);
        $this->assertEquals(0, $wallet->pending_balance);

        // Refund order
        $this->walletService->reverseRefund($order, 'পোস্ট-রিলিজ রিফান্ড');

        $wallet->refresh();
        $this->assertEquals(0, $wallet->balance);
        $this->assertEquals(0, $wallet->pending_balance);
    }

    public function test_ledger_invariant_sum_of_transactions_equals_wallet_balance(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);

        // Perform 3 sales
        for ($i = 1; $i <= 3; $i++) {
            $order = Order::create([
                'user_id' => $this->student->id,
                'course_id' => $this->course->id,
                'amount' => 1000,
                'status' => 'paid',
            ]);
            $tx = $this->walletService->creditPendingRevenue($order);
            $tx->update(['hold_until' => Carbon::now()->subDays(1)]);
        }

        // Release all
        $this->walletService->releaseMaturedPendingBalances();

        $wallet->refresh();
        $this->assertEquals(210000, $wallet->balance); // 3 * 70,000 = 210,000 poisha

        // Request 1 payout of 100,000 poisha (৳1,000)
        $this->walletService->requestPayout(
            $this->teacher,
            100000,
            'bkash',
            ['account_number' => '01911223344']
        );

        $wallet->refresh();
        $this->assertEquals(110000, $wallet->balance);

        // Verify ledger invariant on available balance transactions:
        // Available Credits: 210,000 poisha
        // Available Debits: 100,000 poisha
        // Expected Balance: 110,000 poisha
        $availableCredits = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('balance_type', 'available')
            ->where('type', 'credit')
            ->sum('amount');

        $availableDebits = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('balance_type', 'available')
            ->where('type', 'debit')
            ->sum('amount');

        $this->assertEquals($wallet->balance, $availableCredits - $availableDebits);
        $this->assertEquals(110000, $availableCredits - $availableDebits);
    }

    public function test_instructor_cannot_request_payout_below_minimum_threshold(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);
        $wallet->update(['balance' => 100000]); // ৳1,000 available

        $this->actingAs($this->instructorUser)
            ->post('/instructor/earnings/payout', [
                'amount_bdt' => 400, // Below ৳500 minimum
                'method' => 'bkash',
                'account_number' => '01911223344',
            ])
            ->assertSessionHasErrors('amount_bdt');

        $wallet->refresh();
        $this->assertEquals(100000, $wallet->balance); // Unchanged
    }

    public function test_instructor_can_request_payout_with_valid_amount(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);
        $wallet->update(['balance' => 500000]); // ৳5,000 available

        $response = $this->actingAs($this->instructorUser)
            ->post('/instructor/earnings/payout', [
                'amount_bdt' => 2000,
                'method' => 'bkash',
                'account_number' => '01911223344',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $wallet->refresh();
        $this->assertEquals(300000, $wallet->balance); // 5000 - 2000 = ৳3,000 = 300,000 poisha

        $payout = PayoutRequest::where('teacher_id', $this->teacher->id)->first();
        $this->assertNotNull($payout);
        $this->assertEquals(200000, $payout->amount);
        $this->assertEquals('pending', $payout->status);
        $this->assertEquals('bkash', $payout->method);
        $this->assertEquals('01911223344', $payout->account_info['account_number']);
    }

    public function test_admin_can_approve_payout_request(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);
        $wallet->update(['balance' => 500000]);

        $payout = $this->walletService->requestPayout(
            $this->teacher,
            200000,
            'bkash',
            ['account_number' => '01911223344']
        );

        $response = $this->actingAs($this->admin)
            ->post("/admin/payouts/{$payout->id}/approve", [
                'transaction_reference' => 'BKASH-TRX-778899',
                'admin_notes' => 'সফলভাবে পেআউট সম্পন্ন হয়েছে',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payout->refresh();
        $this->assertEquals('paid', $payout->status);
        $this->assertEquals($this->admin->id, $payout->processed_by);
        $this->assertEquals('BKASH-TRX-778899', $payout->transaction_reference);
        $this->assertNotNull($payout->processed_at);
    }

    public function test_admin_can_reject_payout_request_and_funds_are_refunded_to_wallet(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);
        $wallet->update(['balance' => 500000]);

        $payout = $this->walletService->requestPayout(
            $this->teacher,
            200000,
            'bank',
            ['bank_name' => 'Islami Bank Bangladesh PLC', 'account_number' => '205012345678']
        );

        $wallet->refresh();
        $this->assertEquals(300000, $wallet->balance);

        $response = $this->actingAs($this->admin)
            ->post("/admin/payouts/{$payout->id}/reject", [
                'reason' => 'ব্যাংক হিসাব নম্বর অসম্পূর্ণ ছিল',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payout->refresh();
        $this->assertEquals('rejected', $payout->status);
        $this->assertEquals($this->admin->id, $payout->processed_by);

        // Wallet balance must be restored to 500,000 poisha
        $wallet->refresh();
        $this->assertEquals(500000, $wallet->balance);

        $this->assertDatabaseHas('wallet_transactions', [
            'wallet_id' => $wallet->id,
            'type' => 'credit',
            'balance_type' => 'available',
            'amount' => 200000,
            'reference_type' => 'payout_rejected',
        ]);
    }

    public function test_scholar_consultation_profile_fields_and_settings(): void
    {
        $this->assertEquals(150000, $this->teacher->consultation_fee);
        $this->assertEquals('1500.00', $this->teacher->consultation_fee_bdt);
        $this->assertEquals(45, $this->teacher->consultation_session_duration);
        $this->assertTrue($this->teacher->consultation_enabled);

        // Update via instructor profile controller (PUT /instructor/profile/teacher)
        $this->actingAs($this->instructorUser)
            ->put('/instructor/profile/teacher', [
                'name' => 'মাওলানা আব্দুল্লাহ',
                'bio' => 'মুফতি ও গবেষক',
                'designation' => 'প্রধান মুফতি',
                'consultation_fee' => 2000,
                'consultation_session_duration' => 60,
                'consultation_enabled' => true,
            ])
            ->assertRedirect();

        $this->teacher->refresh();
        $this->assertEquals(200000, $this->teacher->consultation_fee);
        $this->assertEquals(60, $this->teacher->consultation_session_duration);
    }

    public function test_monthly_statement_is_accessible_by_instructor_and_admin(): void
    {
        $year = now()->year;
        $month = now()->month;

        // Instructor statement
        $response = $this->actingAs($this->instructorUser)
            ->get("/instructor/earnings/statement/{$year}/{$month}");
        $response->assertStatus(200);
        $response->assertSee('মাসিক আর্থিক স্টেটমেন্ট');
        $response->assertSee('মাওলানা আব্দুল্লাহ');

        // Other student cannot access instructor statement
        $this->actingAs($this->student)
            ->get("/instructor/earnings/statement/{$year}/{$month}")
            ->assertStatus(403);

        // Admin statement for teacher
        $adminResponse = $this->actingAs($this->admin)
            ->get("/admin/payouts/statement/{$this->teacher->id}/{$year}/{$month}");
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('মাসিক আর্থিক স্টেটমেন্ট');
    }

    public function test_concurrent_payout_requests_enforces_balance_lock(): void
    {
        $wallet = $this->walletService->getOrCreateWallet($this->teacher);
        $wallet->update(['balance' => 500000]); // ৳5,000 available

        // First withdrawal of ৳3,000 (300,000 poisha)
        $payout1 = $this->walletService->requestPayout(
            $this->teacher,
            300000,
            'bkash',
            ['account_number' => '01911223344']
        );
        $this->assertNotNull($payout1);

        $wallet->refresh();
        $this->assertEquals(200000, $wallet->balance); // ৳2,000 left

        // Second withdrawal attempt of ৳3,000 must fail due to insufficient locked balance
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('ওয়ালেটে পর্যাপ্ত ব্যালেন্স নেই');

        $this->walletService->requestPayout(
            $this->teacher,
            300000,
            'bkash',
            ['account_number' => '01911223344']
        );
    }

    public function test_exact_poisha_arithmetic_has_zero_floating_point_drift(): void
    {
        // Set an uneven revenue split: 66.67% instructor / 33.33% platform
        RevenueShare::create([
            'course_id' => $this->course->id,
            'instructor_share_percentage' => 66.67,
            'platform_share_percentage' => 33.33,
            'is_active' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1337.50, // ৳1,337.50 = 133,750 poisha
            'status' => 'pending',
        ]);

        $paymentService = app(PaymentService::class);
        $paymentService->confirm($order, 'sslcommerz', 'TXN-EXACT-001', '01711223344');

        $wallet = TeacherWallet::where('teacher_id', $this->teacher->id)->first();
        $txn = WalletTransaction::where('wallet_id', $wallet->id)
            ->where('reference_type', 'order')
            ->where('reference_id', $order->id)
            ->first();

        $grossPoisha = 133750;
        // instructor poisha = floor(133750 * 66.67 / 100) = 89171 poisha
        $expectedInstructorPoisha = (int) bcdiv(bcmul('133750', '66.67', 2), '100', 0);
        $expectedPlatformPoisha = $grossPoisha - $expectedInstructorPoisha;

        $this->assertEquals($expectedInstructorPoisha, $txn->amount);
        $this->assertEquals($expectedInstructorPoisha, $wallet->pending_balance);

        // Platform fee + Instructor poisha must strictly equal gross poisha with 0 poisha loss
        $metadata = $txn->metadata;
        $this->assertEquals($grossPoisha, $metadata['instructor_amount_poisha'] + $metadata['platform_fee_poisha']);
        $this->assertEquals($expectedPlatformPoisha, $metadata['platform_fee_poisha']);
    }
}
