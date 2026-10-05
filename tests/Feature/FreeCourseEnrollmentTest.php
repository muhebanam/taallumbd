<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FreeCourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_checkout(): void
    {
        $course = Course::factory()->free()->published()->create();

        $response = $this->get(route('checkout.show', $course));
        $response->assertRedirect('/login');

        $postResponse = $this->post(route('checkout.process', $course));
        $postResponse->assertRedirect('/login');
    }

    public function test_student_can_enroll_in_free_course_directly(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->free()->published()->create();

        $response = $this->actingAs($student)->post(route('checkout.process', $course));

        $response->assertRedirect(route('student.courses.show', $course));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->assertTrue($student->fresh()->isEnrolled($course));
    }

    public function test_already_enrolled_student_is_redirected_to_learning_room(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->free()->published()->create();

        Enrollment::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($student)->post(route('checkout.process', $course));

        $response->assertRedirect(route('student.courses.show', $course));
        $this->assertEquals(1, Enrollment::where('user_id', $student->id)->where('course_id', $course->id)->count());
    }

    public function test_paid_course_checkout_creates_pending_order(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->paid(800)->published()->create();

        $response = $this->actingAs($student)->post(route('checkout.process', $course));

        $this->assertDatabaseHas('orders', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'amount' => 800.00,
            'status' => 'pending',
        ]);

        $this->assertFalse($student->fresh()->isEnrolled($course));
    }
}
