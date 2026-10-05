<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DailyMetric;
use App\Models\Enrollment;
use App\Models\LearningEvent;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnalyticsDashboardsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_learning_analytics_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create();
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress' => 50,
        ]);

        LearningEvent::create([
            'user_id' => $student->id,
            'event_type' => 'lesson_completed',
            'course_id' => $course->id,
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($student)->get('/dashboard/analytics');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Student/Analytics')
            ->has('metrics')
            ->has('weekly_time')
            ->has('strengths')
            ->has('weaknesses')
            ->has('recent_events')
            ->where('metrics.current_streak', 1)
            ->where('metrics.total_courses', 1)
        );
    }

    public function test_guest_is_redirected_from_student_analytics(): void
    {
        $response = $this->get('/dashboard/analytics');
        $response->assertRedirect('/login');
    }

    public function test_instructor_can_view_teacher_analytics(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['instructor_id' => $instructor->id, 'price' => 2000]);
        $lesson = Lesson::factory()->create(['course_id' => $course->id]);

        $student = User::factory()->create();
        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress' => 0,
        ]);

        LearningEvent::create([
            'user_id' => $student->id,
            'event_type' => 'lesson_started',
            'subject_type' => 'lesson',
            'subject_id' => $lesson->id,
            'course_id' => $course->id,
            'occurred_at' => now(),
        ]);

        $response = $this->actingAs($instructor)->get('/instructor/analytics');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Instructor/Analytics')
            ->has('overview')
            ->has('courses')
            ->has('drop_off_lessons')
            ->where('overview.total_students', 1)
            ->where('overview.total_enrollments', 1)
        );
    }

    public function test_regular_student_cannot_view_instructor_analytics(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $response = $this->actingAs($student)->get('/instructor/analytics');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_business_analytics_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        DailyMetric::create([
            'date' => now()->toDateString(),
            'metric_key' => 'dau',
            'metric_value' => 45,
        ]);
        DailyMetric::create([
            'date' => now()->toDateString(),
            'metric_key' => 'mau',
            'metric_value' => 300,
        ]);

        $response = $this->actingAs($admin)->get('/admin/analytics');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Analytics')
            ->has('overview')
            ->has('revenue_trend')
            ->has('dau_trend')
            ->has('funnel')
            ->has('cohort_matrix')
            ->has('top_courses')
            ->has('top_scholars')
            ->where('overview.dau', 45)
            ->where('overview.mau', 300)
        );
    }

    public function test_non_admin_cannot_view_business_analytics(): void
    {
        $instructor = User::factory()->create(['role' => 'instructor']);
        $response = $this->actingAs($instructor)->get('/admin/analytics');
        $response->assertStatus(403);
    }

    public function test_admin_can_export_daily_metrics_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        DailyMetric::create([
            'date' => now()->subDay()->toDateString(),
            'metric_key' => 'dau',
            'metric_value' => 50,
        ]);
        DailyMetric::create([
            'date' => now()->subDay()->toDateString(),
            'metric_key' => 'revenue',
            'metric_value' => 15000,
        ]);

        $response = $this->actingAs($admin)->get('/admin/analytics/export');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
    }
}
