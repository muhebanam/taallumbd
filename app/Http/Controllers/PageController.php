<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\Publication;
use App\Models\Teacher;
use App\Models\TeacherFollower;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function about(?string $section = 'taallum')
    {
        return Inertia::render('About', ['section' => $section]);
    }

    public function teachers(Request $request)
    {
        $search = $request->input('search');
        $specialty = $request->input('specialty');
        $sort = $request->input('sort', 'popular');
        $verifiedOnly = $request->boolean('verified');

        $query = Teacher::active()
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
            }], 'rating');

        // Apply filters
        if ($search) {
            $query->search($search);
        }

        if ($specialty) {
            $query->specialty($specialty);
        }

        if ($verifiedOnly) {
            $query->verified();
        }

        // Apply sorting
        switch ($sort) {
            case 'latest':
                $query->latest();
                break;
            case 'courses':
                $query->orderByDesc('courses_count');
                break;
            case 'articles':
                $query->orderByDesc('articles_count');
                break;
            case 'fatawa':
                $query->orderByDesc('fatawa_count');
                break;
            case 'rating':
                $query->orderByDesc('average_rating');
                break;
            case 'popular':
            default:
                $query->orderByDesc('followers_count');
                break;
        }

        // Always sort verified teachers first, and then apply sort_order
        $query->orderByDesc('is_verified')->orderBy('sort_order');

        $teachers = $query->paginate(12)->withQueryString();

        // Featured verified teachers
        $featuredTeachers = Teacher::active()
            ->verified()
            ->featured()
            ->withCount(['followers', 'courses' => function ($q) {
                $q->published();
            }])
            ->withAvg(['teacherReviews as average_rating' => function ($q) {
                $q->where('status', 'approved');
            }], 'rating')
            ->take(3)
            ->get();

        // Calculate statistics
        $stats = [
            'total_teachers' => Teacher::active()->count(),
            'total_courses' => Course::published()->count(),
            'total_articles' => Article::published()->count(),
            'total_fatawa' => Fatwa::published()->count(),
            'total_publications' => Publication::published()->count(),
            'total_followers' => TeacherFollower::count(),
        ];

        return Inertia::render('About/Teachers', [
            'teachers' => $teachers,
            'featuredTeachers' => $featuredTeachers,
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'specialty' => $specialty,
                'sort' => $sort,
                'verified' => $verifiedOnly,
            ],
        ]);
    }
}
