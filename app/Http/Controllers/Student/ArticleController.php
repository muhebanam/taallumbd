<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ArticleController extends Controller
{
    public function create()
    {
        return Inertia::render('Student/ArticleCreate', [
            'categories' => Category::ofType('article')->orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'excerpt' => 'required|string|max:500',
            'body' => 'required|string',
        ]);
        Article::create([
            ...$data,
            'user_id' => $request->user()->id,
            'slug' => Str::slug($data['title']).'-'.Str::random(5),
            'status' => 'pending', // admin approval required before publish
        ]);

        return redirect()->route('dashboard')->with('success', 'প্রবন্ধ জমা হয়েছে। অ্যাডমিন অনুমোদনের পর প্রকাশিত হবে।');
    }
}
