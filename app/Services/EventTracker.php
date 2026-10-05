<?php

namespace App\Services;

use App\Jobs\IngestLearningEventsBatchJob;
use App\Models\LearningEvent;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

class EventTracker
{
    /**
     * Keys to remove from properties to avoid logging PII or sensitive data.
     */
    protected static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'token',
        'access_token',
        'secret',
        'credit_card',
        'card_number',
        'card',
        'cvv',
        'cvc',
        'pin',
        'email',
        'phone',
        'mobile',
        'authorization',
        'cookie',
    ];

    /**
     * Track a single learning event.
     */
    public function track(
        string $eventType,
        ?int $userId = null,
        array $properties = [],
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?int $courseId = null,
        ?string $sessionId = null,
        ?\DateTimeInterface $occurredAt = null,
        bool $async = true
    ): void {
        $event = [
            'user_id' => $userId,
            'event_type' => $eventType,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'course_id' => $courseId,
            'properties' => $this->sanitizeProperties($properties),
            'session_id' => $sessionId ?: session()->getId(),
            'occurred_at' => $occurredAt ? $occurredAt->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
        ];

        $this->trackBatch([$event], $async);
    }

    /**
     * Synchronous track for immediate insertion (useful in tests or critical sync flows).
     */
    public function trackSync(
        string $eventType,
        ?int $userId = null,
        array $properties = [],
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?int $courseId = null,
        ?string $sessionId = null,
        ?\DateTimeInterface $occurredAt = null
    ): void {
        $this->track(
            eventType: $eventType,
            userId: $userId,
            properties: $properties,
            subjectType: $subjectType,
            subjectId: $subjectId,
            courseId: $courseId,
            sessionId: $sessionId,
            occurredAt: $occurredAt,
            async: false
        );
    }

    /**
     * Batch track multiple learning events.
     */
    public function trackBatch(array $events, bool $async = true): void
    {
        if (empty($events)) {
            return;
        }

        $sanitizedEvents = [];
        $now = now()->format('Y-m-d H:i:s');

        foreach ($events as $event) {
            $props = $event['properties'] ?? [];
            if (is_string($props)) {
                $decoded = json_decode($props, true);
                $props = is_array($decoded) ? $decoded : [];
            }

            $sanitizedEvents[] = [
                'user_id' => $event['user_id'] ?? null,
                'event_type' => (string) ($event['event_type'] ?? 'unknown'),
                'subject_type' => $event['subject_type'] ?? null,
                'subject_id' => $event['subject_id'] ?? null,
                'course_id' => $event['course_id'] ?? null,
                'properties' => $this->sanitizeProperties($props),
                'session_id' => $event['session_id'] ?? (session()->isStarted() ? session()->getId() : null),
                'occurred_at' => $event['occurred_at'] ?? $now,
            ];
        }

        // When in testing environment or async is false, insert directly
        if (! $async || App::environment('testing')) {
            $formatted = [];
            $currentTime = now();
            foreach ($sanitizedEvents as $item) {
                $formatted[] = [
                    'user_id' => $item['user_id'],
                    'event_type' => $item['event_type'],
                    'subject_type' => $item['subject_type'],
                    'subject_id' => $item['subject_id'],
                    'course_id' => $item['course_id'],
                    'properties' => json_encode($item['properties']),
                    'session_id' => $item['session_id'],
                    'occurred_at' => $item['occurred_at'],
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                ];
            }
            foreach (array_chunk($formatted, 250) as $chunk) {
                LearningEvent::insert($chunk);
            }
        } else {
            IngestLearningEventsBatchJob::dispatch($sanitizedEvents);
        }
    }

    /**
     * Minimize PII and sanitize properties.
     */
    public function sanitizeProperties(array $properties): array
    {
        $sanitized = [];

        foreach ($properties as $key => $value) {
            $lowerKey = strtolower((string) $key);

            // Strip sensitive fields
            foreach (self::$sensitiveKeys as $sensitive) {
                if (str_contains($lowerKey, $sensitive)) {
                    continue 2;
                }
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeProperties($value);
            } elseif (is_string($value)) {
                // Truncate overly long text fields to avoid database bloat and memory pressure
                $sanitized[$key] = Str::limit(trim($value), 300);
            } elseif (is_numeric($value) || is_bool($value) || is_null($value)) {
                $sanitized[$key] = $value;
            } else {
                $sanitized[$key] = Str::limit((string) $value, 200);
            }
        }

        return $sanitized;
    }

    // ==========================================
    // Dedicated Event Helper Methods
    // ==========================================

    public function trackLessonStarted(?User $user, int $lessonId, int $courseId, array $extra = []): void
    {
        $this->track(
            eventType: 'lesson_started',
            userId: $user?->id,
            properties: $extra,
            subjectType: 'lesson',
            subjectId: $lessonId,
            courseId: $courseId
        );
    }

    public function trackLessonCompleted(?User $user, int $lessonId, int $courseId, array $extra = []): void
    {
        $this->track(
            eventType: 'lesson_completed',
            userId: $user?->id,
            properties: $extra,
            subjectType: 'lesson',
            subjectId: $lessonId,
            courseId: $courseId
        );
    }

    public function trackVideoProgress(?User $user, int $lessonId, int $courseId, int $percent, int $durationSeconds = 0, array $extra = []): void
    {
        // Accepted milestone checkpoints: 25, 50, 75, 100
        $this->track(
            eventType: 'video_progress',
            userId: $user?->id,
            properties: array_merge($extra, [
                'percent' => $percent,
                'duration_seconds' => $durationSeconds,
            ]),
            subjectType: 'lesson',
            subjectId: $lessonId,
            courseId: $courseId
        );
    }

    public function trackQuizAttempted(?User $user, int $quizId, int $courseId, array $extra = []): void
    {
        $this->track(
            eventType: 'quiz_attempted',
            userId: $user?->id,
            properties: $extra,
            subjectType: 'quiz',
            subjectId: $quizId,
            courseId: $courseId
        );
    }

    public function trackQuizPassed(?User $user, int $quizId, int $courseId, float $score, array $extra = []): void
    {
        $this->track(
            eventType: 'quiz_passed',
            userId: $user?->id,
            properties: array_merge($extra, ['score' => $score]),
            subjectType: 'quiz',
            subjectId: $quizId,
            courseId: $courseId
        );
    }

    public function trackAssignmentSubmitted(?User $user, int $assignmentId, int $courseId, array $extra = []): void
    {
        $this->track(
            eventType: 'assignment_submitted',
            userId: $user?->id,
            properties: $extra,
            subjectType: 'assignment',
            subjectId: $assignmentId,
            courseId: $courseId
        );
    }

    public function trackCourseEnrolled(?User $user, int $courseId, array $extra = []): void
    {
        $this->track(
            eventType: 'course_enrolled',
            userId: $user?->id,
            properties: $extra,
            subjectType: 'course',
            subjectId: $courseId,
            courseId: $courseId
        );
    }

    public function trackCourseCompleted(?User $user, int $courseId, array $extra = []): void
    {
        $this->track(
            eventType: 'course_completed',
            userId: $user?->id,
            properties: $extra,
            subjectType: 'course',
            subjectId: $courseId,
            courseId: $courseId
        );
    }

    public function trackQuranRead(?User $user, int $surahNumber, ?int $ayahNumber = null, int $durationSeconds = 0, array $extra = []): void
    {
        $this->track(
            eventType: 'quran_read',
            userId: $user?->id,
            properties: array_merge($extra, [
                'surah' => $surahNumber,
                'ayah' => $ayahNumber,
                'duration_seconds' => $durationSeconds,
            ]),
            subjectType: 'quran',
            subjectId: $surahNumber
        );
    }

    public function trackHadithViewed(?User $user, string $bookSlug, int $hadithNumber, array $extra = []): void
    {
        $this->track(
            eventType: 'hadith_viewed',
            userId: $user?->id,
            properties: array_merge($extra, [
                'book' => $bookSlug,
                'hadith_number' => $hadithNumber,
            ]),
            subjectType: 'hadith',
            subjectId: $hadithNumber
        );
    }

    public function trackFatwaViewed(?User $user, int $fatwaId, ?string $category = null, array $extra = []): void
    {
        $this->track(
            eventType: 'fatwa_viewed',
            userId: $user?->id,
            properties: array_merge($extra, [
                'category' => $category,
            ]),
            subjectType: 'fatwa',
            subjectId: $fatwaId
        );
    }

    public function trackSearchPerformed(?User $user, string $query, int $resultsCount = 0, array $extra = []): void
    {
        $this->track(
            eventType: 'search_performed',
            userId: $user?->id,
            properties: array_merge($extra, [
                'query' => Str::limit($query, 100),
                'results_count' => $resultsCount,
            ]),
            subjectType: 'search'
        );
    }

    public function trackCheckoutStarted(?User $user, int $courseId, float $amount, array $extra = []): void
    {
        $this->track(
            eventType: 'checkout_started',
            userId: $user?->id,
            properties: array_merge($extra, [
                'amount' => $amount,
            ]),
            subjectType: 'course',
            subjectId: $courseId,
            courseId: $courseId
        );
    }

    public function trackCheckoutCompleted(?User $user, int $orderId, int $courseId, float $amount, ?string $gateway = null, array $extra = []): void
    {
        $this->track(
            eventType: 'checkout_completed',
            userId: $user?->id,
            properties: array_merge($extra, [
                'order_id' => $orderId,
                'amount' => $amount,
                'payment_method' => $gateway,
            ]),
            subjectType: 'order',
            subjectId: $orderId,
            courseId: $courseId
        );
    }
}
