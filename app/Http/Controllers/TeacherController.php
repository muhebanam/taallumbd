<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\Teacher;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function show(string $slug)
    {
        $data = Cache::remember("scholar_show_{$slug}", 3600, function () use ($slug) {
            $teacher = Teacher::active()
                ->where('slug', $slug)
                ->with([
                    'courses' => fn ($q) => $q->published()->withCount('lessons')->with('category:id,name,slug'),
                    'articles' => fn ($q) => $q->published()->with('category:id,name,slug'),
                    'fatawa' => fn ($q) => $q->published()->with('category:id,name,slug'),
                    'publications' => fn ($q) => $q->published()->with('category:id,name,slug'),
                ])
                ->withCount(['followers'])
                ->withAvg(['teacherReviews as average_rating' => function ($q) {
                    $q->where('status', 'approved');
                }], 'rating')
                ->firstOrFail();

            $courses = $teacher->courses;
            $articles = $teacher->articles;
            $fatawa = $teacher->fatawa;
            $publications = $teacher->publications;

            $teacher->courses_count = $courses->count();
            $teacher->articles_count = $articles->count();
            $teacher->fatawa_count = $fatawa->count();
            $teacher->publications_count = $publications->count();

            $courseIds = $courses->pluck('id');

            $reviews = Review::where(function ($query) use ($teacher, $courseIds) {
                $query->where('teacher_id', $teacher->id);
                if ($courseIds->isNotEmpty()) {
                    $query->orWhereIn('course_id', $courseIds);
                }
            })
                ->where('status', 'approved')
                ->with(['user:id,name,avatar', 'course:id,title,slug'])
                ->latest()
                ->take(10)
                ->get();

            $questions = $teacher->teacherQuestions()
                ->where('status', 'published')
                ->where('is_private', false)
                ->with('user:id,name,avatar')
                ->latest()
                ->take(10)
                ->get();

            $fatwaCategories = Category::ofType('fatwa')
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->with(['children' => fn ($query) => $query->where('status', 'active')->orderBy('sort_order')])
                ->get(['id', 'name', 'slug']);

            // Recalculate average rating and reviews count
            $avgRating = $reviews->avg('rating');
            $teacher->average_rating = $avgRating ? round((float) $avgRating, 1) : 0.0;
            $teacher->reviews_count = $reviews->count();

            // Calculate unique enrolled students count for this teacher's courses
            $studentsCount = 0;
            if ($courseIds->isNotEmpty()) {
                $studentsCount = Enrollment::whereIn('course_id', $courseIds)
                    ->whereIn('status', ['active', 'completed'])
                    ->distinct('user_id')
                    ->count('user_id');
            }

            return [
                'teacher' => $teacher,
                'courses' => $courses,
                'articles' => $articles,
                'fatawa' => $fatawa,
                'publications' => $publications,
                'reviews' => $reviews,
                'questions' => $questions,
                'fatwaCategories' => $fatwaCategories,
                'studentsCount' => $studentsCount,
            ];
        });

        // Check if logged-in user follows this teacher
        $isFollowing = false;
        if (auth()->check()) {
            $isFollowing = auth()->user()->followedTeachers()->where('teacher_id', $data['teacher']->id)->exists();
        }

        return Inertia::render('Teachers/Show', array_merge($data, [
            'isFollowing' => $isFollowing,
        ]));
    }
}
