<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\LessonResource;
use App\Http\Resources\Api\V1\SectionResource;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    /**
     * Get complete curriculum outline for a course.
     */
    public function curriculum(Request $request, Course $course): JsonResponse
    {
        $sections = $course->sections()
            ->orderBy('sort_order')
            ->with(['lessons' => fn ($q) => $q->orderBy('sort_order')])
            ->get();

        return response()->json([
            'success' => true,
            'course_id' => $course->id,
            'course_title' => $course->title,
            'sections' => SectionResource::collection($sections),
        ]);
    }

    /**
     * Get single lesson content with video streaming permissions.
     */
    public function lesson(Request $request, Lesson $lesson): JsonResponse
    {
        $lesson->load(['section.course', 'quiz.questions.options']);
        $course = $lesson->section?->course;

        $user = $request->user();
        $isEnrolled = false;

        if ($user) {
            $isEnrolled = $user->isEnrolledIn($course?->id) || $user->id === $course?->instructor_id || $user->isAdmin();
        }

        $isFree = (bool) ($lesson->is_preview || $lesson->is_free || $lesson->is_free_preview);

        if (! $isFree && ! $isEnrolled) {
            return response()->json([
                'success' => false,
                'message' => 'এই পাঠটি দেখতে অনুগ্রহ করে কোর্সে এনরোল করুন।',
                'is_locked' => true,
                'lesson' => [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'duration' => $lesson->duration,
                    'is_free_preview' => false,
                ],
            ], 403);
        }

        if ($user && $course) {
            app(EventTracker::class)->trackLessonStarted($user, $lesson->id, $course->id);
        }

        return response()->json([
            'success' => true,
            'is_locked' => false,
            'data' => new LessonResource($lesson),
            'lesson' => new LessonResource($lesson),
        ]);
    }
}
