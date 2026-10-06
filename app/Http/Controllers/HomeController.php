<?php

namespace App\Http\Controllers;

use App\Contracts\RecommendationService;
use App\Models\Article;
use App\Models\Category;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\Teacher;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __invoke(RecommendationService $recommendationService)
    {
        $user = request()->user();
        $recommendedCourses = $recommendationService->recommendForUser($user, 4);

        $popularCourses = Cache::remember('homepage_popular_courses', 1800, function () {
            return Course::published()
                ->with(['instructor:id,name', 'category:id,name,slug'])
                ->withCount('lessons')
                ->withAvg('reviews', 'rating')
                ->latest()
                ->take(6)
                ->get();
        });

        $featuredTeachers = Cache::remember('homepage_featured_teachers', 1800, function () {
            $teachers = Teacher::where('status', 'active')
                ->where(function ($query) {
                    $query->where('featured', true)
                        ->orWhere('is_verified', true);
                })
                ->withCount(['courses', 'articles', 'fatawa', 'followers'])
                ->orderByDesc('featured')
                ->take(4)
                ->get();

            if ($teachers->isEmpty()) {
                $teachers = Teacher::where('status', 'active')
                    ->withCount(['courses', 'articles', 'fatawa', 'followers'])
                    ->take(4)
                    ->get();
            }

            return $teachers;
        });

        $categories = Cache::remember('homepage_categories', 3600, function () {
            return Category::where('status', 'active')
                ->where('type', 'course')
                ->whereNull('parent_id')
                ->withCount('courses')
                ->orderBy('sort_order')
                ->take(8)
                ->get();
        });

        $latestFatawa = Cache::remember('homepage_latest_fatawa', 900, function () {
            return Fatwa::published()
                ->with(['category:id,name,slug', 'teacher:id,name,slug,avatar,is_verified'])
                ->latest('published_at')
                ->take(4)
                ->get();
        });

        $latestArticles = Cache::remember('homepage_latest_articles', 900, function () {
            return Article::published()
                ->with(['category:id,name,slug', 'author:id,name,avatar'])
                ->latest('published_at')
                ->take(3)
                ->get();
        });

        $stats = Cache::remember('homepage_stats', 1800, function () {
            return [
                ['value' => '২,৫০০+', 'label' => 'নিবন্ধিত শিক্ষার্থী'],
                ['value' => '৯৫%', 'label' => 'সন্তুষ্ট শিক্ষার্থী'],
                ['value' => '১০০%', 'label' => 'বিশুদ্ধ কারিকুলাম'],
                ['value' => bn_number(Course::published()->count()).' টি', 'label' => 'সক্রিয় কোর্স'],
                ['value' => bn_number(Teacher::where('status', 'active')->count()).' জন', 'label' => 'যোগ্য উস্তায ও গবেষক'],
            ];
        });

        return Inertia::render('Home', [
            'popularCourses' => $popularCourses,
            'recommendedCourses' => $recommendedCourses,
            'featuredTeachers' => $featuredTeachers,
            'categories' => $categories,
            'latestFatawa' => $latestFatawa,
            'latestArticles' => $latestArticles,
            'stats' => $stats,
        ]);
    }
}
