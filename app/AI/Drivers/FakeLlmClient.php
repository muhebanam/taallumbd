<?php

namespace App\AI\Drivers;

use App\AI\Contracts\LlmClient;
use App\AI\DTOs\LlmResponse;

class FakeLlmClient implements LlmClient
{
    protected array $calls = [];

    protected array $queuedResponses = [];

    protected ?string $defaultResponse = null;

    protected bool $shouldThrowRateLimit = false;

    public function __construct(?string $defaultResponse = 'এটি একটি পরীক্ষামূলক এআই উত্তর।')
    {
        $this->defaultResponse = $defaultResponse;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function queueResponse(string $text, array $sources = [], int $tokens = 50): self
    {
        $this->queuedResponses[] = new LlmResponse(
            text: $text,
            sources: $sources,
            tokens: $tokens,
            cost: 0.000005,
            latencyMs: 12,
        );

        return $this;
    }

    public function simulateRateLimit(bool $state = true): self
    {
        $this->shouldThrowRateLimit = $state;

        return $this;
    }

    public function generateText(string $prompt, array $options = []): LlmResponse
    {
        return $this->generateWithSystemPrompt('', $prompt, $options);
    }

    public function generateWithSystemPrompt(string $systemPrompt, string $userPrompt, array $options = []): LlmResponse
    {
        $this->calls[] = [
            'system_prompt' => $systemPrompt,
            'user_prompt' => $userPrompt,
            'options' => $options,
            'timestamp' => microtime(true),
        ];

        if ($this->shouldThrowRateLimit) {
            return new LlmResponse(
                text: 'সার্ভারে অতিরিক্ত চাপের কারণে অনুরোধটি সম্পন্ন করা যায়নি। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
                sources: [],
                tokens: 0,
                cost: 0.0,
                latencyMs: 10,
            );
        }

        if (! empty($this->queuedResponses)) {
            $resp = array_shift($this->queuedResponses);
            if (isset($options['sources']) && empty($resp->sources)) {
                $resp->sources = $options['sources'];
            }

            return $resp;
        }

        return new LlmResponse(
            text: $this->defaultResponse ?? "উত্তর: {$userPrompt}",
            sources: $options['sources'] ?? [],
            tokens: 45,
            cost: 0.000005,
            latencyMs: 15,
        );
    }

    public function getCalls(): array
    {
        return $this->calls;
    }

    public function getLastCall(): ?array
    {
        return empty($this->calls) ? null : end($this->calls);
    }

    public function assertPromptContains(string $needle): bool
    {
        foreach ($this->calls as $call) {
            if (str_contains($call['user_prompt'], $needle) || str_contains($call['system_prompt'], $needle)) {
                return true;
            }
        }

        return false;
    }
}
