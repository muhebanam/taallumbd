<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Fatwa;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class FatwaController extends Controller
{
    public function index(Request $request, ?string $slug = null)
    {
        $search = $request->query('q');
        $scholarId = $request->query('scholar_id');

        $category = $slug ? Category::ofType('fatwa')->where('slug', $slug)->firstOrFail() : null;
        $categoryIds = $category ? [$category->id, ...$category->children()->pluck('id')] : null;

        $fatawaQuery = Fatwa::published()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('question_title', 'like', "%{$search}%")
                        ->orWhere('question_body', 'like', "%{$search}%")
                        ->orWhere('answer_body', 'like', "%{$search}%");
                });
            })
            ->when($categoryIds, fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->when($scholarId, function ($q) use ($scholarId) {
                $q->where(function ($sub) use ($scholarId) {
                    $sub->where('teacher_id', $scholarId)
                        ->orWhere('assigned_scholar_id', $scholarId);
                });
            })
            ->with([
                'category:id,name,slug',
                'mufti:id,name',
                'teacher.user:id,name',
                'assignedScholar.user:id,name',
            ])
            ->latest('published_at');

        $fatawa = $fatawaQuery->paginate(12)->withQueryString();

        $categories = Cache::remember('fatwa_categories_tree', 3600, function () {
            return Category::ofType('fatwa')
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->with(['children' => fn ($query) => $query->where('status', 'active')->orderBy('sort_order')])
                ->get(['id', 'name', 'slug']);
        });

        $scholars = Cache::remember('fatwa_scholars_list', 1800, function () {
            return Teacher::with('user:id,name')
                ->where('status', 'active')
                ->get(['id', 'user_id', 'name', 'designation', 'slug']);
        });

        $stats = Cache::remember('fatawa_stats', 1800, function () {
            return [
                'total_fatawa' => Fatwa::published()->count(),
                'answered_this_month' => Fatwa::published()->where('published_at', '>=', now()->startOfMonth())->count(),
            ];
        });

        return Inertia::render('Fatawa/Index', [
            'fatawa' => $fatawa,
            'categories' => $categories,
            'scholars' => $scholars,
            'activeCategory' => $category,
            'filters' => [
                'q' => $search,
                'scholar_id' => $scholarId,
            ],
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, Fatwa $fatwa)
    {
        $user = $request->user();
        $isAuthor = $user && $fatwa->question_user_id === $user->id;
        $isStaff = $user && in_array($user->role, ['admin', 'instructor']);

        if (! $isAuthor && ! $isStaff) {
            abort_unless($fatwa->status === 'published' && ! $fatwa->is_private, 404);
        }

        // Increment views
        $fatwa->increment('views_count');

        $fatwa->load([
            'category:id,name,slug',
            'mufti:id,name',
            'teacher.user:id,name',
            'assignedScholar.user:id,name',
            'relatedCourse' => function ($q) {
                $q->select('id', 'title', 'slug', 'thumbnail', 'price', 'instructor_id')
                    ->with('instructor:id,name');
            },
        ]);

        // Related Fatawa in the same category
        $relatedFatawa = Fatwa::published()
            ->where('id', '!=', $fatwa->id)
            ->where('category_id', $fatwa->category_id)
            ->with('category:id,name')
            ->latest('published_at')
            ->take(5)
            ->get(['id', 'question_title', 'published_at', 'category_id', 'views_count']);

        return Inertia::render('Fatawa/Show', [
            'fatwa' => $fatwa,
            'relatedFatawa' => $relatedFatawa,
        ]);
    }

    public function ask()
    {
        $categories = Category::ofType('fatwa')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with(['children' => fn ($query) => $query->where('status', 'active')->orderBy('sort_order')])
            ->get(['id', 'name']);

        $scholars = Teacher::with('user:id,name')
            ->where('status', 'active')
            ->get(['id', 'user_id', 'name', 'designation', 'slug']);

        return Inertia::render('Fatawa/Ask', [
            'categories' => $categories,
            'scholars' => $scholars,
        ]);
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'questioner_name' => 'required|string|max:255',
            'questioner_email' => 'required|email|max:255',
            'questioner_phone' => 'nullable|string|max:30',
            'question_title' => 'required|string|max:255',
            'question_body' => 'required|string|min:20',
            'category_id' => 'required|exists:categories,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_private' => 'boolean',
        ]);

        $data['question_user_id'] = $request->user()?->id;
        $data['status'] = 'pending';

        Fatwa::create($data);

        return back()->with('success', 'আপনার প্রশ্নটি সফলভাবে জমা হয়েছে। উলামা ও মুফতী পরিষদ উত্তর প্রস্তুত করার পর জানানো হবে, ইনশাআল্লাহ।');
    }

    public function myQuestions(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $questions = Fatwa::where('question_user_id', $user->id)
            ->with(['category:id,name', 'teacher.user:id,name', 'assignedScholar.user:id,name'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Fatawa/MyQuestions', [
            'questions' => $questions,
        ]);
    }
}
