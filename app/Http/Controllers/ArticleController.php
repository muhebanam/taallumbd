<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Services\SeoService;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function index(?string $slug = null)
    {
        $category = $slug ? Category::ofType('article')->where('slug', $slug)->firstOrFail() : null;

        return Inertia::render('Articles/Index', [
            'articles' => Article::published()->when($category, fn ($q) => $q->where('category_id', $category->id))
                ->with(['author:id,name', 'category:id,name,slug'])->latest('published_at')->paginate(12),
            'categories' => Category::ofType('article')->orderBy('sort_order')->get(['id', 'name', 'slug']),
            'activeCategory' => $category,
        ]);
    }

    public function show(Article $article)
    {
        abort_unless($article->status === 'published', 404);

        $article->load(['author:id,name', 'category:id,name,slug']);
        $seoService = app(SeoService::class);

        return Inertia::render('Articles/Show', [
            'article' => $article,
            'seo' => [
                'title' => $article->title.' — আত-তাআল্লুম ব্লগ',
                'description' => $article->excerpt ?: substr(strip_tags((string) $article->body), 0, 160),
                'canonical' => url('/articles/'.$article->slug),
                'ogImage' => $article->thumbnail ? (str_starts_with($article->thumbnail, 'http') ? $article->thumbnail : url('/storage/'.$article->thumbnail)) : url('/images/covers/cover_default.jpg'),
                'type' => 'article',
                'jsonLd' => [
                    $seoService->organization(),
                    $seoService->article($article),
                    $seoService->breadcrumbs([
                        ['name' => 'হোম', 'url' => url('/')],
                        ['name' => 'প্রবন্ধ ও ব্লগ', 'url' => url('/articles')],
                        ['name' => $article->title, 'url' => url('/articles/'.$article->slug)],
                    ]),
                ],
            ],
        ]);
    }
}
