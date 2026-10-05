<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
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

    public function show(Request $request, Course $course)
    {
        abort_unless(in_array($course->status, ['published', 'coming_soon']), 404);
        $user = $request->user();

        return Inertia::render('Courses/Show', [
            'course' => $course->load([
                'instructor:id,name,avatar',
                'category:id,name,slug',
                'sections.curriculumItems.itemable',
            ])->loadCount('lessons'),
            'isEnrolled' => $user ? $user->isEnrolled($course) : false,
        ]);
    }
}
