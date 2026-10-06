<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CourseDetailResource;
use App\Http\Resources\Api\V1\CourseResource;
use App\Models\Course;
use App\Services\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display paginated list of published courses with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Course::where('status', 'published')
            ->with(['instructor', 'instructor.teacher'])
            ->withCount('lessons');

        // Search by title or description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });

            if ($request->user()) {
                app(EventTracker::class)->trackSearchPerformed($request->user(), $search, $query->count());
            }
        }

        // Category filter
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Level filter (beginner, intermediate, advanced)
        if ($level = $request->input('level')) {
            $query->where('level', $level);
        }

        // Free vs Paid filter
        if ($request->has('is_free')) {
            $query->where('is_free', $request->boolean('is_free'));
        }

        // Instructor filter
        if ($instructorId = $request->input('instructor_id')) {
            $query->where('instructor_id', $instructorId);
        }

        // Sorting
        match ($request->input('sort')) {
            'price_low' => $query->orderBy('price', 'asc'),
            'price_high' => $query->orderBy('price', 'desc'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };

        $perPage = min(50, max(5, (int) $request->input('per_page', 15)));
        $courses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses),
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ],
            'links' => [
                'first' => $courses->url(1),
                'last' => $courses->url($courses->lastPage()),
                'prev' => $courses->previousPageUrl(),
                'next' => $courses->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Display detailed course information with curriculum preview.
     */
    public function show(Request $request, Course $course): JsonResponse
    {
        if ($course->status !== 'published' && (! $request->user() || ! $request->user()->isAdmin() && $request->user()->id !== $course->instructor_id)) {
            return response()->json([
                'success' => false,
                'message' => 'কোর্সটি এখনও প্রকাশিত হয়নি।',
            ], 404);
        }

        $course->load([
            'instructor',
            'instructor.teacher',
            'sections' => fn ($q) => $q->orderBy('sort_order'),
            'sections.lessons' => fn ($q) => $q->orderBy('sort_order'),
        ]);

        if ($request->user()) {
            app(EventTracker::class)->trackCourseViewed($request->user(), $course->id);
        }

        return response()->json([
            'success' => true,
            'data' => new CourseDetailResource($course),
            'course' => new CourseDetailResource($course),
        ]);
    }
}
