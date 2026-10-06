<?php

namespace App\AI\DTOs;

class LlmResponse
{
    public function __construct(
        public string $text,
        public array $sources = [],
        public int $tokens = 0,
        public float $cost = 0.0,
        public int $latencyMs = 0,
        public bool $isCached = false,
        public bool $isRedirectToFatwa = false,
        public ?string $redirectUrl = null,
        public string $disclaimer = '',
    ) {
        if ($this->disclaimer === '') {
            $this->disclaimer = (string) config('ai.disclaimer', '');
        }
    }

    public static function create(array $data): self
    {
        return new self(
            text: $data['text'] ?? '',
            sources: $data['sources'] ?? [],
            tokens: (int) ($data['tokens'] ?? 0),
            cost: (float) ($data['cost'] ?? 0.0),
            latencyMs: (int) ($data['latencyMs'] ?? $data['latency_ms'] ?? 0),
            isCached: (bool) ($data['isCached'] ?? false),
            isRedirectToFatwa: (bool) ($data['isRedirectToFatwa'] ?? false),
            redirectUrl: $data['redirectUrl'] ?? null,
            disclaimer: $data['disclaimer'] ?? (string) config('ai.disclaimer', ''),
        );
    }

    public static function fatwaRedirect(string $message = '', ?string $redirectUrl = '/fatawa/ask'): self
    {
        $defaultMsg = config('ai.fatwa_redirect_message', 'ফিকহী বিধান বা ফতোয়ার জন্য অনুগ্রহ করে আমাদের যোগ্য মুফতি বোর্ডের কাছে প্রশ্ন করুন।');
        $text = $message !== '' ? $message : $defaultMsg;

        return new self(
            text: $text,
            sources: [
                [
                    'title' => 'দারুল ইফতা ও ফতোয়া বোর্ড',
                    'reference' => 'আত-তাআল্লুম ফতোয়া বিভাগ',
                    'url' => $redirectUrl ?? '/fatawa/ask',
                ],
            ],
            tokens: 0,
            cost: 0.0,
            latencyMs: 5,
            isCached: true,
            isRedirectToFatwa: true,
            redirectUrl: $redirectUrl ?? '/fatawa/ask',
            disclaimer: (string) config('ai.disclaimer', ''),
        );
    }

    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'sources' => $this->sources,
            'tokens' => $this->tokens,
            'cost' => $this->cost,
            'latency_ms' => $this->latencyMs,
            'is_cached' => $this->isCached,
            'is_redirect_to_fatwa' => $this->isRedirectToFatwa,
            'redirect_url' => $this->redirectUrl,
            'disclaimer' => $this->disclaimer,
        ];
    }
}
