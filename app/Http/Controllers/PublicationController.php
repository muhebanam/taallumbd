<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Publication;
use Inertia\Inertia;

class PublicationController extends Controller
{
    public function index(?string $parent = null, ?string $child = null)
    {
        $slug = $child ?? $parent;
        $category = $slug ? Category::ofType('publication')->where('slug', $slug)->firstOrFail() : null;
        $ids = $category ? [$category->id, ...Category::where('parent_id', $category->id)->pluck('id')] : null;

        return Inertia::render('Publications/Index', [
            'publications' => Publication::published()->when($ids, fn ($q) => $q->whereIn('category_id', $ids))
                ->with('category:id,name,slug')->latest('published_at')->paginate(12),
            'categories' => Category::ofType('publication')->whereNull('parent_id')->orderBy('sort_order')
                ->with('children:id,name,slug,parent_id')->get(['id', 'name', 'slug']),
            'activeCategory' => $category,
        ]);
    }
}
