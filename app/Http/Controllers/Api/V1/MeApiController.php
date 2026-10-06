<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\RecommendationService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CourseResource;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeApiController extends Controller
{
    /**
     * "My courses" with progress and the lesson to resume (continue learning).
     */
    public function courses(Request $request): JsonResponse
    {
        $user = $request->user();

        $enrollments = Enrollment::where('user_id', $user->id)
            ->whereIn('status', ['active', 'completed'])
            ->with(['course.instructor', 'course.instructor.teacher'])
            ->withCount(['course as dummy_count' => fn ($q) => $q->whereRaw('1 = 1')])
            ->latest('updated_at')
            ->get()
            ->filter(fn ($e) => $e->course !== null);

        $courseIds = $enrollments->pluck('course_id')->all();

        // Most recently touched lesson per course => resume target.
        $lastProgress = LessonProgress::where('user_id', $user->id)
            ->whereIn('course_id', $courseIds)
            ->with('lesson:id,title')
            ->orderByDesc('updated_at')
            ->get()
            ->unique('course_id')
            ->keyBy('course_id');

        $data = $enrollments->map(function (Enrollment $enrollment) use ($lastProgress, $request) {
            $last = $lastProgress->get($enrollment->course_id);

            return [
                'course' => (new CourseResource($enrollment->course))->toArray($request),
                'status' => $enrollment->status,
                'progress_percentage' => (int) $enrollment->progress,
                'enrolled_at' => $enrollment->enrolled_at?->toIso8601String(),
                'last_lesson' => $last && $last->lesson ? [
                    'lesson_id' => $last->lesson_id,
                    'title' => $last->lesson->title,
                    'is_completed' => (bool) $last->is_completed,
                    'last_position_seconds' => (int) $last->last_position_seconds,
                    'updated_at' => $last->updated_at?->toIso8601String(),
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Personalised recommendations ("আপনার জন্য"). Works for guests (popularity) and signed-in users.
     */
    public function recommendations(Request $request, RecommendationService $recommendations): JsonResponse
    {
        $limit = min(20, max(1, (int) $request->input('limit', 6)));
        $user = $request->user('sanctum');

        $courses = $recommendations->recommendForUser($user, $limit);

        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses->values()),
        ]);
    }
}
