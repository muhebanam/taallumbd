<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningEvent;
use App\Models\Lesson;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class TeacherAnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // If admin, can view all or filter by teacher, otherwise own courses
        $coursesQuery = Course::query();
        if (! $user->isAdmin()) {
            $coursesQuery->where('instructor_id', $user->id);
        }

        $courses = $coursesQuery->withCount(['enrollments', 'lessons', 'reviews'])->get();
        $courseIds = $courses->pluck('id')->toArray();

        // 1. Overview counts
        $totalStudents = Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id');
        $totalEnrollments = Enrollment::whereIn('course_id', $courseIds)->count();
        $completedEnrollments = Enrollment::whereIn('course_id', $courseIds)->where('status', 'completed')->count();
        $overallCompletionRate = $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 1) : 0;

        // 2. Revenue calculation (Phase 9 preparation)
        $grossRevenue = (float) Order::whereIn('course_id', $courseIds)
            ->whereIn('status', ['paid', 'approved', 'completed'])
            ->sum('amount');

        $teacherShareRate = 0.70; // 70% to instructor, 30% platform
        $estimatedEarnings = round($grossRevenue * $teacherShareRate, 2);

        // 3. Course-level breakdown
        $courseStats = $courses->map(function ($course) {
            $enrollmentsCount = $course->enrollments_count ?? 0;
            $completionsCount = Enrollment::where('course_id', $course->id)->where('status', 'completed')->count();
            $completionRate = $enrollmentsCount > 0 ? round(($completionsCount / $enrollmentsCount) * 100, 1) : 0;
            $revenue = (float) Order::where('course_id', $course->id)
                ->whereIn('status', ['paid', 'approved', 'completed'])
                ->sum('amount');

            $avgRating = round((float) Review::where('course_id', $course->id)->avg('rating'), 1) ?: 5.0;

            return [
                'id' => $course->id,
                'title' => $course->title,
                'thumbnail' => $course->thumbnail,
                'enrollments' => $enrollmentsCount,
                'completions' => $completionsCount,
                'completion_rate' => $completionRate,
                'revenue' => $revenue,
                'rating' => $avgRating,
                'reviews_count' => $course->reviews_count ?? 0,
            ];
        });

        // 4. Drop-off Lessons Analysis
        // Find lessons in instructor's courses and compare lesson_started vs lesson_completed
        $lessons = Lesson::whereIn('course_id', $courseIds)->with('course:id,title')->get();
        $lessonIds = $lessons->pluck('id')->toArray();

        $startedCounts = LearningEvent::where('event_type', 'lesson_started')
            ->whereIn('subject_id', $lessonIds)
            ->selectRaw('subject_id, count(*) as count')
            ->groupBy('subject_id')
            ->pluck('count', 'subject_id')
            ->toArray();

        $completedCounts = LearningEvent::where('event_type', 'lesson_completed')
            ->whereIn('subject_id', $lessonIds)
            ->selectRaw('subject_id, count(*) as count')
            ->groupBy('subject_id')
            ->pluck('count', 'subject_id')
            ->toArray();

        $dropOffLessons = $lessons->map(function ($lesson) use ($startedCounts, $completedCounts) {
            $starts = $startedCounts[$lesson->id] ?? 0;
            $completes = $completedCounts[$lesson->id] ?? 0;
            $dropOffRate = $starts > 0 ? round((($starts - $completes) / $starts) * 100, 1) : 0;

            return [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'course_title' => $lesson->course?->title,
                'starts' => $starts,
                'completes' => $completes,
                'drop_off_rate' => max(0, $dropOffRate),
            ];
        })
            ->sortByDesc('drop_off_rate')
            ->values()
            ->take(10);

        // 5. Ratings and feedback
        $recentReviews = Review::whereIn('course_id', $courseIds)
            ->with(['user:id,name,avatar', 'course:id,title'])
            ->latest()
            ->limit(5)
            ->get();

        $avgOverallRating = round((float) Review::whereIn('course_id', $courseIds)->avg('rating'), 1) ?: 5.0;

        return Inertia::render('Instructor/Analytics', [
            'overview' => [
                'total_students' => $totalStudents,
                'total_enrollments' => $totalEnrollments,
                'completion_rate' => $overallCompletionRate,
                'gross_revenue' => $grossRevenue,
                'estimated_earnings' => $estimatedEarnings,
                'avg_rating' => $avgOverallRating,
            ],
            'courses' => $courseStats,
            'drop_off_lessons' => $dropOffLessons,
            'recent_reviews' => $recentReviews,
        ]);
    }
}
