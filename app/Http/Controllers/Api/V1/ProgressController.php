<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\EventTracker;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function __construct(protected ProgressService $progressService) {}

    /**
     * Mark lesson as completed and/or update video playback resume position.
     */
    public function updateLessonProgress(Request $request, Lesson $lesson): JsonResponse
    {
        $request->validate([
            'is_completed' => 'nullable|boolean',
            'last_position_seconds' => 'nullable|integer|min:0',
            'last_watched_seconds' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $course = $lesson->section?->course ?? $lesson->course;

        if (! $course || ! $user->isEnrolledIn($course->id)) {
            return response()->json([
                'success' => false,
                'message' => 'কোর্সে এনরোল করা ছাড়া প্রগ্রেস আপডেট করা যাবে না।',
            ], 403);
        }

        $isCompleted = $request->boolean('is_completed', true);
        $lastPosition = $request->input('last_position_seconds') ?? $request->input('last_watched_seconds');

        $progressRecord = LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
            [
                'course_id' => $course->id,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
            ]
        );

        $courseProgress = $this->progressService->syncEnrollmentProgress($user, $course);

        if ($isCompleted) {
            try {
                app(EventTracker::class)->trackLessonCompleted($user, $lesson->id, $course->id);
            } catch (\Throwable $e) {
                // Non-blocking
            }
        }

        return response()->json([
            'success' => true,
            'message' => $courseProgress === 100 ? 'মাশাআল্লাহ! আপনি সফলভাবে সম্পূর্ণ কোর্সটি সম্পন্ন করেছেন।' : 'পাঠের প্রগ্রেস সংরক্ষিত হয়েছে।',
            'data' => [
                'lesson_id' => $lesson->id,
                'is_completed' => (bool) $progressRecord->is_completed,
                'last_watched_seconds' => (int) ($progressRecord->last_position_seconds ?? 0),
                'course_progress_percentage' => $courseProgress,
            ],
            'lesson_id' => $lesson->id,
            'is_completed' => (bool) $progressRecord->is_completed,
            'last_watched_seconds' => (int) ($progressRecord->last_position_seconds ?? 0),
            'course_progress_percentage' => $courseProgress,
        ]);
    }

    /**
     * Get user progress for a course.
     */
    public function getCourseProgress(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        if (! $user->isEnrolledIn($course->id)) {
            return response()->json([
                'success' => false,
                'message' => 'কোর্সে এনরোলমেন্ট নেই।',
            ], 403);
        }

        $completedLessonIds = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('is_completed', true)
            ->pluck('lesson_id');

        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'course_id' => $course->id,
                'is_enrolled' => true,
                'progress_percentage' => $enrollment?->progress ?? 0,
                'is_completed' => ($enrollment?->progress ?? 0) === 100,
                'completed_lesson_ids' => $completedLessonIds,
                'total_lessons' => $course->lessons()->count(),
                'completed_lessons' => $completedLessonIds->count(),
            ],
            'course_id' => $course->id,
            'progress_percentage' => $enrollment?->progress ?? 0,
            'is_completed' => ($enrollment?->progress ?? 0) === 100,
            'completed_lesson_ids' => $completedLessonIds,
            'total_lessons' => $course->lessons()->count(),
        ]);
    }
}
