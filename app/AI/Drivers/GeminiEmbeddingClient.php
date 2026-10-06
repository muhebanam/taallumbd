<?php

namespace App\AI\Drivers;

use App\AI\Contracts\EmbeddingClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiEmbeddingClient implements EmbeddingClient
{
    protected string $apiKey;

    protected string $model;

    protected int $timeout;

    public function __construct(array $config = [])
    {
        $this->apiKey = $config['api_key'] ?? (string) config('ai.gemini.api_key', '');
        $this->model = $config['embedding_model'] ?? (string) config('ai.gemini.embedding_model', 'text-embedding-004');
        $this->timeout = (int) ($config['timeout'] ?? config('ai.gemini.timeout', 15));
    }

    public function isAvailable(): bool
    {
        return ! empty($this->apiKey);
    }

    public function embedText(string $text): array
    {
        if (! $this->isAvailable() || trim($text) === '') {
            return [];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:embedContent?key={$this->apiKey}";

        try {
            $response = Http::timeout($this->timeout)->post($url, [
                'model' => "models/{$this->model}",
                'content' => [
                    'parts' => [
                        ['text' => mb_substr($text, 0, 2048)],
                    ],
                ],
            ]);

            if ($response->successful()) {
                return (array) ($response->json('embedding.values') ?? []);
            }

            Log::error('Gemini Embed call failed: '.$response->body());
        } catch (\Throwable $e) {
            Log::error('Gemini Embed exception: '.$e->getMessage());
        }

        return [];
    }

    public function embedBatch(array $texts): array
    {
        $results = [];
        foreach ($texts as $text) {
            $results[] = $this->embedText($text);
        }

        return $results;
    }
}
