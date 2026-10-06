<?php

namespace App\Http\Controllers\Student;

use App\Contracts\RecommendationService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, RecommendationService $recommendationService)
    {
        $user = $request->user();
        $recommendedCourses = $recommendationService->recommendForUser($user, 4);

        $learningPathEnrollments = $user->learningPathEnrollments()
            ->with(['learningPath.courses'])
            ->latest()
            ->get();

        return Inertia::render('Student/Dashboard', [
            'enrollments' => $user->enrollments()->with(['course' => fn ($q) => $q->with('instructor:id,name')->withCount('lessons')])->latest()->get(),
            'certificates' => $user->certificates()->with(['course:id,title,slug', 'learningPath:id,title,slug'])->latest()->get(),
            'quizAttempts' => $user->quizAttempts()->with('quiz:id,title,total_marks,pass_marks')->latest()->take(10)->get(),
            'submissions' => $user->assignmentSubmissions()->with('assignment:id,title,total_marks')->latest()->take(10)->get(),
            'recommendedCourses' => $recommendedCourses,
            'learningPathEnrollments' => $learningPathEnrollments,
        ]);
    }
}
