<?php

namespace Tests\Feature;

use App\Models\LearningEvent;
use App\Models\User;
use App\Services\EventTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningEventTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_ingest_single_learning_event_via_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/events', [
            'event_type' => 'lesson_started',
            'course_id' => 1,
            'subject_type' => 'lesson',
            'subject_id' => 10,
            'properties' => [
                'device' => 'desktop',
                'browser' => 'Chrome',
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
            ]);

        $this->assertDatabaseHas('learning_events', [
            'user_id' => $user->id,
            'event_type' => 'lesson_started',
            'course_id' => 1,
            'subject_type' => 'lesson',
            'subject_id' => 10,
        ]);
    }

    public function test_can_ingest_batch_of_learning_events(): void
    {
        $user = User::factory()->create();

        $events = [
            [
                'event_type' => 'video_progress',
                'course_id' => 1,
                'subject_type' => 'lesson',
                'subject_id' => 5,
                'properties' => ['percent' => 25, 'duration_seconds' => 120],
            ],
            [
                'event_type' => 'video_progress',
                'course_id' => 1,
                'subject_type' => 'lesson',
                'subject_id' => 5,
                'properties' => ['percent' => 50, 'duration_seconds' => 240],
            ],
            [
                'event_type' => 'quran_read',
                'subject_type' => 'quran',
                'subject_id' => 1,
                'properties' => ['surah' => 1, 'ayah' => 7],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/events', [
            'events' => $events,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 3,
            ]);

        $this->assertEquals(3, LearningEvent::where('user_id', $user->id)->count());
    }

    public function test_pii_sanitization_removes_sensitive_keys(): void
    {
        $tracker = app(EventTracker::class);

        $dirtyProperties = [
            'password' => 'secret123',
            'auth_token' => 'bearer-token-abc',
            'credit_card' => '4111222233334444',
            'cvv' => '123',
            'email' => 'user@example.com',
            'phone' => '01700000000',
            'allowed_feature' => 'dark_mode',
            'score' => 95,
        ];

        $sanitized = $tracker->sanitizeProperties($dirtyProperties);

        $this->assertArrayNotHasKey('password', $sanitized);
        $this->assertArrayNotHasKey('auth_token', $sanitized);
        $this->assertArrayNotHasKey('credit_card', $sanitized);
        $this->assertArrayNotHasKey('cvv', $sanitized);
        $this->assertArrayNotHasKey('email', $sanitized);
        $this->assertArrayNotHasKey('phone', $sanitized);
        $this->assertEquals('dark_mode', $sanitized['allowed_feature']);
        $this->assertEquals(95, $sanitized['score']);
    }

    public function test_event_tracker_service_tracks_all_required_events(): void
    {
        $user = User::factory()->create();
        $tracker = app(EventTracker::class);

        $tracker->trackLessonStarted($user, 1, 10);
        $tracker->trackLessonCompleted($user, 1, 10);
        $tracker->trackVideoProgress($user, 1, 10, 50, 300);
        $tracker->trackQuizAttempted($user, 2, 10);
        $tracker->trackQuizPassed($user, 2, 10, 90.0);
        $tracker->trackAssignmentSubmitted($user, 3, 10);
        $tracker->trackCourseEnrolled($user, 10);
        $tracker->trackCourseCompleted($user, 10);
        $tracker->trackQuranRead($user, 1, 1, 60);
        $tracker->trackHadithViewed($user, 'bukhari', 1);
        $tracker->trackFatwaViewed($user, 5, 'ibadah');
        $tracker->trackSearchPerformed($user, 'Salah rules', 12);
        $tracker->trackCheckoutStarted($user, 10, 1500.0);
        $tracker->trackCheckoutCompleted($user, 100, 10, 1500.0, 'bkash');

        $this->assertEquals(14, LearningEvent::where('user_id', $user->id)->count());
        $this->assertDatabaseHas('learning_events', ['event_type' => 'video_progress']);
        $this->assertDatabaseHas('learning_events', ['event_type' => 'quiz_passed']);
        $this->assertDatabaseHas('learning_events', ['event_type' => 'checkout_completed']);
    }

    public function test_user_can_export_and_delete_their_learning_data_for_privacy(): void
    {
        $user = User::factory()->create();
        $tracker = app(EventTracker::class);
        $tracker->trackQuranRead($user, 114, 1);
        $tracker->trackLessonStarted($user, 2, 1);

        // 1. Export personal data
        $exportResponse = $this->actingAs($user)->get('/profile/privacy/export-data');
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('Content-Type', 'application/json');

        // 2. Anonymize personal history
        $deleteResponse = $this->actingAs($user)->deleteJson('/profile/privacy/delete-data');
        $deleteResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        // Events are dissociated from user_id for privacy
        $this->assertEquals(0, LearningEvent::where('user_id', $user->id)->count());
        $this->assertEquals(2, LearningEvent::whereNull('user_id')->count());
    }
}
