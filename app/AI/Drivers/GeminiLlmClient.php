<?php

namespace App\AI\Drivers;

use App\AI\Contracts\LlmClient;
use App\AI\DTOs\LlmResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiLlmClient implements LlmClient
{
    protected string $apiKey;

    protected string $model;

    protected float $temperature;

    protected int $maxTokens;

    protected int $timeout;

    protected int $maxRetries;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? (string) config('ai.gemini.api_key', '');
        $this->model = $config['model'] ?? (string) config('ai.gemini.model', 'gemini-1.5-flash');
        $this->temperature = (float) ($config['temperature'] ?? config('ai.gemini.temperature', 0.3));
        $this->maxTokens = (int) ($config['max_tokens'] ?? config('ai.gemini.max_tokens', 1024));
        $this->timeout = (int) ($config['timeout'] ?? config('ai.gemini.timeout', 15));
        $this->maxRetries = (int) ($config['max_retries'] ?? config('ai.gemini.max_retries', 2));
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function generateText(string $prompt, array $options = []): LlmResponse
    {
        return $this->generateWithSystemPrompt('', $prompt, $options);
    }

    public function generateWithSystemPrompt(string $systemPrompt, string $userPrompt, array $options = []): LlmResponse
    {
        if (! $this->isAvailable()) {
            return new LlmResponse(
                text: 'AI সেবা বর্তমানে কনফিগার করা হয়নি। অনুগ্রহ করে পরবর্তীতে যোগাযোগ করুন।',
                sources: [],
                tokens: 0,
                cost: 0.0,
                latencyMs: 0,
            );
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userPrompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => (float) ($options['temperature'] ?? $this->temperature),
                'maxOutputTokens' => (int) ($options['max_tokens'] ?? $this->maxTokens),
            ],
        ];

        if (! empty($systemPrompt)) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemPrompt],
                ],
            ];
        }

        $start = hrtime(true);
        $attempts = 0;
        $delayMs = 1000;

        while ($attempts <= $this->maxRetries) {
            $attempts++;

            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($url, $payload);

                $elapsedMs = (int) round((hrtime(true) - $start) / 1e6);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    $tokens = (int) ($json['usageMetadata']['totalTokenCount'] ?? 0);

                    // Free tier cost is 0.00, but compute nominal cost for tracking
                    $cost = round(($tokens / 1000) * 0.0001, 6);

                    return new LlmResponse(
                        text: trim($text),
                        sources: $options['sources'] ?? [],
                        tokens: $tokens,
                        cost: $cost,
                        latencyMs: $elapsedMs,
                    );
                }

                // Handle 429 Resource Exhausted / Rate limit with backoff
                if ($response->status() === 429) {
                    Log::warning("Gemini API rate limit hit (429). Attempt {$attempts}/{$this->maxRetries}. Backing off {$delayMs}ms.");
                    if ($attempts <= $this->maxRetries) {
                        usleep($delayMs * 1000);
                        $delayMs *= 2; // exponential backoff

                        continue;
                    }

                    return new LlmResponse(
                        text: 'সার্ভারে অতিরিক্ত চাপের কারণে অনুরোধটি সম্পন্ন করা যায়নি। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
                        sources: [],
                        tokens: 0,
                        cost: 0.0,
                        latencyMs: $elapsedMs,
                    );
                }

                Log::error('Gemini API call failed: '.$response->body());
                break;
            } catch (\Throwable $e) {
                Log::error("Gemini API error (attempt {$attempts}): ".$e->getMessage());
                if ($attempts <= $this->maxRetries) {
                    usleep($delayMs * 1000);
                    $delayMs *= 2;

                    continue;
                }
                break;
            }
        }

        $elapsedMs = (int) round((hrtime(true) - $start) / 1e6);

        return new LlmResponse(
            text: 'দুঃখিত, সংযোগে ত্রুটি ঘটেছে। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
            sources: [],
            tokens: 0,
            cost: 0.0,
            latencyMs: $elapsedMs,
        );
    }
}
