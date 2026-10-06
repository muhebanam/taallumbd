<?php

namespace App\AI\Drivers;

use App\AI\Contracts\EmbeddingClient;

class FakeEmbeddingClient implements EmbeddingClient
{
    protected int $dimensions = 8;

    public function __construct(int $dimensions = 8)
    {
        $this->dimensions = $dimensions;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function embedText(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        // Deterministic pseudo-embedding based on hash of text
        $hash = md5($text);
        $vector = [];
        for ($i = 0; $i < $this->dimensions; $i++) {
            $hex = substr($hash, $i * 2, 2);
            $val = (hexdec($hex) / 127.5) - 1.0; // [-1.0, 1.0]
            $vector[] = round($val, 4);
        }

        return $vector;
    }

    public function embedBatch(array $texts): array
    {
        return array_map(fn ($t) => $this->embedText($t), $texts);
    }
}
