<?php

namespace App\Http\Controllers\Api\V1;

use App\AI\Services\AiCourseAssistantService;
use App\AI\Services\QuotaManager;
use App\Http\Controllers\Controller;
use App\Models\AiInteraction;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiTutorApiController extends Controller
{
    public function __construct(
        protected AiCourseAssistantService $assistant,
        protected QuotaManager $quotaManager
    ) {}

    /**
     * Ask a question to the AI Course Tutor for a lesson.
     */
    public function ask(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'question' => 'required|string|min:3|max:1000',
        ]);

        $lesson = Lesson::with('course')->findOrFail($validated['lesson_id']);
        $user = $request->user();

        // Check enrollment or free preview
        if (! $lesson->is_preview && $user) {
            $isAuthorized = $user->isEnrolled($lesson->course) || $user->isAdmin() || $lesson->course->instructor_id === $user->id;
            if (! $isAuthorized) {
                return response()->json([
                    'success' => false,
                    'message' => 'এই পাঠের এআই টিউটর ব্যবহার করতে কোর্সে এনরোল করুন।',
                ], 403);
            }
        }

        $response = $this->assistant->ask(
            lesson: $lesson,
            rawQuestion: $validated['question'],
            user: $user
        );

        return response()->json([
            'success' => true,
            'data' => $response->toArray(),
            'quota_remaining' => $this->quotaManager->getUserRemainingQuota($user),
            'privacy_notice' => config('ai.privacy_notice'),
        ]);
    }

    /**
     * Get user's remaining daily AI quota.
     */
    public function quota(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'enabled' => (bool) config('ai.enabled', true),
            'user_remaining' => $this->quotaManager->getUserRemainingQuota($user),
            'daily_limit' => (int) config('ai.quotas.user_daily_limit', 25),
            'global_stats' => $this->quotaManager->getGlobalUsageStats(),
            'disclaimer' => config('ai.disclaimer'),
            'privacy_notice' => config('ai.privacy_notice'),
        ]);
    }

    /**
     * Flag an AI response for Scholar Review.
     */
    public function flag(Request $request, AiInteraction $interaction): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $interaction->update([
            'flagged' => true,
            'flag_reason' => $validated['reason'] ?? 'ভুল বা অসঙ্গতিপূর্ণ তথ্য রিপোর্ট করা হয়েছে।',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ধন্যবাদ। উত্তরটি স্কলার রিভিউ কিউতে প্রেরণ করা হয়েছে। বিজ্ঞ আলেমগণ এটি খতিয়ে দেখবেন।',
        ]);
    }

    /**
     * Submit feedback rating (helpful / unhelpful) on AI response.
     */
    public function feedback(Request $request, AiInteraction $interaction): JsonResponse
    {
        $validated = $request->validate([
            'rating' => 'required|integer|in:-1,1',
            'notes' => 'nullable|string|max:500',
        ]);

        $interaction->update([
            'feedback_rating' => $validated['rating'],
            'feedback_notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'আপনার মূল্যবান মতামতের জন্য শুকরিয়া।',
        ]);
    }
}
