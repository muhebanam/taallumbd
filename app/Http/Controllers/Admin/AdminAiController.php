<?php

namespace App\Http\Controllers\Admin;

use App\AI\Services\AiContentAssistService;
use App\AI\Services\QuotaManager;
use App\Http\Controllers\Controller;
use App\Models\AiInteraction;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAiController extends Controller
{
    public function __construct(
        protected QuotaManager $quotaManager,
        protected AiContentAssistService $contentAssist
    ) {}

    /**
     * Display AI Cost, Token Usage & Governance Dashboard.
     */
    public function index(Request $request): Response
    {
        $globalStats = $this->quotaManager->getGlobalUsageStats();
        $totalInteractions = AiInteraction::count();
        $totalTokens = AiInteraction::sum('tokens');
        $totalCost = AiInteraction::sum('cost');

        $flaggedCount = AiInteraction::where('flagged', true)->where('scholar_reviewed', false)->count();

        // Feature breakdown
        $featureStats = AiInteraction::selectRaw('feature, count(*) as count, sum(tokens) as total_tokens, sum(cost) as total_cost')
            ->groupBy('feature')
            ->get();

        // Recent interactions
        $recentInteractions = AiInteraction::with('user:id,name,email')
            ->latest()
            ->take(15)
            ->get();

        $aiEnabled = filter_var(Setting::get('ai_enabled', config('ai.enabled', true)), FILTER_VALIDATE_BOOLEAN);

        return Inertia::render('Admin/AiDashboard', [
            'aiEnabled' => $aiEnabled,
            'globalStats' => $globalStats,
            'totalInteractions' => $totalInteractions,
            'totalTokens' => (int) $totalTokens,
            'totalCost' => (float) $totalCost,
            'flaggedCount' => $flaggedCount,
            'featureStats' => $featureStats,
            'recentInteractions' => $recentInteractions,
        ]);
    }

    /**
     * Toggle AI system master switch on/off.
     */
    public function toggle(Request $request): RedirectResponse
    {
        $current = filter_var(Setting::get('ai_enabled', config('ai.enabled', true)), FILTER_VALIDATE_BOOLEAN);
        $newState = ! $current;

        Setting::set('ai_enabled', $newState ? 'true' : 'false', 'ai');

        return back()->with('success', $newState ? 'এআই ফিচারসমূহ সফলভাবে চালু করা হয়েছে।' : 'এআই ফিচারসমূহ সফলভাবে বন্ধ করা হয়েছে।');
    }

    /**
     * Scholar Review Queue for flagged AI answers.
     */
    public function reviews(Request $request): Response
    {
        $flaggedList = AiInteraction::with(['user:id,name,email', 'reviewer:id,name'])
            ->where('flagged', true)
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/AiReviews', [
            'reviews' => $flaggedList,
        ]);
    }

    /**
     * Scholar resolves and annotates a flagged AI interaction.
     */
    public function resolveReview(Request $request, AiInteraction $interaction): RedirectResponse
    {
        $validated = $request->validate([
            'scholar_notes' => 'required|string|max:1000',
        ]);

        $interaction->update([
            'scholar_reviewed' => true,
            'scholar_reviewed_by' => $request->user()->id,
            'scholar_notes' => $validated['scholar_notes'],
        ]);

        return back()->with('success', 'স্কলার পর্যবেক্ষণ সফলভাবে সংরক্ষিত হয়েছে।');
    }

    /**
     * Content Assistance / Proofreading (Reviewer / Scholar only).
     */
    public function proofread(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:20|max:5000',
            'type' => 'required|string|in:article,fatwa,lesson',
        ]);

        $result = $this->contentAssist->assistContentDraft(
            title: $validated['title'],
            draftText: $validated['content'],
            contentType: $validated['type'],
            reviewer: $request->user()
        );

        return response()->json($result);
    }
}
