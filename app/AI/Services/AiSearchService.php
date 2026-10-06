<?php

namespace App\AI\Services;

use App\AI\Contracts\LlmClient;
use App\AI\DTOs\LlmResponse;
use App\Models\AiInteraction;
use App\Models\User;

class AiSearchService
{
    public function __construct(
        protected LlmClient $llmClient,
        protected PiiRedactor $piiRedactor,
        protected PromptGuard $promptGuard,
        protected QuotaManager $quotaManager
    ) {}

    /**
     * Synthesize search results into an AI Educational Summary.
     */
    public function summarizeSearch(string $query, array $searchResults, ?User $user = null): ?LlmResponse
    {
        if (! config('ai.enabled', true) || trim($query) === '') {
            return null;
        }

        if ($this->promptGuard->isPromptInjection($query)) {
            return null;
        }

        if ($this->promptGuard->isFatwaRequest($query)) {
            return LlmResponse::fatwaRedirect();
        }

        if (! $this->quotaManager->canUserRequest($user) || ! $this->quotaManager->canSystemProcessRequest()) {
            return null;
        }

        $cleanQuery = $this->piiRedactor->redact($query);
        $promptHash = hash('sha256', "search_summary:{$cleanQuery}");

        $cached = $this->quotaManager->getCachedResponse($promptHash);
        if ($cached) {
            return $cached;
        }

        // Build brief context from top items across search categories
        $context = '';
        $sources = [];

        $items = $searchResults['items'] ?? [];
        foreach (array_slice($items, 0, 5) as $item) {
            $context .= "- [{$item['type_label']}] {$item['title']}: ".($item['subtitle'] ?? $item['description'] ?? '')."\n";
            $sources[] = [
                'title' => $item['title'],
                'reference' => $item['type_label'].($item['subtitle'] ? ' - '.$item['subtitle'] : ''),
                'url' => $item['url'] ?? null,
            ];
        }

        if (empty($context)) {
            return null;
        }

        $systemPrompt = $this->promptGuard->getIslamicSystemGuidelines()."\n\n".
            "ব্যবহারকারী আত-তাআল্লুম প্ল্যাটফর্মে '{$cleanQuery}' লিখে অনুসন্ধান করেছেন।\n".
            "প্ল্যাটফর্মে প্রাপ্ত প্রাসঙ্গিক ফলাফল:\n{$context}\n".
            'এই ফলাফলসমূহের উপর ভিত্তি করে ব্যবহারকারীর জন্য ৩-৪ বাক্যের একটি প্রাঞ্জল ইসলামিক সারসংক্ষেপ তৈরি করুন। কোনো মনগড়া রায় দেবেন না।';

        $startTime = hrtime(true);
        $response = $this->llmClient->generateWithSystemPrompt(
            systemPrompt: $systemPrompt,
            userPrompt: "সারসংক্ষেপ তৈরি করুন: {$cleanQuery}",
            options: ['sources' => $sources, 'max_tokens' => 300]
        );
        $latencyMs = (int) round((hrtime(true) - $startTime) / 1e6);

        if (empty($response->text)) {
            return null;
        }

        AiInteraction::create([
            'user_id' => $user?->id,
            'feature' => 'search_summary',
            'prompt_hash' => $promptHash,
            'prompt_redacted' => $cleanQuery,
            'response' => $response->text,
            'sources' => $sources,
            'tokens' => $response->tokens,
            'cost' => $response->cost,
            'latency_ms' => $latencyMs,
            'flagged' => false,
        ]);

        $this->quotaManager->recordUsage($user, $response->tokens, $response->cost);
        $this->quotaManager->cacheResponse($promptHash, $response);

        return $response;
    }
}
