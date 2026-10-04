<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\ForumComment;
use App\Models\ForumLike;
use App\Models\ForumPost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');
        $topic = $request->query('topic');
        $sort = $request->query('sort', 'latest');

        $query = ForumPost::published()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->when($topic && $topic !== 'all', function ($q) use ($topic) {
                $q->where('topic', $topic);
            })
            ->withCount(['comments', 'likes'])
            ->with([
                'user:id,name,role',
                'course:id,title,slug',
                'category:id,name',
            ]);

        // Sorting
        switch ($sort) {
            case 'popular':
                $query->orderByDesc('upvotes_count')->orderByDesc('comments_count');
                break;
            case 'unanswered':
                $query->has('comments', '=', 0)->latest();
                break;
            case 'solved':
                $query->where('is_solved', true)->latest();
                break;
            case 'latest':
            default:
                $query->orderByDesc('is_pinned')->latest();
                break;
        }

        $posts = $query->paginate(15)->withQueryString();

        $user = $request->user();
        $likedPostIds = $user
            ? ForumLike::where('user_id', $user->id)->whereNotNull('forum_post_id')->pluck('forum_post_id')->toArray()
            : [];

        $topics = [
            ['id' => 'all', 'label' => 'সকল আলোচনা'],
            ['id' => 'quran-hadith', 'label' => 'কুরআন ও হাদিস গবেষণা'],
            ['id' => 'fiqh-masala', 'label' => 'ফিকহ ও আহকাম জিজ্ঞাসা'],
            ['id' => 'arabic-lang', 'label' => 'আরবি ভাষা ও ব্যাকরণ'],
            ['id' => 'course-qa', 'label' => 'কোর্স সংক্রান্ত প্রশ্নোত্তর'],
            ['id' => 'general', 'label' => 'সাধারণ দ্বীনি মতবিনিময়'],
        ];

        $stats = [
            'total_discussions' => ForumPost::published()->count(),
            'solved_discussions' => ForumPost::published()->where('is_solved', true)->count(),
            'total_comments' => ForumComment::count(),
        ];

        return Inertia::render('Community/Index', [
            'posts' => $posts,
            'topics' => $topics,
            'likedPostIds' => $likedPostIds,
            'filters' => [
                'q' => $search,
                'topic' => $topic ?? 'all',
                'sort' => $sort,
            ],
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, ForumPost $post)
    {
        abort_unless($post->status === 'published' || ($request->user() && $request->user()->role === 'admin'), 404);

        $post->increment('views_count');

        $post->load([
            'user:id,name,role',
            'course:id,title,slug',
            'category:id,name',
            'comments' => function ($q) {
                $q->with('user:id,name,role')->latest();
            },
        ])->loadCount('likes');

        $user = $request->user();
        $isLiked = $user ? $post->isLikedBy($user) : false;

        // Related Discussions
        $relatedPosts = ForumPost::published()
            ->where('id', '!=', $post->id)
            ->where(function ($q) use ($post) {
                $q->where('topic', $post->topic);
                if ($post->course_id) {
                    $q->orWhere('course_id', $post->course_id);
                }
            })
            ->withCount('comments')
            ->latest()
            ->take(5)
            ->get(['id', 'title', 'topic', 'created_at']);

        return Inertia::render('Community/Show', [
            'post' => $post,
            'isLiked' => $isLiked,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    public function create(Request $request)
    {
        $courses = Course::where('status', 'published')->get(['id', 'title']);
        $categories = Category::where('status', 'active')->get(['id', 'name']);

        return Inertia::render('Community/Create', [
            'courses' => $courses,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|min:5|max:255',
            'topic' => 'required|string|in:quran-hadith,fiqh-masala,arabic-lang,course-qa,general',
            'body' => 'required|string|min:15',
            'course_id' => 'nullable|exists:courses,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['status'] = 'published';

        $post = ForumPost::create($data);

        return redirect()->route('community.show', $post->id)->with('success', 'আপনার আলোচনা সফলভাবে উন্মুক্ত ফোরামে যুক্ত হয়েছে।');
    }

    public function storeComment(Request $request, ForumPost $post)
    {
        $data = $request->validate([
            'body' => 'required|string|min:3',
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('success', 'আপনার মতামত/উত্তর যুক্ত হয়েছে।');
    }

    public function toggleLike(Request $request, ForumPost $post)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $existing = ForumLike::where('user_id', $user->id)
            ->where('forum_post_id', $post->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $post->decrement('upvotes_count');
            $status = 'unliked';
        } else {
            ForumLike::create([
                'user_id' => $user->id,
                'forum_post_id' => $post->id,
            ]);
            $post->increment('upvotes_count');
            $status = 'liked';
        }

        return back()->with('status', $status);
    }

    public function markSolved(Request $request, ForumPost $post)
    {
        $user = $request->user();
        abort_unless($user && ($user->id === $post->user_id || in_array($user->role, ['admin', 'instructor'])), 403);

        $post->update([
            'is_solved' => ! $post->is_solved,
        ]);

        $message = $post->is_solved
            ? 'আলোচনাটি সফলভাবে সমাধানকৃত হিসেবে চিহ্নিত হয়েছে।'
            : 'আলোচনাটি পুনরায় উন্মুক্ত করা হয়েছে।';

        return back()->with('success', $message);
    }
}
