<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentReport;
use App\Models\User;
use App\Services\CommunityModerationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminCommunityModerationController extends Controller
{
    public function __construct(
        protected CommunityModerationService $moderationService
    ) {}

    /**
     * Moderation reports queue.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $reports = ContentReport::with(['reporter:id,name,role', 'reviewer:id,name', 'reportable'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => ContentReport::where('status', 'pending')->count(),
            'resolved' => ContentReport::where('status', 'resolved')->count(),
            'dismissed' => ContentReport::where('status', 'dismissed')->count(),
        ];

        return Inertia::render('Admin/CommunityModeration', [
            'reports' => $reports,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
            ],
        ]);
    }

    /**
     * Resolve report.
     */
    public function resolve(Request $request, ContentReport $report)
    {
        $validated = $request->validate([
            'action' => 'required|in:dismiss,hide_content,mute_author,ban_author',
            'mute_hours' => 'nullable|integer|min:1|max:720',
        ]);

        $this->moderationService->resolveReport(
            $report,
            $request->user(),
            $validated['action'],
            $validated['mute_hours'] ?? 24
        );

        return back()->with('success', 'রিপোর্টটি সফলভাবে সমাধান করা হয়েছে।');
    }

    /**
     * Unmute a muted community member.
     */
    public function unmute(Request $request, User $user)
    {
        $user->update(['community_muted_until' => null]);

        return back()->with('success', "{$user->name} এর ওপর থেকে মিউট তুলে নেওয়া হয়েছে।");
    }

    /**
     * Unban a banned member.
     */
    public function unban(Request $request, User $user)
    {
        $user->update(['is_banned' => false]);

        return back()->with('success', "{$user->name} এর অ্যাকাউন্ট পুনরায় সক্রিয় করা হয়েছে।");
    }
}
