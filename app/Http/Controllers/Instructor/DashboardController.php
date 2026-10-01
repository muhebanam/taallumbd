<?php
namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\QuizAttempt;
use App\Models\Review;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $courseIds = $user->courses()->pluck('id');

        $courseEarnings = (float) Order::whereIn('course_id', $courseIds)->where('status', 'paid')->sum('amount');
        $quizAttemptsCount = QuizAttempt::whereHas('quiz', fn ($q) => $q->whereIn('course_id', $courseIds))->count();
        $averageRating = (float) Review::whereIn('course_id', $courseIds)->avg('rating');
        $pendingAssignmentsCount = AssignmentSubmission::whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
            ->where('status', 'submitted')->count();

        return Inertia::render('Instructor/Dashboard', [
            'courses' => $user->courses()->withCount(['lessons', 'enrollments'])->latest()->get(),
            'stats' => [
                'my_courses_count' => $courseIds->count(),
                'my_students_count' => Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id'),
                'course_earnings' => $courseEarnings,
                'pending_assignments_count' => $pendingAssignmentsCount,
                'quiz_attempts_count' => $quizAttemptsCount,
                'average_rating' => $averageRating,
            ],
            'pendingSubmissions' => AssignmentSubmission::whereHas('assignment', fn ($q) => $q->whereIn('course_id', $courseIds))
                ->where('status', 'submitted')->with(['user:id,name', 'assignment:id,title,course_id'])->latest()->take(10)->get(),
        ]);
    }
}
