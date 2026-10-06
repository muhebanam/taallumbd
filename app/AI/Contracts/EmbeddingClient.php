<?php

namespace App\AI\Contracts;

interface EmbeddingClient
{
    /**
     * Generate vector embedding for a single string.
     *
     * @return array<float>
     */
    public function embedText(string $text): array;

    /**
     * Generate vector embeddings for a batch of strings.
     *
     * @param  array<string>  $texts
     * @return array<array<float>>
     */
    public function embedBatch(array $texts): array;

    /**
     * Determine if embedding client is operational.
     */
    public function isAvailable(): bool;
}
