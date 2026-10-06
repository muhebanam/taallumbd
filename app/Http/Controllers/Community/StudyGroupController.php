<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\StudyGroup;
use App\Models\StudyGroupMember;
use App\Models\StudyGroupPost;
use App\Services\AuditLoggerService;
use App\Services\CommunityModerationService;
use App\Services\ReputationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudyGroupController extends Controller
{
    public function __construct(
        protected CommunityModerationService $moderationService,
        protected ReputationService $reputationService
    ) {}

    /**
     * List all accessible study groups.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tab = $request->query('tab', 'explore'); // explore, my_groups, course_linked

        $query = StudyGroup::with(['creator:id,name', 'course:id,title'])
            ->where('is_archived', false);

        if ($tab === 'my_groups' && $user) {
            $myGroupIds = $user->studyGroupMemberships()->where('status', 'active')->pluck('study_group_id');
            $query->whereIn('id', $myGroupIds);
        } elseif ($tab === 'course_linked') {
            $query->where('type', 'course_linked');
        } else {
            // Explore shows public groups and groups user is part of
            $query->where(function ($q) use ($user) {
                $q->where('type', 'public');
                if ($user) {
                    $myGroupIds = $user->studyGroupMemberships()->where('status', 'active')->pluck('study_group_id');
                    $q->orWhereIn('id', $myGroupIds);
                }
            });
        }

        $groups = $query->latest()->paginate(12)->withQueryString();

        return Inertia::render('Community/StudyGroups/Index', [
            'groups' => $groups,
            'tab' => $tab,
        ]);
    }

    /**
     * Create group form.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');

        $courses = Course::where('status', 'published')->get(['id', 'title']);

        return Inertia::render('Community/StudyGroups/Create', [
            'courses' => $courses,
        ]);
    }

    /**
     * Store new study group.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');

        $validated = $request->validate([
            'name' => 'required|string|min:3|max:100',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:public,private,course_linked',
            'course_id' => 'nullable|required_if:type,course_linked|exists:courses,id',
            'max_members' => 'nullable|integer|min:2|max:500',
            'weekly_goal' => 'nullable|string|max:255',
            'weekly_goal_target' => 'nullable|integer|min:1|max:1000',
            'weekly_goal_end_date' => 'nullable|date',
        ]);

        $group = StudyGroup::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'course_id' => $validated['course_id'] ?? null,
            'creator_id' => $user->id,
            'max_members' => $validated['max_members'] ?? 100,
            'members_count' => 1,
            'weekly_goal' => $validated['weekly_goal'] ?? null,
            'weekly_goal_target' => $validated['weekly_goal_target'] ?? 100,
            'weekly_goal_end_date' => $validated['weekly_goal_end_date'] ?? now()->endOfWeek(),
        ]);

        // Add creator as owner member
        StudyGroupMember::create([
            'study_group_id' => $group->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        AuditLoggerService::log(
            action: 'study_group.created',
            modelType: StudyGroup::class,
            modelId: $group->id,
            payload: [
                'name' => $group->name,
                'type' => $group->type,
                'creator_id' => $user->id,
            ]
        );

        return redirect()->route('community.groups.show', $group->slug)
            ->with('success', 'মাশাআল্লাহ! আপনার স্টাডি গ্রুপ সফলভাবে তৈরি হয়েছে।');
    }

    /**
     * Show study group details and feed.
     */
    public function show(Request $request, StudyGroup $group)
    {
        $user = $request->user();

        // Enforce Group Privacy Policy
        if (! $user && $group->type !== 'public') {
            return redirect()->route('login')->with('info', 'প্রাইভেট বা কোর্স স্টাডি গ্রুপ দেখতে প্রথমে লগইন করুন।');
        }

        if ($user && ! $group->canView($user)) {
            abort(403, 'এটি একটি প্রাইভেট স্টাডি গ্রুপ। শুধুমাত্র অনুমোদিত সদস্যরা এতে প্রবেশ করতে পারবেন।');
        }

        $group->load(['creator:id,name', 'course:id,title,slug']);

        $isMember = $user ? $group->isMember($user) : false;
        $userMembership = $user ? $group->members()->where('user_id', $user->id)->first() : null;

        $posts = $group->posts()
            ->with(['user:id,name,role', 'comments.user:id,name,role'])
            ->paginate(15);

        $activeMembers = $group->activeMembers()
            ->with('user:id,name,role,reputation_points,reputation_level')
            ->take(12)
            ->get();

        return Inertia::render('Community/StudyGroups/Show', [
            'group' => $group,
            'isMember' => $isMember,
            'userMembership' => $userMembership,
            'posts' => $posts,
            'activeMembers' => $activeMembers,
            'inviteUrl' => route('community.groups.invite', $group->invite_code),
        ]);
    }

    /**
     * Join group directly (public or course-linked for enrolled students).
     */
    public function join(Request $request, StudyGroup $group)
    {
        $user = $request->user();
        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');

        if ($group->isMember($user)) {
            return back()->with('info', 'আপনি ইতিমধ্যে এই গ্রুপের সদস্য।');
        }

        if ($group->members_count >= $group->max_members) {
            return back()->with('error', 'এই গ্রুপে সর্বোচ্চ সদস্য সংখ্যা পূর্ণ হয়ে গেছে।');
        }

        if ($group->type === 'private') {
            return back()->with('error', 'প্রাইভেট গ্রুপে যুক্ত হতে ইনভাইট লিংক প্রয়োজন।');
        }

        if ($group->type === 'course_linked' && $group->course_id && ! $user->isEnrolled($group->course_id) && ! $user->isAdmin()) {
            return back()->with('error', 'এই স্টাডি গ্রুপে যুক্ত হতে সংশ্লিষ্ট কোর্সে ভর্তি হতে হবে।');
        }

        StudyGroupMember::updateOrCreate(
            ['study_group_id' => $group->id, 'user_id' => $user->id],
            ['role' => 'member', 'status' => 'active', 'joined_at' => now()]
        );

        $group->increment('members_count');

        return back()->with('success', 'মারহাবা! আপনি সফলভাবে স্টাডি গ্রুপে যুক্ত হয়েছেন।');
    }

    /**
     * Join group via unique invite code.
     */
    public function joinByInvite(Request $request, string $code)
    {
        $group = StudyGroup::where('invite_code', $code)->firstOrFail();
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('info', 'গ্রুপে যুক্ত হতে লগইন করুন।');
        }

        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');

        if ($group->isMember($user)) {
            return redirect()->route('community.groups.show', $group->slug)->with('info', 'আপনি ইতিমধ্যে এই গ্রুপের সদস্য।');
        }

        StudyGroupMember::updateOrCreate(
            ['study_group_id' => $group->id, 'user_id' => $user->id],
            ['role' => 'member', 'status' => 'active', 'joined_at' => now()]
        );

        $group->increment('members_count');

        return redirect()->route('community.groups.show', $group->slug)
            ->with('success', 'ইনভাইট কোডের মাধ্যমে আপনি সফলভাবে গ্রুপে যুক্ত হয়েছেন।');
    }

    /**
     * Post in group feed.
     */
    public function storePost(Request $request, StudyGroup $group)
    {
        $user = $request->user();
        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');
        abort_unless($group->isMember($user) || $user->isAdmin(), 403, 'পোস্ট করার জন্য প্রথমে গ্রুপে যোগ দিন।');

        $validated = $request->validate([
            'body' => 'required|string|min:5|max:5000',
        ]);

        $post = StudyGroupPost::create([
            'study_group_id' => $group->id,
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        // Auto moderation scan
        $this->moderationService->scanAndFlag($post);

        return back()->with('success', 'পোস্টটি সফলভাবে প্রকাশিত হয়েছে।');
    }

    /**
     * Comment on a group post.
     */
    public function storeComment(Request $request, StudyGroup $group, StudyGroupPost $post)
    {
        $user = $request->user();
        abort_if($user->isMutedInCommunity() || $user->is_banned, 403, 'আপনার অ্যাকাউন্ট সাময়িকভাবে স্থগিত বা মিউট করা হয়েছে।');
        abort_unless($group->isMember($user) || $user->isAdmin(), 403);

        $validated = $request->validate([
            'body' => 'required|string|min:2|max:2000',
        ]);

        $post->comments()->create([
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $post->increment('comments_count');

        return back()->with('success', 'আপনার মন্তব্য যুক্ত হয়েছে।');
    }

    /**
     * Update student weekly goal progress.
     */
    public function updateWeeklyGoalProgress(Request $request, StudyGroup $group)
    {
        $user = $request->user();
        abort_unless($group->isMember($user), 403);

        $validated = $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $membership = StudyGroupMember::where('study_group_id', $group->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $completed = $validated['progress'] >= 100;
        $wasCompleted = $membership->weekly_goal_completed;

        $membership->update([
            'weekly_goal_progress' => $validated['progress'],
            'weekly_goal_completed' => $completed,
        ]);

        // Award reputation points when goal is achieved for the first time
        if ($completed && ! $wasCompleted) {
            $this->reputationService->awardPoints(
                $user,
                ReputationService::ACTION_GOAL_COMPLETED,
                $group
            );
        }

        return back()->with('success', 'আপনার সাপ্তাহিক লক্ষ্যের অগ্রগতি সংরক্ষিত হয়েছে।');
    }
}
