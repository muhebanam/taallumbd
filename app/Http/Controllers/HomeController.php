<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Teacher;
use App\Models\Category;
use App\Models\Fatwa;
use App\Models\Article;
use App\Models\User;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function __invoke()
    {
        $popularCourses = Course::published()
            ->with(['instructor:id,name', 'category:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->latest()
            ->take(6)
            ->get();

        $featuredTeachers = Teacher::where('status', 'active')
            ->where(function ($query) {
                $query->where('featured', true)
                      ->orWhere('is_verified', true);
            })
            ->withCount(['courses', 'articles', 'fatawa', 'followers'])
            ->orderByDesc('featured')
            ->take(4)
            ->get();

        // If no featured teachers yet, load active teachers
        if ($featuredTeachers->isEmpty()) {
            $featuredTeachers = Teacher::where('status', 'active')
                ->withCount(['courses', 'articles', 'fatawa', 'followers'])
                ->take(4)
                ->get();
        }

        $categories = Category::where('status', 'active')
            ->where('type', 'course')
            ->whereNull('parent_id')
            ->withCount('courses')
            ->orderBy('sort_order')
            ->take(8)
            ->get();

        $latestFatawa = Fatwa::published()
            ->with(['category:id,name,slug', 'teacher:id,name,slug,avatar,is_verified'])
            ->latest('published_at')
            ->take(4)
            ->get();

        $latestArticles = Article::published()
            ->with(['category:id,name,slug', 'author:id,name,avatar'])
            ->latest('published_at')
            ->take(3)
            ->get();

        $stats = [
            ['value' => '২,৫০০+', 'label' => 'নিবন্ধিত শিক্ষার্থী'],
            ['value' => '৯৫%', 'label' => 'সন্তুষ্ট শিক্ষার্থী'],
            ['value' => '১০০%', 'label' => 'বিশুদ্ধ কারিকুলাম'],
            ['value' => bn_number(Course::published()->count()) . ' টি', 'label' => 'সক্রিয় কোর্স'],
            ['value' => bn_number(Teacher::where('status', 'active')->count()) . ' জন', 'label' => 'যোগ্য উস্তায ও গবেষক'],
        ];

        return Inertia::render('Home', [
            'popularCourses' => $popularCourses,
            'featuredTeachers' => $featuredTeachers,
            'categories' => $categories,
            'latestFatawa' => $latestFatawa,
            'latestArticles' => $latestArticles,
            'stats' => $stats,
        ]);
    }
}
