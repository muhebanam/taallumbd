<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Services\LocalizationService;
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
            'notifications' => fn () => $request->user() ? [
                'unread_count' => $request->user()->unreadNotifications()->count(),
                'latest' => $request->user()->notifications()->latest()->limit(5)->get()->map(fn ($notification) => [
                    'id' => $notification->id,
                    'title' => $notification->data['title'] ?? 'নোটিফিকেশন',
                    'message' => $notification->data['message'] ?? '',
                    'url' => $notification->data['url'] ?? null,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at?->diffForHumans(),
                ]),
            ] : ['unread_count' => 0, 'latest' => []],
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
            'locale' => fn () => app()->getLocale(),
            'dir' => fn () => app(LocalizationService::class)->getDirection(),
            'isRtl' => fn () => app(LocalizationService::class)->isRtl(),
            'currency' => fn () => session('currency', app()->getLocale() === 'bn' ? 'BDT' : 'USD'),
            'hijriDate' => fn () => app(LocalizationService::class)->getHijriDate(),
            'availableLocales' => LocalizationService::SUPPORTED_LOCALES,
            'translations' => fn () => app(LocalizationService::class)->getTranslationsDictionary(app()->getLocale()),
        ];
    }
}
