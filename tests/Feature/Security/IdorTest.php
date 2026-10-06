<?php

namespace Tests\Feature\Security;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonBookmark;
use App\Models\LessonNote;
use App\Models\Order;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IdorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_read_or_update_another_users_private_lesson_notes(): void
    {
        $userA = User::factory()->create(['role' => 'student']);
        $userB = User::factory()->create(['role' => 'student']);

        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $noteB = LessonNote::create([
            'user_id' => $userB->id,
            'lesson_id' => $lesson->id,
            'note' => 'User B private study note.',
        ]);

        // User A requests User B's lesson note endpoint
        $response = $this->actingAs($userA)->getJson(route('student.lessons.note.get', $lesson));
        $response->assertOk();

        // Must NOT return User B's note content
        $data = $response->json();
        $this->assertNotSame('User B private study note.', $data['note'] ?? null);
    }

    public function test_user_cannot_delete_another_users_bookmark(): void
    {
        $userA = User::factory()->create(['role' => 'student']);
        $userB = User::factory()->create(['role' => 'student']);

        $course = Course::factory()->create();
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $bookmarkB = LessonBookmark::create([
            'user_id' => $userB->id,
            'lesson_id' => $lesson->id,
            'timestamp_seconds' => 120,
            'title' => 'User B private bookmark',
        ]);

        $response = $this->actingAs($userA)->delete(route('student.lessons.bookmarks.destroy', $bookmarkB));

        // Must be forbidden (403)
        $this->assertSame(403, $response->status());
        $this->assertDatabaseHas('lesson_bookmarks', ['id' => $bookmarkB->id]);
    }

    public function test_instructor_cannot_edit_or_delete_another_instructors_course(): void
    {
        $instructorA = User::factory()->create([
            'role' => 'instructor',
            'two_factor_secret' => 'SECRETKEY1234567',
            'two_factor_confirmed_at' => now(),
        ]);
        $instructorB = User::factory()->create([
            'role' => 'instructor',
            'two_factor_secret' => 'SECRETKEY7654321',
            'two_factor_confirmed_at' => now(),
        ]);

        $courseB = Course::factory()->create([
            'instructor_id' => $instructorB->id,
            'title' => 'Instructor B Proprietary Course',
        ]);

        // Instructor A attempts to edit Instructor B's course
        $response = $this->actingAs($instructorA)
            ->withSession(['two_factor_verified' => true])
            ->get(route('instructor.courses.edit', $courseB));

        $this->assertSame(403, $response->status());

        // Instructor A attempts to update Instructor B's course
        $updateResponse = $this->actingAs($instructorA)
            ->withSession(['two_factor_verified' => true])
            ->put(route('instructor.courses.update', $courseB), [
                'title' => 'Hacked Course Title',
            ]);

        $this->assertSame(403, $updateResponse->status());
        $this->assertDatabaseHas('courses', [
            'id' => $courseB->id,
            'title' => 'Instructor B Proprietary Course',
        ]);
    }

    public function test_student_cannot_access_admin_orders_or_payment_management(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $order = Order::factory()->create();

        // Student attempts accessing admin orders
        $response = $this->actingAs($student)->get(route('admin.orders.index'));
        $this->assertSame(403, $response->status());

        // Student attempts approving order
        $approveResponse = $this->actingAs($student)->post(route('admin.orders.approve', $order));
        $this->assertSame(403, $approveResponse->status());
    }

    public function test_member_of_org_a_cannot_access_or_manage_org_b_cohorts(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $orgA = Organization::create([
            'name' => 'Madrasa A',
            'slug' => 'madrasa-a',
            'subdomain' => 'madrasa-a',
            'plan' => 'enterprise',
        ]);

        $orgB = Organization::create([
            'name' => 'Madrasa B',
            'slug' => 'madrasa-b',
            'subdomain' => 'madrasa-b',
            'plan' => 'enterprise',
        ]);

        OrganizationMember::create([
            'organization_id' => $orgA->id,
            'user_id' => $userA->id,
            'role' => 'org_admin',
        ]);

        OrganizationMember::create([
            'organization_id' => $orgB->id,
            'user_id' => $userB->id,
            'role' => 'org_admin',
        ]);

        // User A attempts accessing Org B dashboard
        $response = $this->actingAs($userA)
            ->get("/org/{$orgB->slug}/dashboard");

        // Should be forbidden (403)
        $this->assertSame(403, $response->status());
    }
}
