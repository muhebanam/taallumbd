<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\ForumComment;
use App\Models\ForumLike;
use App\Models\ForumPost;
use App\Models\User;
use App\Services\CommunityModerationService;
use App\Services\NotificationDispatcher;
use App\Services\ReputationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForumController extends Controller
{
    public function __construct(
        protected NotificationDispatcher $dispatcher,
        protected ReputationService $reputationService,
        protected CommunityModerationService $moderationService
    ) {}

    public function index(Request $request)
    {
        $search = $request->query('q');
        $topic = $request->query('topic');
        $tag = $request->query('tag');
        $sort = $request->query('sort', 'latest');
        $period = $request->query('period', 'all');

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
            ->when($tag, function ($q) use ($tag) {
                $q->whereJsonContains('tags', $tag);
            })
            ->withCount(['comments', 'likes'])
            ->with([
                'user:id,name,role,reputation_points,reputation_level',
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
            ? ForumLike::where('user_id', $user->id)->whereNotNull('forum_post_id')->where('vote_type', 'upvote')->pluck('forum_post_id')->toArray()
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

        $leaderboard = $this->reputationService->getLeaderboard($period, 5);

        return Inertia::render('Community/Index', [
            'posts' => $posts,
            'topics' => $topics,
            'likedPostIds' => $likedPostIds,
            'leaderboard' => $leaderboard,
            'filters' => [
                'q' => $search,
                'topic' => $topic ?? 'all',
                'tag' => $tag,
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
            'user:id,name,role,avatar,reputation_points,reputation_level',
            'course:id,title,slug',
            'category:id,name',
            'comments' => function ($q) {
                $q->whereNull('parent_id')
                    ->with([
                        'user:id,name,role,avatar,reputation_points,reputation_level',
                        'verifiedByScholar:id,name,role',
                        'replies.user:id,name,role,avatar',
                    ])
                    ->latest();
            },
        ])->loadCount('likes');

        $user = $request->user();
        $isLiked = $user ? $post->isLikedBy($user) : false;
        $userVote = $user ? $post->userVote($user) : null;

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
            'userVote' => $userVote,
            'relatedPosts' => $relatedPosts,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');

        $courses = Course::where('status', 'published')->get(['id', 'title']);
        $categories = Category::where('status', 'active')->get(['id', 'name']);

        return Inertia::render('Community/Create', [
            'courses' => $courses,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');

        $data = $request->validate([
            'title' => 'required|string|min:5|max:255',
            'topic' => 'required|string|in:quran-hadith,fiqh-masala,arabic-lang,course-qa,general',
            'tags' => 'nullable|array|max:5',
            'tags.*' => 'string|max:30',
            'body' => 'required|string|min:15',
            'course_id' => 'nullable|exists:courses,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $data['user_id'] = $user->id;
        $data['status'] = 'published';

        $post = ForumPost::create($data);

        // Auto moderation scan
        $this->moderationService->scanAndFlag($post);

        // Notify @mentions
        $this->parseAndNotifyMentions($post->body, $post->title, route('community.show', $post), $user);

        // Award reputation for first discussion
        $this->reputationService->awardPoints($user, ReputationService::ACTION_FIRST_DISCUSSION, $post);

        return redirect()->route('community.show', $post->id)->with('success', 'আপনার আলোচনা সফলভাবে উন্মুক্ত ফোরামে যুক্ত হয়েছে।');
    }

    public function storeComment(Request $request, ForumPost $post)
    {
        $user = $request->user();
        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');

        $data = $request->validate([
            'body' => 'required|string|min:3',
            'parent_id' => 'nullable|exists:forum_comments,id',
        ]);

        $comment = $post->comments()->create([
            'user_id' => $user->id,
            'body' => $data['body'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        // Auto moderation scan
        $this->moderationService->scanAndFlag($comment);

        // Notify post author
        $this->dispatcher->forumComment($post->fresh(['user']), $user);

        // Notify @mentions
        $this->parseAndNotifyMentions($comment->body, $post->title, route('community.show', $post), $user);

        return back()->with('success', 'আপনার মতামত/উত্তর যুক্ত হয়েছে।');
    }

    /**
     * Upvote/Downvote a forum post.
     */
    public function votePost(Request $request, ForumPost $post)
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if($user->is_banned, 403);

        $validated = $request->validate([
            'vote_type' => 'required|in:upvote,downvote',
        ]);
        $type = $validated['vote_type'];

        $existing = ForumLike::where('user_id', $user->id)
            ->where('forum_post_id', $post->id)
            ->first();

        if ($existing) {
            if ($existing->vote_type === $type) {
                // Remove vote
                $existing->delete();
                if ($type === 'upvote') {
                    $post->decrement('upvotes_count');
                    $this->reputationService->deductPoints($post->user, ReputationService::ACTION_UPVOTE_RECEIVED, $post, $user);
                } else {
                    $post->decrement('downvotes_count');
                }
                $status = 'unvoted';
            } else {
                // Change vote type
                $oldType = $existing->vote_type;
                $existing->update(['vote_type' => $type]);

                if ($type === 'upvote') {
                    $post->increment('upvotes_count');
                    $post->decrement('downvotes_count');
                    $this->reputationService->awardPoints($post->user, ReputationService::ACTION_UPVOTE_RECEIVED, $post, $user);
                } else {
                    $post->decrement('upvotes_count');
                    $post->increment('downvotes_count');
                    $this->reputationService->deductPoints($post->user, ReputationService::ACTION_UPVOTE_RECEIVED, $post, $user);
                }
                $status = $type;
            }
        } else {
            // New vote
            ForumLike::create([
                'user_id' => $user->id,
                'forum_post_id' => $post->id,
                'vote_type' => $type,
            ]);

            if ($type === 'upvote') {
                $post->increment('upvotes_count');
                $this->reputationService->awardPoints($post->user, ReputationService::ACTION_UPVOTE_RECEIVED, $post, $user);
            } else {
                $post->increment('downvotes_count');
            }
            $status = $type;
        }

        return back()->with('status', $status);
    }

    /**
     * Backward-compatible toggleLike route.
     */
    public function toggleLike(Request $request, ForumPost $post)
    {
        $request->merge(['vote_type' => 'upvote']);

        return $this->votePost($request, $post);
    }

    /**
     * Upvote/Downvote a comment.
     */
    public function voteComment(Request $request, ForumComment $comment)
    {
        $user = $request->user();
        abort_unless($user, 401);
        abort_if($user->is_banned, 403);

        $validated = $request->validate([
            'vote_type' => 'required|in:upvote,downvote',
        ]);
        $type = $validated['vote_type'];

        $existing = ForumLike::where('user_id', $user->id)
            ->where('forum_comment_id', $comment->id)
            ->first();

        if ($existing) {
            if ($existing->vote_type === $type) {
                $existing->delete();
                if ($type === 'upvote') {
                    $comment->decrement('upvotes_count');
                    $this->reputationService->deductPoints($comment->user, ReputationService::ACTION_COMMENT_UPVOTED, $comment, $user);
                } else {
                    $comment->decrement('downvotes_count');
                }
            } else {
                $existing->update(['vote_type' => $type]);
                if ($type === 'upvote') {
                    $comment->increment('upvotes_count');
                    $comment->decrement('downvotes_count');
                    $this->reputationService->awardPoints($comment->user, ReputationService::ACTION_COMMENT_UPVOTED, $comment, $user);
                } else {
                    $comment->decrement('upvotes_count');
                    $comment->increment('downvotes_count');
                    $this->reputationService->deductPoints($comment->user, ReputationService::ACTION_COMMENT_UPVOTED, $comment, $user);
                }
            }
        } else {
            ForumLike::create([
                'user_id' => $user->id,
                'forum_comment_id' => $comment->id,
                'vote_type' => $type,
            ]);

            if ($type === 'upvote') {
                $comment->increment('upvotes_count');
                $this->reputationService->awardPoints($comment->user, ReputationService::ACTION_COMMENT_UPVOTED, $comment, $user);
            } else {
                $comment->increment('downvotes_count');
            }
        }

        return back();
    }

    /**
     * Mark discussion as solved.
     */
    public function markSolved(Request $request, ForumPost $post)
    {
        $user = $request->user();
        abort_unless($user && ($user->id === $post->user_id || in_array($user->role, ['admin', 'instructor'])), 403);

        $post->update([
            'is_solved' => ! $post->is_solved,
        ]);

        if ($post->is_solved) {
            $this->dispatcher->forumSolved($post->fresh(['user']));
            $this->reputationService->awardPoints($post->user, ReputationService::ACTION_ACCEPTED_ANSWER, $post);
        } else {
            $this->reputationService->deductPoints($post->user, ReputationService::ACTION_ACCEPTED_ANSWER, $post);
        }

        $message = $post->is_solved
            ? 'আলোচনাটি সফলভাবে সমাধানকৃত হিসেবে চিহ্নিত হয়েছে।'
            : 'আলোচনাটি পুনরায় উন্মুক্ত করা হয়েছে।';

        return back()->with('success', $message);
    }

    /**
     * Scholar-Verified Answer Seal.
     * Only scholars (instructors) and admins can seal answers.
     */
    public function verifyComment(Request $request, ForumComment $comment)
    {
        $user = $request->user();
        abort_unless($user && in_array($user->role, ['admin', 'instructor']), 403, 'শুধুমাত্র বিজ্ঞ আলেম বা অ্যাডমিন কোনো উত্তরকে স্কলার-যাচাইকৃত হিসেবে সিলমোহর দিতে পারেন।');

        $isVerified = ! $comment->is_scholar_verified;

        $comment->update([
            'is_scholar_verified' => $isVerified,
            'verified_by_scholar_id' => $isVerified ? $user->id : null,
            'verified_at' => $isVerified ? now() : null,
        ]);

        if ($isVerified) {
            $this->reputationService->awardPoints(
                $comment->user,
                ReputationService::ACTION_ACCEPTED_ANSWER,
                $comment,
                $user
            );
        } else {
            $this->reputationService->deductPoints(
                $comment->user,
                ReputationService::ACTION_ACCEPTED_ANSWER,
                $comment,
                $user
            );
        }

        $msg = $isVerified
            ? 'মাশাআল্লাহ! উত্তরটি স্কলার-যাচাইকৃত হিসেবে সিলমোহরযুক্ত হলো।'
            : 'যাচাইকৃত সিলমোহর প্রত্যাহার করা হয়েছে।';

        return back()->with('success', $msg);
    }

    /**
     * User submits report on post or comment.
     */
    public function report(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'type' => 'required|in:post,comment,group_post',
            'id' => 'required|integer',
            'reason' => 'required|in:inappropriate,false_information,harassment,spam,heresy_or_misguidance,other',
            'details' => 'nullable|string|max:500',
        ]);

        $model = match ($validated['type']) {
            'post' => ForumPost::findOrFail($validated['id']),
            'comment' => ForumComment::findOrFail($validated['id']),
            default => abort(400),
        };

        $this->moderationService->report(
            $user,
            $model,
            $validated['reason'],
            $validated['details'] ?? null
        );

        return back()->with('success', 'আপনার রিপোর্টটি স্কলার মডারেশন কিউতে জমা হয়েছে। যাছাই করে যথাযথ ব্যবস্থা নেওয়া হবে।');
    }

    /**
     * Helper to parse and notify @mentions.
     */
    protected function parseAndNotifyMentions(string $body, string $title, string $url, User $sender): void
    {
        preg_match_all('/@([a-zA-Z0-9_\x{0980}-\x{09FF}]+)/u', $body, $matches);
        if (! empty($matches[1])) {
            $names = array_unique($matches[1]);
            $users = User::whereIn('name', $names)->take(5)->get();
            foreach ($users as $mentioned) {
                $this->dispatcher->userMention($mentioned, $sender, $title, $url);
            }
        }
    }
}
