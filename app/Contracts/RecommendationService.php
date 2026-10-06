<?php

namespace App\Contracts;

use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Collection;

interface RecommendationService
{
    /**
     * Get personalized course recommendations for a user ("আপনার জন্য").
     * Excludes courses the user is already enrolled in.
     *
     * @return Collection<Course>
     */
    public function recommendForUser(?User $user, int $limit = 6): Collection;

    /**
     * Get "Learners who enrolled in this course also enrolled in" ("যারা এটা নিয়েছে তারা এটাও নিয়েছে").
     * Excludes the current course and any courses the user is already enrolled in.
     *
     * @return Collection<Course>
     */
    public function recommendLearnersAlsoEnrolled(Course $course, ?User $user = null, int $limit = 4): Collection;

    /**
     * Get similar courses by category, scholar affinity, and level.
     *
     * @return Collection<Course>
     */
    public function recommendSimilarCourses(Course $course, ?User $user = null, int $limit = 4): Collection;
}
