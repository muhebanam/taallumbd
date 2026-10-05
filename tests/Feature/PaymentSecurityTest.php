<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_invoice(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ১',
            'slug' => 'course-1',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 500,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $user1->id,
            'course_id' => $course->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user2)->get('/invoice/'.$order->id);
        $response->assertStatus(403);
    }

    public function test_user_can_view_own_invoice(): void
    {
        $user = User::factory()->create();
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ১',
            'slug' => 'course-1',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 500,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/invoice/'.$order->id);
        $response->assertStatus(200);
    }

    public function test_manual_payment_submission_records_transaction(): void
    {
        $user = User::factory()->create();
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ১',
            'slug' => 'course-manual-test',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 1000,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post('/payment/'.$order->id.'/manual-submit', [
            'method' => 'bkash',
            'sender_phone' => '01712345678',
            'transaction_id' => 'TRX123456TEST',
        ]);

        $response->assertRedirect('/invoice/'.$order->id);

        $order->refresh();
        $this->assertEquals('pending_verification', $order->status);
        $this->assertEquals('TRX123456TEST', $order->transaction_id);
        $this->assertEquals('01712345678', $order->sender_phone);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'type' => 'manual_submit',
            'gateway_ref' => 'TRX123456TEST',
            'status' => 'pending_verification',
        ]);
    }

    public function test_duplicate_successful_transaction_id_is_rejected(): void
    {
        $user = User::factory()->create();
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ১',
            'slug' => 'course-dup-test',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 1000,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order1 = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 1000,
            'status' => 'paid',
        ]);

        Payment::create([
            'order_id' => $order1->id,
            'transaction_id' => 'TRX-DUPLICATE',
            'payment_method' => 'bkash',
            'amount' => 1000,
            'status' => 'success',
        ]);

        $order2 = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->post('/payment/'.$order2->id.'/manual-submit', [
            'method' => 'bkash',
            'sender_phone' => '01712345678',
            'transaction_id' => 'TRX-DUPLICATE',
        ]);

        $response->assertSessionHasErrors('transaction_id');
    }

    public function test_admin_can_approve_order_and_enroll_student(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ১',
            'slug' => 'course-approve-test',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 1000,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 1000,
            'status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => 'TRX-APPROVE-1',
            'sender_phone' => '01700000001',
        ]);

        $response = $this->actingAs($admin)->post('/admin/orders/'.$order->id.'/approve');
        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals('paid', $order->status);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order.payment_confirmed',
            'model_id' => $order->id,
        ]);
    }

    public function test_admin_can_reject_order(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ১',
            'slug' => 'course-reject-test',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 1000,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 1000,
            'status' => 'pending',
            'payment_method' => 'bkash',
            'transaction_id' => 'TRX-REJECT-1',
        ]);

        $response = $this->actingAs($admin)->post('/admin/orders/'.$order->id.'/reject');
        $response->assertRedirect();

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_audit_log_index_accessible_to_admin_only(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/admin/audit-logs');
        $response->assertStatus(403);

        $response = $this->actingAs($admin)->get('/admin/audit-logs');
        $response->assertStatus(200);
    }

    public function test_security_headers_are_applied(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_admin_can_update_payment_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);

        $settingsPayload = [
            'settings' => [
                'bkash' => [
                    'number' => '01711223344',
                    'type' => 'merchant',
                    'instructions' => 'বিকাশ পেমেন্ট করুন',
                    'enabled' => true,
                ],
                'nagad' => [
                    'number' => '01811223344',
                    'type' => 'personal',
                    'instructions' => 'নগদ সেন্ড মানি করুন',
                    'enabled' => true,
                ],
            ],
        ];

        // Student cannot update
        $res = $this->actingAs($student)->post('/admin/settings/payments', $settingsPayload);
        $res->assertStatus(403);

        // Admin can update
        $res = $this->actingAs($admin)->post('/admin/settings/payments', $settingsPayload);
        $res->assertRedirect();

        $this->assertEquals('01711223344', Setting::get('payment_manual_bkash_number'));
        $this->assertEquals('merchant', Setting::get('payment_manual_bkash_type'));
    }

    public function test_manual_payment_with_screenshot_upload(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ছবি টেস্ট',
            'slug' => 'course-screenshot-test',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 1000,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        $file = UploadedFile::fake()->image('payment_receipt.png', 400, 300);

        $response = $this->actingAs($user)->post('/payment/'.$order->id.'/manual-submit', [
            'method' => 'bkash',
            'sender_phone' => '01712345678',
            'transaction_id' => 'TRXSCREENSHOT123',
            'screenshot' => $file,
        ]);

        $response->assertRedirect('/invoice/'.$order->id);

        $order->refresh();
        $this->assertEquals('pending_verification', $order->status);
        $this->assertNotNull($order->screenshot_path);
        Storage::disk('local')->assertExists($order->screenshot_path);
    }
}
