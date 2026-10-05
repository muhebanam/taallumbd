<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutomatedPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $admin;

    protected Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->student = User::factory()->create(['role' => 'student', 'phone' => '01711223344']);
        $this->admin = User::factory()->create(['role' => 'admin']);

        $instructor = User::factory()->create(['role' => 'instructor']);
        $this->course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'ফিকহুস সুন্নাহ কোর্স',
            'slug' => 'fiqh-us-sunnah',
            'short_description' => 'ফিকহ শিক্ষা',
            'description' => 'বিস্তারিত ফিকহ কোর্স',
            'price' => 1000,
            'is_free' => false,
            'status' => 'published',
        ]);
    }

    public function test_gateway_routes_return_404_when_disabled(): void
    {
        config([
            'payments.gateways.sslcommerz.enabled' => false,
            'payments.gateways.bkash.enabled' => false,
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        // SSLCommerz disabled
        $this->actingAs($this->student)
            ->get("/payments/sslcommerz/init/{$order->id}")
            ->assertStatus(404);

        $this->get("/payments/sslcommerz/callback/{$order->id}")
            ->assertStatus(404);

        $this->post('/webhooks/sslcommerz/ipn')
            ->assertStatus(404);

        // bKash disabled
        $this->actingAs($this->student)
            ->get("/payments/bkash/init/{$order->id}")
            ->assertStatus(404);

        $this->get("/payments/bkash/callback/{$order->id}")
            ->assertStatus(404);

        $this->post('/webhooks/bkash/ipn')
            ->assertStatus(404);
    }

    public function test_sslcommerz_initiate_redirects_to_gateway_page(): void
    {
        config([
            'payments.gateways.sslcommerz.enabled' => true,
            'payments.gateways.sslcommerz.store_id' => 'test_store',
            'payments.gateways.sslcommerz.store_password' => 'test_passwd',
            'payments.gateways.sslcommerz.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/gwprocess/v4/api.php' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/EasyCheckOut/testsession123',
                'sessionkey' => 'testsession123',
            ], 200),
        ]);

        $response = $this->actingAs($this->student)
            ->get("/payments/sslcommerz/init/{$order->id}");

        $response->assertRedirect('https://sandbox.sslcommerz.com/EasyCheckOut/testsession123');

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'sslcommerz',
            'type' => 'initiate',
            'status' => 'initiated',
        ]);

        $this->assertEquals('sslcommerz', $order->fresh()->payment_method);
    }

    public function test_sslcommerz_successful_callback_confirms_payment_and_enrolls(): void
    {
        config([
            'payments.gateways.sslcommerz.enabled' => true,
            'payments.gateways.sslcommerz.store_id' => 'test_store',
            'payments.gateways.sslcommerz.store_password' => 'test_passwd',
            'payments.gateways.sslcommerz.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'amount' => '1000.00',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK_TRX_999888',
                'card_no' => '1234XXXXXXXX5678',
                'card_issuer' => 'BRAC BANK',
            ], 200),
        ]);

        $response = $this->actingAs($this->student)
            ->post("/payments/sslcommerz/callback/{$order->id}", [
                'val_id' => 'VAL_ID_12345',
                'status' => 'VALID',
            ]);

        $response->assertRedirect(route('orders.invoice', $order));

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('BANK_TRX_999888', $order->transaction_id);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method' => 'sslcommerz',
            'transaction_id' => 'BANK_TRX_999888',
            'status' => 'success',
        ]);
    }

    public function test_sslcommerz_tampered_amount_aborts_and_records_tampered_transaction(): void
    {
        config([
            'payments.gateways.sslcommerz.enabled' => true,
            'payments.gateways.sslcommerz.store_id' => 'test_store',
            'payments.gateways.sslcommerz.store_password' => 'test_passwd',
            'payments.gateways.sslcommerz.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        // Gateway returns 100 BDT instead of 1000 BDT
        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'amount' => '100.00',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK_TRX_HACKED',
            ], 200),
        ]);

        $response = $this->actingAs($this->student)
            ->post("/payments/sslcommerz/callback/{$order->id}", [
                'val_id' => 'VAL_ID_HACK',
                'status' => 'VALID',
            ]);

        $response->assertRedirect(route('checkout.show', $this->course->slug));

        $order->refresh();
        $this->assertNotEquals('paid', $order->status);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'sslcommerz',
            'status' => 'tampered',
            'type' => 'amount_mismatch',
        ]);

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
        ]);
    }

    public function test_sslcommerz_duplicate_ipn_is_idempotent(): void
    {
        config([
            'payments.gateways.sslcommerz.enabled' => true,
            'payments.gateways.sslcommerz.store_id' => 'test_store',
            'payments.gateways.sslcommerz.store_password' => 'test_passwd',
            'payments.gateways.sslcommerz.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'amount' => '1000.00',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK_TRX_IPN_1',
            ], 200),
        ]);

        // 1st IPN call
        $first = $this->post('/webhooks/sslcommerz/ipn', [
            'val_id' => 'VAL_IPN_1',
            'value_a' => $order->id,
        ]);
        $first->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->status);

        // 2nd duplicate IPN call
        $second = $this->post('/webhooks/sslcommerz/ipn', [
            'val_id' => 'VAL_IPN_1',
            'value_a' => $order->id,
        ]);
        $second->assertStatus(200);

        // Ensure single enrollment and single payment record created
        $this->assertEquals(1, Enrollment::where('user_id', $this->student->id)->where('course_id', $this->course->id)->count());
        $this->assertEquals(1, Payment::where('order_id', $order->id)->count());
    }

    public function test_bkash_initiate_redirects_to_bkash_checkout(): void
    {
        config([
            'payments.gateways.bkash.enabled' => true,
            'payments.gateways.bkash.app_key' => 'bk_app_key',
            'payments.gateways.bkash.app_secret' => 'bk_app_secret',
            'payments.gateways.bkash.username' => 'bk_user',
            'payments.gateways.bkash.password' => 'bk_pass',
            'payments.gateways.bkash.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        Http::fake([
            'https://tokenized.sandbox.bka.sh/v2.0/checkout/token/grant' => Http::response([
                'id_token' => 'mock_bkash_token_123',
                'statusCode' => '0000',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v2.0/checkout/create' => Http::response([
                'statusCode' => '0000',
                'bkashURL' => 'https://sandbox.payment.bkash.com/redirect/tokenized?paymentID=BK_PAY_111',
                'paymentID' => 'BK_PAY_111',
            ], 200),
        ]);

        $response = $this->actingAs($this->student)
            ->get("/payments/bkash/init/{$order->id}");

        $response->assertRedirect('https://sandbox.payment.bkash.com/redirect/tokenized?paymentID=BK_PAY_111');

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'type' => 'initiate',
            'status' => 'initiated',
        ]);

        $this->assertEquals('bkash', $order->fresh()->payment_method);
    }

    public function test_bkash_successful_callback_executes_and_confirms_payment(): void
    {
        config([
            'payments.gateways.bkash.enabled' => true,
            'payments.gateways.bkash.app_key' => 'bk_app_key',
            'payments.gateways.bkash.app_secret' => 'bk_app_secret',
            'payments.gateways.bkash.username' => 'bk_user',
            'payments.gateways.bkash.password' => 'bk_pass',
            'payments.gateways.bkash.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
            'payment_method' => 'bkash',
        ]);

        Http::fake([
            'https://tokenized.sandbox.bka.sh/v2.0/checkout/token/grant' => Http::response([
                'id_token' => 'mock_bkash_token_123',
                'statusCode' => '0000',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v2.0/checkout/execute' => Http::response([
                'statusCode' => '0000',
                'paymentID' => 'BK_PAY_111',
                'trxID' => 'BK_TRX_887766',
                'amount' => '1000.00',
                'currency' => 'BDT',
                'customerMsisdn' => '01711223344',
            ], 200),
        ]);

        $response = $this->actingAs($this->student)
            ->get("/payments/bkash/callback/{$order->id}?paymentID=BK_PAY_111&status=success");

        $response->assertRedirect(route('orders.invoice', $order));

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('BK_TRX_887766', $order->transaction_id);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);
    }

    public function test_bkash_tampered_amount_aborts_and_records_tampered_transaction(): void
    {
        config([
            'payments.gateways.bkash.enabled' => true,
            'payments.gateways.bkash.app_key' => 'bk_app_key',
            'payments.gateways.bkash.app_secret' => 'bk_app_secret',
            'payments.gateways.bkash.username' => 'bk_user',
            'payments.gateways.bkash.password' => 'bk_pass',
            'payments.gateways.bkash.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
            'payment_method' => 'bkash',
        ]);

        Http::fake([
            'https://tokenized.sandbox.bka.sh/v2.0/checkout/token/grant' => Http::response([
                'id_token' => 'mock_bkash_token_123',
                'statusCode' => '0000',
            ], 200),
            'https://tokenized.sandbox.bka.sh/v2.0/checkout/execute' => Http::response([
                'statusCode' => '0000',
                'paymentID' => 'BK_PAY_TAMPER',
                'trxID' => 'BK_TRX_TAMPER',
                'amount' => '10.00', // Mismatch!
                'currency' => 'BDT',
            ], 200),
        ]);

        $response = $this->actingAs($this->student)
            ->get("/payments/bkash/callback/{$order->id}?paymentID=BK_PAY_TAMPER&status=success");

        $response->assertRedirect(route('checkout.show', $this->course->slug));

        $order->refresh();
        $this->assertNotEquals('paid', $order->status);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'status' => 'tampered',
            'type' => 'amount_mismatch',
        ]);
    }

    public function test_reconcile_command_verifies_pending_orders_older_than_30_minutes(): void
    {
        config([
            'payments.gateways.sslcommerz.enabled' => true,
            'payments.gateways.sslcommerz.store_id' => 'test_store',
            'payments.gateways.sslcommerz.store_password' => 'test_passwd',
            'payments.gateways.sslcommerz.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
            'payment_method' => 'sslcommerz',
        ]);
        Order::where('id', $order->id)->update(['created_at' => now()->subMinutes(35)]);
        $order->refresh();

        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'sslcommerz',
            'type' => 'initiate',
            'gateway_ref' => 'VAL_RECONCILE_1',
            'amount' => 1000,
            'currency' => 'BDT',
            'status' => 'initiated',
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php*' => Http::response([
                'status' => 'VALID',
                'amount' => '1000.00',
                'currency' => 'BDT',
                'bank_tran_id' => 'BANK_TRX_RECONCILED',
            ], 200),
        ]);

        $this->artisan('payments:reconcile')
            ->expectsOutputToContain('Found 1 candidate order(s) for reconciliation.')
            ->expectsOutputToContain("Order #{$order->id} successfully reconciled and confirmed!")
            ->assertSuccessful();

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->assertEquals('BANK_TRX_RECONCILED', $order->transaction_id);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_can_refund_paid_order_and_revokes_enrollment(): void
    {
        config([
            'payments.gateways.sslcommerz.enabled' => true,
            'payments.gateways.sslcommerz.store_id' => 'test_store',
            'payments.gateways.sslcommerz.store_password' => 'test_passwd',
            'payments.gateways.sslcommerz.mode' => 'sandbox',
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'paid',
            'payment_method' => 'sslcommerz',
            'transaction_id' => 'BANK_TRX_REFUND_1',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'transaction_id' => 'BANK_TRX_REFUND_1',
            'payment_method' => 'sslcommerz',
            'amount' => 1000,
            'status' => 'success',
        ]);

        $enrollment = Enrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
            'progress' => 10,
        ]);

        Http::fake([
            'https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php' => Http::response([
                'status' => 'success',
                'refund_ref_id' => 'REFUND_REF_123',
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/orders/{$order->id}/refund", [
                'reason' => 'শিক্ষার্থীর অনুরোধে কোর্স ফি ফেরত দেওয়া হলো',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('cancelled', $order->status);

        $enrollment->refresh();
        $this->assertEquals('cancelled', $enrollment->status);

        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'gateway' => 'sslcommerz',
            'type' => 'refund',
            'status' => 'refunded',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order.refunded',
            'model_type' => Order::class,
            'model_id' => $order->id,
        ]);
    }

    public function test_default_manual_flow_unaffected_when_automated_gateways_disabled(): void
    {
        config([
            'payments.gateways.sslcommerz.enabled' => false,
            'payments.gateways.bkash.enabled' => false,
            'payments.gateways.manual.enabled' => true,
        ]);

        $order = Order::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'amount' => 1000,
            'status' => 'pending',
        ]);

        // Student submits manual payment proof
        $response = $this->actingAs($this->student)
            ->post("/payment/{$order->id}/manual-submit", [
                'method' => 'bkash',
                'sender_phone' => '01711223344',
                'transaction_id' => 'MANUAL1234',
            ]);

        $response->assertRedirect(route('invoice', $order));

        $order->refresh();
        $this->assertEquals('pending_verification', $order->status);
        $this->assertEquals('MANUAL1234', $order->transaction_id);

        // Admin approves manual payment
        $approveResponse = $this->actingAs($this->admin)
            ->post("/admin/orders/{$order->id}/approve");

        $approveResponse->assertRedirect();

        $order->refresh();
        $this->assertEquals('paid', $order->status);

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);
    }
}
