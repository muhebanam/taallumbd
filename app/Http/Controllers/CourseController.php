<?php

namespace App\Http\Controllers;

use App\Contracts\RecommendationService;
use App\Models\Category;
use App\Models\Course;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index()
    {
        $categories = Cache::remember('course_categories_active', 3600, function () {
            return Category::ofType('course')->orderBy('sort_order')->get(['id', 'name', 'slug'])->values()->toArray();
        });

        return Inertia::render('Courses/Index', [
            'courses' => Course::publiclyVisible()->with(['instructor:id,name', 'category:id,name,slug'])
                ->withCount('lessons')->withAvg('reviews', 'rating')->latest()->paginate(12),
            'categories' => $categories,
            'activeCategory' => null,
        ]);
    }

    public function category(string $slug)
    {
        $category = Category::ofType('course')->where('slug', $slug)->firstOrFail();

        $categories = Cache::remember('course_categories_active', 3600, function () {
            return Category::ofType('course')->orderBy('sort_order')->get(['id', 'name', 'slug'])->values()->toArray();
        });

        return Inertia::render('Courses/Index', [
            'courses' => Course::publiclyVisible()->where('category_id', $category->id)
                ->with(['instructor:id,name', 'category:id,name,slug'])
                ->withCount('lessons')->withAvg('reviews', 'rating')->latest()->paginate(12),
            'categories' => $categories,
            'activeCategory' => $category,
        ]);
    }

    public function show(Request $request, Course $course, RecommendationService $recommendationService)
    {
        abort_unless(in_array($course->status, ['published', 'coming_soon']), 404);
        $user = $request->user();

        $course->load([
            'instructor:id,name,avatar',
            'certifiedByScholar:id,name,designation,avatar',
            'category:id,name,slug',
            'sections.curriculumItems.itemable',
        ])->loadCount('lessons');

        $seoService = app(SeoService::class);
        $learnersAlsoEnrolled = $recommendationService->recommendLearnersAlsoEnrolled($course, $user, 4);
        $similarCourses = $recommendationService->recommendSimilarCourses($course, $user, 4);

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'isEnrolled' => $user ? $user->isEnrolled($course) : false,
            'learnersAlsoEnrolled' => $learnersAlsoEnrolled,
            'similarCourses' => $similarCourses,
            'seo' => [
                'title' => $course->title.' — আত-তাআল্লুম',
                'description' => $course->short_description ?: substr(strip_tags((string) $course->description), 0, 160),
                'canonical' => url('/courses/'.$course->slug),
                'ogImage' => $course->thumbnail ? (str_starts_with($course->thumbnail, 'http') ? $course->thumbnail : url('/storage/'.$course->thumbnail)) : url('/images/logo.png'),
                'jsonLd' => [
                    $seoService->organization(),
                    $seoService->course($course),
                    $seoService->breadcrumbs([
                        ['name' => 'হোম', 'url' => url('/')],
                        ['name' => 'কোর্সসমূহ', 'url' => url('/courses')],
                        ['name' => $course->title, 'url' => url('/courses/'.$course->slug)],
                    ]),
                ],
            ],
        ]);
    }
}
