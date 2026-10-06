<?php

namespace App\AI\Services;

use App\AI\Contracts\LlmClient;
use App\Models\AiInteraction;
use App\Models\User;

class AiContentAssistService
{
    public function __construct(
        protected LlmClient $llmClient,
        protected PiiRedactor $piiRedactor,
        protected PromptGuard $promptGuard,
        protected QuotaManager $quotaManager
    ) {}

    /**
     * Proofread and edit draft text for an article or fatwa response.
     * Restricted to Reviewers, Scholars, and Instructors.
     */
    public function assistContentDraft(string $title, string $draftText, string $contentType, User $reviewer): array
    {
        if (! config('ai.enabled', true)) {
            return [
                'success' => false,
                'message' => 'AI ভাষা সম্পাদনা সেবা বর্তমানে বন্ধ রয়েছে।',
                'suggestion' => $draftText,
            ];
        }

        $cleanText = $this->piiRedactor->redact($draftText);

        $systemPrompt = "আপনি একজন অভিজ্ঞ ইসলামিক সাহিত্য সম্পাদক ও ভাষাবিদ।\n".
            "প্রদত্ত খসড়াটির বাংলা বানানরীতি, ব্যাকরণগত বিশুদ্ধতা, প্রাঞ্জলতা এবং মার্জিত ইসলামিক পরিভাষা নিশ্চিত করে একটি পরিমার্জিত সংস্করণ দিন।\n".
            "সতর্কতা: মূল বক্তব্য বা শরয়ী অর্থে কোনো পরিবর্তন আনবেন না। শুধুমাত্র ভাষা ও বাচনভঙ্গি উন্নত করুন।\n".
            "উত্তরটি নিম্নের ফরম্যাটে দিন:\n".
            "[সম্পাদিত খসড়া]\n(এখানে পরিমার্জিত টেক্সট)\n\n[পরামর্শ ও সংশোধনী]\n(এখানে প্রধান পরিবর্তনের পয়েন্ট)";

        $userPrompt = "বিষয়/শিরোনাম: {$title}\nকনটেন্ট ধরণ: {$contentType}\nখসড়া:\n{$cleanText}";

        $startTime = hrtime(true);
        $response = $this->llmClient->generateWithSystemPrompt(
            systemPrompt: $systemPrompt,
            userPrompt: $userPrompt,
            options: ['max_tokens' => 1500]
        );
        $latencyMs = (int) round((hrtime(true) - $startTime) / 1e6);

        AiInteraction::create([
            'user_id' => $reviewer->id,
            'feature' => 'content_assist',
            'prompt_hash' => hash('sha256', "content_assist:{$reviewer->id}:".now()->timestamp),
            'prompt_redacted' => mb_substr($cleanText, 0, 200),
            'response' => $response->text,
            'sources' => [
                ['title' => $title, 'reference' => "খসড়া সম্পাদনা ({$contentType})", 'url' => null],
            ],
            'tokens' => $response->tokens,
            'cost' => $response->cost,
            'latency_ms' => $latencyMs,
            'flagged' => false,
        ]);

        $this->quotaManager->recordUsage($reviewer, $response->tokens, $response->cost);

        return [
            'success' => true,
            'notice' => 'এটি রিভিউয়ারদের সহায়তার জন্য এআই প্রস্তুতকৃত পরামর্শ। চূড়ান্ত সিদ্ধান্ত পর্যালোচকের হাতে।',
            'edited_suggestion' => $response->text,
        ];
    }
}
