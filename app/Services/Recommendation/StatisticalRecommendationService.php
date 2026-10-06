<?php

namespace App\Services\Recommendation;

use App\AI\Contracts\EmbeddingClient;
use App\Contracts\RecommendationService;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticalRecommendationService implements RecommendationService
{
    public function __construct(
        protected ?EmbeddingClient $embeddingClient = null
    ) {}

    /**
     * Get personalized course recommendations for a user ("আপনার জন্য").
     * Always excludes already enrolled courses.
     */
    public function recommendForUser(?User $user, int $limit = 6): Collection
    {
        $enrolledCourseIds = [];

        if ($user) {
            $enrolledCourseIds = Enrollment::where('user_id', $user->id)
                ->pluck('course_id')
                ->all();
        }

        // Base query: published courses excluding already enrolled ones
        $baseQuery = Course::query()
            ->whereIn('status', ['published', 'coming_soon'])
            ->when(! empty($enrolledCourseIds), function ($q) use ($enrolledCourseIds) {
                $q->whereNotIn('id', $enrolledCourseIds);
            })
            ->with(['instructor', 'category'])
            ->withCount('enrollments');

        // If user is guest or has no enrollments yet, return popularity + freshness
        if (! $user || empty($enrolledCourseIds)) {
            return $this->getPopularAndFreshCourses($baseQuery, $limit);
        }

        // 1. Category & Scholar Affinity: extract favorite categories and instructors
        $userCourses = Course::whereIn('id', $enrolledCourseIds)->get();
        $preferredCategoryIds = $userCourses->pluck('category_id')->filter()->unique()->all();
        $preferredInstructorIds = $userCourses->pluck('instructor_id')->filter()->unique()->all();

        // 2. Co-enrollment ("Learners who took what user took also took X")
        $coUserIds = Enrollment::whereIn('course_id', $enrolledCourseIds)
            ->where('user_id', '!=', $user->id)
            ->pluck('user_id')
            ->unique()
            ->take(500)
            ->all();

        $coEnrolledWeights = [];
        if (! empty($coUserIds)) {
            $coEnrolledWeights = Enrollment::whereIn('user_id', $coUserIds)
                ->whereNotIn('course_id', $enrolledCourseIds)
                ->select('course_id', DB::raw('count(*) as aggregate_count'))
                ->groupBy('course_id')
                ->pluck('aggregate_count', 'course_id')
                ->all();
        }

        // 3. User interest profile for Embedding Semantic Similarity (Recommendation v2)
        $userEmbedding = null;
        if ($this->embeddingClient && config('ai.enabled', true)) {
            $userCorpus = $userCourses->pluck('title')->implode(' ');
            if (mb_strlen($userCorpus) > 5) {
                $userEmbedding = $this->embeddingClient->embedText($userCorpus);
            }
        }

        // Fetch candidate courses
        $candidates = $baseQuery->get();

        if ($candidates->isEmpty()) {
            return collect();
        }

        // Score each candidate
        $scored = $candidates->map(function ($course) use ($preferredCategoryIds, $preferredInstructorIds, $coEnrolledWeights, $userEmbedding) {
            $score = 0.0;

            // Factor 1: Co-enrollment weight (Highest signal: 10 points per co-enrollment)
            if (isset($coEnrolledWeights[$course->id])) {
                $score += min(50, $coEnrolledWeights[$course->id] * 10);
            }

            // Factor 2: Category Affinity (20 points if in user's learned category)
            if (in_array($course->category_id, $preferredCategoryIds, true)) {
                $score += 20;
            }

            // Factor 3: Scholar Affinity (15 points if taught by favorite scholar)
            if (in_array($course->instructor_id, $preferredInstructorIds, true)) {
                $score += 15;
            }

            // Factor 4: Platform Popularity (Enrollment count: up to 25 points)
            $score += min(25, ($course->enrollments_count ?? 0) * 2);

            // Factor 5: Freshness (Courses created within last 60 days get up to 10 points)
            if ($course->created_at && $course->created_at->diffInDays(now()) <= 60) {
                $score += max(0, 10 - ($course->created_at->diffInDays(now()) / 6));
            }

            // Factor 6: Embedding Semantic Similarity (Recommendation v2: up to 25 points)
            if (! empty($userEmbedding) && $this->embeddingClient) {
                $courseEmbedding = $this->embeddingClient->embedText($course->title.' '.$course->short_description);
                $sim = $this->cosineSimilarity($userEmbedding, $courseEmbedding);
                if ($sim > 0) {
                    $score += ($sim * 25);
                }
            }

            $course->recommendation_score = $score;

            return $course;
        });

        return $scored->sortByDesc('recommendation_score')
            ->values()
            ->take($limit);
    }

    /**
     * Get "Learners who enrolled in this course also enrolled in" ("যারা এটা নিয়েছে তারা এটাও নিয়েছে").
     */
    public function recommendLearnersAlsoEnrolled(Course $course, ?User $user = null, int $limit = 4): Collection
    {
        $excludeIds = [$course->id];

        if ($user) {
            $userEnrolled = Enrollment::where('user_id', $user->id)
                ->pluck('course_id')
                ->all();
            $excludeIds = array_unique(array_merge($excludeIds, $userEnrolled));
        }

        // Find users who enrolled in this specific course
        $enrolledUserIds = Enrollment::where('course_id', $course->id)
            ->when($user, fn ($q) => $q->where('user_id', '!=', $user->id))
            ->pluck('user_id')
            ->unique()
            ->take(300)
            ->all();

        $coEnrolledIds = [];
        if (! empty($enrolledUserIds)) {
            $coEnrolledIds = Enrollment::whereIn('user_id', $enrolledUserIds)
                ->whereNotIn('course_id', $excludeIds)
                ->select('course_id', DB::raw('count(*) as aggregate_count'))
                ->groupBy('course_id')
                ->orderByDesc('aggregate_count')
                ->take($limit)
                ->pluck('course_id')
                ->all();
        }

        $results = collect();
        if (! empty($coEnrolledIds)) {
            $results = Course::whereIn('id', $coEnrolledIds)
                ->whereIn('status', ['published', 'coming_soon'])
                ->with(['instructor', 'category'])
                ->withCount('enrollments')
                ->get();
        }

        // Fallback: If not enough co-enrollments, backfill with same category / same instructor
        if ($results->count() < $limit) {
            $backfillLimit = $limit - $results->count();
            $backfillExclude = array_merge($excludeIds, $results->pluck('id')->all());

            $backfill = Course::query()
                ->whereIn('status', ['published', 'coming_soon'])
                ->whereNotIn('id', $backfillExclude)
                ->where(function ($q) use ($course) {
                    $q->where('category_id', $course->category_id)
                        ->orWhere('instructor_id', $course->instructor_id);
                })
                ->with(['instructor', 'category'])
                ->withCount('enrollments')
                ->orderByDesc('enrollments_count')
                ->take($backfillLimit)
                ->get();

            $results = $results->merge($backfill);
        }

        return $results->take($limit);
    }

    /**
     * Get similar courses by category, scholar affinity, and level.
     */
    public function recommendSimilarCourses(Course $course, ?User $user = null, int $limit = 4): Collection
    {
        $excludeIds = [$course->id];

        if ($user) {
            $userEnrolled = Enrollment::where('user_id', $user->id)
                ->pluck('course_id')
                ->all();
            $excludeIds = array_unique(array_merge($excludeIds, $userEnrolled));
        }

        return Course::query()
            ->whereIn('status', ['published', 'coming_soon'])
            ->whereNotIn('id', $excludeIds)
            ->where(function ($q) use ($course) {
                $q->where('category_id', $course->category_id)
                    ->orWhere('instructor_id', $course->instructor_id)
                    ->orWhere('level', $course->level);
            })
            ->with(['instructor', 'category'])
            ->withCount('enrollments')
            ->orderByRaw('CASE WHEN category_id = ? THEN 1 ELSE 0 END DESC', [$course->category_id])
            ->orderByRaw('CASE WHEN instructor_id = ? THEN 1 ELSE 0 END DESC', [$course->instructor_id])
            ->orderByDesc('enrollments_count')
            ->take($limit)
            ->get();
    }

    /**
     * Helper to retrieve popular and fresh courses when user has no prior history.
     */
    protected function getPopularAndFreshCourses($query, int $limit): Collection
    {
        return $query->orderByDesc('enrollments_count')
            ->orderByDesc('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Cosine similarity between two float vectors.
     */
    protected function cosineSimilarity(array $vecA, array $vecB): float
    {
        $count = count($vecA);
        if ($count === 0 || $count !== count($vecB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $count; $i++) {
            $a = (float) $vecA[$i];
            $b = (float) $vecB[$i];
            $dotProduct += $a * $b;
            $normA += $a * $a;
            $normB += $b * $b;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
