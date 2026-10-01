<?php
namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
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
        return Inertia::render('Articles/Show', [
            'article' => $article->load(['author:id,name', 'category:id,name,slug']),
        ]);
    }
}
