<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CourseResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Fatwa;
use App\Models\LessonComment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorApiController extends Controller
{
    /**
     * Instructor courses list with stats.
     */
    public function courses(Request $request): JsonResponse
    {
        $user = $request->user();

        $courses = Course::where('instructor_id', $user->id)
            ->withCount(['lessons', 'enrollments'])
            ->with(['category'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ],
        ]);
    }

    /**
     * Instructor analytics overview.
     */
    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();

        $courseIds = Course::where('instructor_id', $user->id)->pluck('id');
        $totalCourses = $courseIds->count();

        $enrollments = Enrollment::whereIn('course_id', $courseIds);
        $totalStudents = (clone $enrollments)->distinct('user_id')->count('user_id');
        $completedStudents = (clone $enrollments)->where('status', 'completed')->count();
        $activeStudents = (clone $enrollments)->where('status', 'active')->count();

        $avgProgress = (clone $enrollments)->avg('progress') ?? 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_courses' => $totalCourses,
                'total_students' => $totalStudents,
                'active_students' => $activeStudents,
                'completed_students' => $completedStudents,
                'average_completion_rate' => round($avgProgress, 1),
            ],
        ]);
    }

    /**
     * Students enrolled in instructor's courses.
     */
    public function students(Request $request): JsonResponse
    {
        $user = $request->user();
        $courseIds = Course::where('instructor_id', $user->id)->pluck('id');

        $enrollments = Enrollment::whereIn('course_id', $courseIds)
            ->with(['user:id,name,email,avatar', 'course:id,title'])
            ->latest('enrolled_at')
            ->paginate(20);

        $data = $enrollments->map(function ($enrollment) {
            return [
                'id' => $enrollment->id,
                'student' => [
                    'id' => $enrollment->user->id,
                    'name' => $enrollment->user->name,
                    'email' => $enrollment->user->email,
                    'avatar' => $enrollment->user->avatar,
                ],
                'course' => [
                    'id' => $enrollment->course->id,
                    'title' => $enrollment->course->title,
                ],
                'progress' => (int) $enrollment->progress,
                'status' => $enrollment->status,
                'enrolled_at' => $enrollment->enrolled_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
            ],
        ]);
    }

    /**
     * Questions asked by students on instructor's lessons or assigned fatwas.
     */
    public function questions(Request $request): JsonResponse
    {
        $user = $request->user();
        $courseIds = Course::where('instructor_id', $user->id)->pluck('id');

        // Lesson comments on instructor's courses
        $comments = LessonComment::whereHas('lesson', function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds);
        })
            ->whereNull('parent_id')
            ->with(['user:id,name,avatar', 'lesson:id,title,course_id', 'replies'])
            ->latest()
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $comments,
        ]);
    }

    /**
     * Reply to a student question.
     */
    public function replyQuestion(Request $request, LessonComment $comment): JsonResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'min:3'],
        ]);

        $reply = LessonComment::create([
            'user_id' => $request->user()->id,
            'lesson_id' => $comment->lesson_id,
            'parent_id' => $comment->id,
            'body' => $request->input('body'),
        ]);

        $reply->load('user:id,name,role,avatar');

        return response()->json([
            'success' => true,
            'message' => 'উত্তর সফলভাবে দেওয়া হয়েছে।',
            'data' => $reply,
        ]);
    }
}
