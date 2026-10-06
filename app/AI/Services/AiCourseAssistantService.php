<?php

namespace App\AI\Services;

use App\AI\Contracts\LlmClient;
use App\AI\DTOs\LlmResponse;
use App\Models\AiInteraction;
use App\Models\Lesson;
use App\Models\User;

class AiCourseAssistantService
{
    public function __construct(
        protected LlmClient $llmClient,
        protected RagService $ragService,
        protected PiiRedactor $piiRedactor,
        protected PromptGuard $promptGuard,
        protected QuotaManager $quotaManager
    ) {}

    /**
     * Ask a question to the AI Course Tutor about a specific lesson.
     */
    public function ask(Lesson $lesson, string $rawQuestion, ?User $user = null): LlmResponse
    {
        // 1. Check AI enabled
        if (! config('ai.enabled', true)) {
            return new LlmResponse(
                text: 'AI টিউটর সেবা বর্তমানে সাময়িকভাবে নিষ্ক্রিয় রয়েছে। অনুগ্রহ করে পরে চেষ্টা করুন।',
                sources: [],
            );
        }

        // 2. Check Prompt Injection
        if ($this->promptGuard->isPromptInjection($rawQuestion)) {
            return new LlmResponse(
                text: 'অনুরোধটি প্রক্রিয়াকরণ করা সম্ভব নয়। অনুগ্রহ করে শুধুমাত্র কোর্স সংক্রান্ত প্রশ্ন করুন।',
                sources: [],
            );
        }

        // 3. Check Fatwa / Fiqhi Ruling Request -> Redirect to Mufti Board
        if ($this->promptGuard->isFatwaRequest($rawQuestion)) {
            $interaction = $this->logInteraction(
                user: $user,
                feature: 'course_tutor',
                promptRedacted: $rawQuestion,
                response: LlmResponse::fatwaRedirect()->text,
                sources: LlmResponse::fatwaRedirect()->sources,
                tokens: 0,
                cost: 0.0,
                latencyMs: 5
            );

            $response = LlmResponse::fatwaRedirect();
            $response->sources[0]['interaction_id'] = $interaction->id;

            return $response;
        }

        // 4. Check User & Global Quotas
        if (! $this->quotaManager->canUserRequest($user)) {
            return new LlmResponse(
                text: 'আপনার আজকের দৈনিক AI কোটা শেষ হয়েছে। আগামীকাল আবার চেষ্টা করুন অথবা ফোরামে প্রশ্ন করুন।',
                sources: [],
            );
        }

        if (! $this->quotaManager->canSystemProcessRequest()) {
            return new LlmResponse(
                text: 'সার্ভারে অতিরিক্ত চাপের কারণে দৈনিক সীমা অতিক্রম করেছে। অনুগ্রহ করে কিছুক্ষণ পর চেষ্টা করুন।',
                sources: [],
            );
        }

        // 5. Redact PII (Name, Phone, Email)
        $cleanPrompt = $this->piiRedactor->redact($rawQuestion);

        // 6. Check Cache
        $promptHash = hash('sha256', "course_tutor:{$lesson->id}:{$cleanPrompt}");
        $cached = $this->quotaManager->getCachedResponse($promptHash);
        if ($cached) {
            return $cached;
        }

        // 7. Context assembly from lesson + RAG
        $courseTitle = $lesson->course?->title ?? 'কোর্স';
        $lessonContent = strip_tags($lesson->content ?? $lesson->title);
        $lectureSheet = strip_tags($lesson->lecture_sheet ?? '');

        // Search related Islamic citations from Quran/Hadith/Fatwa via RAG
        $ragCitations = $this->ragService->search($cleanPrompt, limit: 3);

        $ragContextText = '';
        $sources = [
            [
                'title' => "পাঠ: {$lesson->title}",
                'reference' => "{$courseTitle} - পাঠ নং {$lesson->id}",
                'url' => route('courses.show', $lesson->course?->slug ?? $lesson->course_id),
            ],
        ];

        foreach ($ragCitations as $citation) {
            $ragContextText .= "উৎস: {$citation['reference']}\nতথ্য: {$citation['content']}\n\n";
            $sources[] = [
                'title' => $citation['title'],
                'reference' => $citation['reference'],
                'url' => $citation['url'],
            ];
        }

        // 8. Build System Prompt & Instructions
        $systemPrompt = $this->promptGuard->getIslamicSystemGuidelines()."\n\n".
            "বর্তমান পাঠের প্রেক্ষাপট:\n".
            "- কোর্স: {$courseTitle}\n".
            "- পাঠের শিরোনাম: {$lesson->title}\n".
            "- মূল বিষয়বস্তু:\n{$lessonContent}\n".
            ($lectureSheet ? "- লেকচার শিট সারাংশ:\n{$lectureSheet}\n" : '').
            ($ragContextText ? "- সম্পর্কিত ইসলামিক প্রামাণ্য উদ্ধৃতি:\n{$ragContextText}" : '')."\n".
            'শিক্ষার্থীর প্রশ্নের উত্তর দিন। শুধুমাত্র উপরের পাঠ্য এবং প্রামাণ্য ইসলামিক উৎস ব্যবহার করুন। ফতোয়া দেওয়া কঠোরভাবে নিষেধ।';

        // 9. Call LLM
        $startTime = hrtime(true);
        $llmResponse = $this->llmClient->generateWithSystemPrompt(
            systemPrompt: $systemPrompt,
            userPrompt: $cleanPrompt,
            options: ['sources' => $sources]
        );
        $latencyMs = (int) round((hrtime(true) - $startTime) / 1e6);

        // 10. Record interaction and usage
        $interaction = $this->logInteraction(
            user: $user,
            feature: 'course_tutor',
            promptRedacted: $cleanPrompt,
            response: $llmResponse->text,
            sources: $sources,
            tokens: $llmResponse->tokens,
            cost: $llmResponse->cost,
            latencyMs: $latencyMs
        );

        $this->quotaManager->recordUsage($user, $llmResponse->tokens, $llmResponse->cost);

        // Attach interaction_id to sources metadata for frontend feedback/report button
        $sourcesWithId = array_map(function ($s) use ($interaction) {
            $s['interaction_id'] = $interaction->id;

            return $s;
        }, $sources);

        $llmResponse->sources = $sourcesWithId;

        // 11. Cache response
        $this->quotaManager->cacheResponse($promptHash, $llmResponse);

        return $llmResponse;
    }

    protected function logInteraction(
        ?User $user,
        string $feature,
        string $promptRedacted,
        string $response,
        array $sources,
        int $tokens,
        float $cost,
        int $latencyMs
    ): AiInteraction {
        return AiInteraction::create([
            'user_id' => $user?->id,
            'feature' => $feature,
            'prompt_hash' => hash('sha256', "{$feature}:{$promptRedacted}"),
            'prompt_redacted' => $promptRedacted,
            'response' => $response,
            'sources' => $sources,
            'tokens' => $tokens,
            'cost' => $cost,
            'latency_ms' => $latencyMs,
            'flagged' => false,
        ]);
    }
}
