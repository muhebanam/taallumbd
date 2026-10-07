<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Teacher;
use App\Models\TeacherWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\RefundService;
use App\Services\TeacherWalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RefundWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_refund_workflow_revokes_enrollment_and_adjusts_wallet(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $instructorUser = User::factory()->create(['role' => 'instructor']);

        $teacher = Teacher::create([
            'user_id' => $instructorUser->id,
            'name' => $instructorUser->name,
            'slug' => 'test-teacher',
            'status' => 'active',
        ]);

        $course = Course::create([
            'instructor_id' => $instructorUser->id,
            'title' => 'ফিকহুল হাদিস কোর্স',
            'slug' => 'fiqh-hadith',
            'short_description' => 'কোর্স বিবরণ',
            'description' => 'বিস্তারিত বর্ণনা',
            'price' => 1000,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 1000,
            'currency' => 'BDT',
            'status' => 'paid',
            'payment_method' => 'bkash',
            'transaction_id' => 'BKASH-REF-TEST-01',
        ]);

        // Student Enrollment
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'enrolled_at' => now(),
        ]);

        // Credit instructor wallet revenue
        $walletService = app(TeacherWalletService::class);
        $creditTx = $walletService->creditPendingRevenue($order);
        $this->assertNotNull($creditTx);

        $wallet = TeacherWallet::where('teacher_id', $teacher->id)->first();
        $this->assertGreaterThan(0, $wallet->pending_balance);

        // Process refund
        $refundService = app(RefundService::class);
        $refundedOrder = $refundService->processRefund($order, 'শিক্ষার্থী রিফান্ড আবেদন করেছে', $admin);

        // Assertions
        $this->assertEquals('refunded', $refundedOrder->fresh()->status);
        $this->assertEquals('cancelled', $enrollment->fresh()->status);

        // Wallet deduction assertion
        $wallet->refresh();
        $this->assertEquals(0, $wallet->pending_balance);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'type' => 'refund',
            'status' => 'success',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order.refunded',
            'model_id' => $order->id,
        ]);
    }

    public function test_cannot_refund_unpaid_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['price' => 500]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $this->expectException(\InvalidArgumentException::class);

        app(RefundService::class)->processRefund($order, 'টেস্ট', $admin);
    }

    public function test_cannot_refund_after_refund_window_expires(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['price' => 500]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 500,
            'status' => 'paid',
            'created_at' => now()->subDays(10), // 10 days ago (window is 7 days)
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('রিফান্ডের সময়সীমা');

        app(RefundService::class)->processRefund($order, 'দেরিতে আবেদন', $admin);
    }

    public function test_cannot_refund_if_course_progress_exceeds_threshold(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['price' => 500]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 500,
            'status' => 'paid',
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress' => 45.0, // 45% completed (threshold is max 20%)
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('বেশি সম্পন্ন করায়');

        app(RefundService::class)->processRefund($order, 'অগ্রগতি বেশি', $admin);
    }

    public function test_payment_state_machine_enforces_valid_lifecycle(): void
    {
        $order = Order::factory()->create(['status' => 'initiated']);

        // Valid transition: initiated -> pending
        \App\Payments\PaymentStateMachine::transition($order, 'pending');
        $this->assertEquals('pending', $order->status);

        // Valid transition: pending -> paid
        \App\Payments\PaymentStateMachine::transition($order, 'paid');
        $this->assertEquals('paid', $order->status);

        // Invalid transition: paid -> initiated (Throws exception)
        $this->expectException(\InvalidArgumentException::class);
        \App\Payments\PaymentStateMachine::transition($order, 'initiated');
    }
}
