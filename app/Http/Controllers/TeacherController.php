<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\Teacher;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function show(string $slug)
    {
        $teacher = Teacher::active()
            ->where('slug', $slug)
            ->withCount(['followers', 'courses' => function ($q) {
                $q->published();
            }, 'articles' => function ($q) {
                $q->published();
            }, 'fatawa' => function ($q) {
                $q->published();
            }, 'publications' => function ($q) {
                $q->published();
            }])
            ->withAvg(['teacherReviews as average_rating' => function ($q) {
                $q->where('status', 'approved');
            }], 'rating')
            ->firstOrFail();

        // Load relations
        $courses = $teacher->courses()
            ->published()
            ->withCount('lessons')
            ->with('category')
            ->get();

        $articles = $teacher->articles()
            ->published()
            ->with('category')
            ->get();

        $fatawa = $teacher->fatawa()
            ->published()
            ->with('category')
            ->get();

        $publications = $teacher->publications()
            ->published()
            ->with('category')
            ->get();

        $reviews = Review::where(function ($query) use ($teacher, $courses) {
            $query->where('teacher_id', $teacher->id)
                ->orWhereIn('course_id', $courses->pluck('id'));
        })
            ->where('status', 'approved')
            ->with(['user', 'course'])
            ->latest()
            ->get();

        $questions = $teacher->teacherQuestions()
            ->where('status', 'published')
            ->where('is_private', false)
            ->with('user')
            ->latest()
            ->get();

        $fatwaCategories = Category::ofType('fatwa')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with(['children' => fn ($query) => $query->where('status', 'active')->orderBy('sort_order')])
            ->get(['id', 'name', 'slug']);

        // Recalculate average rating and reviews count on the fly
        $avgRating = $reviews->avg('rating');
        $teacher->average_rating = $avgRating ? round((float) $avgRating, 1) : 0.0;
        $teacher->reviews_count = $reviews->count();

        // Calculate unique enrolled students count for this teacher's courses
        $courseIds = $courses->pluck('id');
        $studentsCount = Enrollment::whereIn('course_id', $courseIds)
            ->whereIn('status', ['active', 'completed'])
            ->distinct('user_id')
            ->count('user_id');

        // Check if logged-in user follows this teacher
        $isFollowing = false;
        if (auth()->check()) {
            $isFollowing = auth()->user()->followedTeachers()->where('teacher_id', $teacher->id)->exists();
        }

        return Inertia::render('Teachers/Show', [
            'teacher' => $teacher,
            'courses' => $courses,
            'articles' => $articles,
            'fatawa' => $fatawa,
            'publications' => $publications,
            'reviews' => $reviews,
            'questions' => $questions,
            'fatwaCategories' => $fatwaCategories,
            'studentsCount' => $studentsCount,
            'isFollowing' => $isFollowing,
        ]);
    }
}
