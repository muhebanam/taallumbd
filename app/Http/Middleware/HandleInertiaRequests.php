<?php
namespace App\Http\Middleware;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
                    ? $request->user()->only('id', 'name', 'email', 'role', 'avatar')
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // Nav categories cached as arrays: mega menu (course), dropdowns (article/fatwa incl. nested)
            'navCategories' => fn () => cache()->remember('nav_categories_v2', 3600, fn () => [
                'course' => Category::ofType('course')->orderBy('sort_order')->get(['id', 'name', 'slug'])->toArray(),
                'article' => Category::ofType('article')->orderBy('sort_order')->get(['id', 'name', 'slug'])->toArray(),
                'fatwa' => Category::ofType('fatwa')->whereNull('parent_id')->orderBy('sort_order')
                    ->with('children:id,name,slug,parent_id')->get(['id', 'name', 'slug'])->toArray(),
            ]),
        ];
    }
}
