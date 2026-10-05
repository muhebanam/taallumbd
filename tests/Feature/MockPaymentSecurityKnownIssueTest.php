<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MockPaymentSecurityKnownIssueTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test mock payment behavior in development/testing mode.
     * Note: In local and testing environments, mock payment allows immediate enrollment.
     * In production, mock payments are disabled and manual payment requires admin verification.
     *
     * @group security-known-issue
     */
    public function test_mock_payment_endpoint_behavior_in_testing_environment(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->paid(1200)->published()->create();
        $order = Order::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 1200.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)->post(route('payment.mock.success', $order));

        $response->assertRedirect(route('student.courses.show', $course));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);

        $this->assertTrue($student->fresh()->isEnrolled($course));
    }

    /**
     * @group security-known-issue
     */
    public function test_user_cannot_trigger_mock_payment_for_another_users_order(): void
    {
        $owner = User::factory()->student()->create();
        $attacker = User::factory()->student()->create();
        $course = Course::factory()->paid(1200)->published()->create();
        $order = Order::factory()->create([
            'user_id' => $owner->id,
            'course_id' => $course->id,
            'amount' => 1200.00,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($attacker)->post(route('payment.mock.success', $order));

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->status);
    }
}
