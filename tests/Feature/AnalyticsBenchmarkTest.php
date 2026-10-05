<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DailyMetric;
use App\Models\Enrollment;
use App\Models\LearningEvent;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsBenchmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_queries_execute_under_one_second_with_one_hundred_thousand_events(): void
    {
        // 1. Setup entities
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create(['role' => 'student']);

        $course = Course::factory()->create(['instructor_id' => $instructor->id, 'price' => 1500]);
        $lessons = Lesson::factory()->count(5)->create(['course_id' => $course->id]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress' => 40,
        ]);

        // Seed daily metrics for past 30 days
        for ($i = 0; $i < 30; $i++) {
            $d = now()->subDays($i)->toDateString();
            DailyMetric::create([
                'date' => $d,
                'metric_key' => 'dau',
                'metric_value' => rand(50, 200),
            ]);
            DailyMetric::create([
                'date' => $d,
                'metric_key' => 'mau',
                'metric_value' => rand(1500, 3000),
            ]);
            DailyMetric::create([
                'date' => $d,
                'metric_key' => 'revenue',
                'metric_value' => rand(5000, 25000),
            ]);
        }

        // 2. Bulk Seed 100,000 Learning Events in chunks
        $now = now()->format('Y-m-d H:i:s');
        $eventTypes = [
            'lesson_started',
            'lesson_completed',
            'video_progress',
            'quiz_attempted',
            'quran_read',
            'hadith_viewed',
            'fatwa_viewed',
            'search_performed',
        ];

        $totalEvents = 100000;
        $chunkSize = 5000;
        $lessonIds = $lessons->pluck('id')->toArray();

        for ($c = 0; $c < ($totalEvents / $chunkSize); $c++) {
            $chunk = [];
            for ($i = 0; $i < $chunkSize; $i++) {
                $type = $eventTypes[($c * $chunkSize + $i) % count($eventTypes)];
                $isStudent = ($i % 50 === 0); // disperse some events to test student
                $chunk[] = [
                    'user_id' => $isStudent ? $student->id : rand(100, 2000),
                    'event_type' => $type,
                    'subject_type' => 'lesson',
                    'subject_id' => $lessonIds[$i % count($lessonIds)],
                    'course_id' => $course->id,
                    'properties' => '{"duration_seconds":120,"percent":50}',
                    'session_id' => 'sess_'.rand(1000, 9999),
                    'occurred_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            LearningEvent::insert($chunk);
        }

        $this->assertEquals(100000, LearningEvent::count());

        // Warm up HTTP routing and middleware stack
        $this->actingAs($student)->get('/dashboard');

        // 3. Benchmark Student Analytics (< 1.0s)
        $start = microtime(true);
        $studentResponse = $this->actingAs($student)->get('/dashboard/analytics');
        $studentDuration = microtime(true) - $start;

        $studentResponse->assertStatus(200);
        $this->assertLessThan(1.0, $studentDuration, "Student Dashboard took {$studentDuration}s, expected < 1.0s");

        // 4. Benchmark Teacher Analytics (< 1.0s)
        $start = microtime(true);
        $teacherResponse = $this->actingAs($instructor)->get('/instructor/analytics');
        $teacherDuration = microtime(true) - $start;

        $teacherResponse->assertStatus(200);
        $this->assertLessThan(1.0, $teacherDuration, "Teacher Dashboard took {$teacherDuration}s, expected < 1.0s");

        // 5. Benchmark Admin Analytics (< 1.0s)
        $start = microtime(true);
        $adminResponse = $this->actingAs($admin)->get('/admin/analytics');
        $adminDuration = microtime(true) - $start;

        $adminResponse->assertStatus(200);
        $this->assertLessThan(1.0, $adminDuration, "Admin Dashboard took {$adminDuration}s, expected < 1.0s");

        fwrite(STDERR, sprintf(
            "\n⚡ [PERFORMANCE BENCHMARK on 100,000 EVENTS]\n- Student Analytics: %.3f sec (Pass < 1.0s)\n- Teacher Analytics: %.3f sec (Pass < 1.0s)\n- Admin Analytics:   %.3f sec (Pass < 1.0s)\n",
            $studentDuration,
            $teacherDuration,
            $adminDuration
        ));
    }
}
