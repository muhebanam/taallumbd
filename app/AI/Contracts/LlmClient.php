<?php

namespace App\AI\Contracts;

use App\AI\DTOs\LlmResponse;

interface LlmClient
{
    /**
     * Generate text response from prompt.
     */
    public function generateText(string $prompt, array $options = []): LlmResponse;

    /**
     * Generate text response with system prompt instructions and context.
     */
    public function generateWithSystemPrompt(string $systemPrompt, string $userPrompt, array $options = []): LlmResponse;

    /**
     * Determine if the client is properly configured and operational.
     */
    public function isAvailable(): bool;
}
