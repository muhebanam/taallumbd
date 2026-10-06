<?php

namespace App\AI\Services;

use App\AI\DTOs\LlmResponse;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class QuotaManager
{
    /**
     * Check if a specific user has remaining daily AI quota.
     */
    public function canUserRequest(?User $user): bool
    {
        if (! $user) {
            // Guest users share a temporary IP-based or limited quota
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $limit = (int) config('ai.quotas.user_daily_limit', 25);
        $key = $this->getUserKey($user->id);
        $current = (int) Cache::get($key, 0);

        return $current < $limit;
    }

    /**
     * Check if the global platform budget guard allows more requests today.
     */
    public function canSystemProcessRequest(): bool
    {
        $globalLimit = (int) config('ai.quotas.global_daily_limit', 500);
        $globalKey = $this->getGlobalKey();
        $current = (int) Cache::get($globalKey, 0);

        return $current < $globalLimit;
    }

    /**
     * Record an AI request execution.
     */
    public function recordUsage(?User $user, int $tokens = 0, float $cost = 0.0): void
    {
        $ttl = now()->endOfDay();

        // Increment user counter
        if ($user && ! $user->isAdmin()) {
            $userKey = $this->getUserKey($user->id);
            Cache::increment($userKey);
            Cache::put($userKey, Cache::get($userKey, 1), $ttl);
        }

        // Increment global counter
        $globalKey = $this->getGlobalKey();
        Cache::increment($globalKey);
        Cache::put($globalKey, Cache::get($globalKey, 1), $ttl);

        // Record global tokens & cost
        $dateStr = now()->format('Y-m-d');
        Cache::increment("ai_global_tokens:{$dateStr}", $tokens);
        Cache::put("ai_global_tokens:{$dateStr}", Cache::get("ai_global_tokens:{$dateStr}", $tokens), $ttl);
    }

    /**
     * Get remaining daily quota for user.
     */
    public function getUserRemainingQuota(?User $user): int
    {
        if (! $user || $user->isAdmin()) {
            return 999;
        }

        $limit = (int) config('ai.quotas.user_daily_limit', 25);
        $current = (int) Cache::get($this->getUserKey($user->id), 0);

        return max(0, $limit - $current);
    }

    /**
     * Get global statistics for today.
     */
    public function getGlobalUsageStats(): array
    {
        $dateStr = now()->format('Y-m-d');
        $limit = (int) config('ai.quotas.global_daily_limit', 500);
        $requests = (int) Cache::get($this->getGlobalKey(), 0);
        $tokens = (int) Cache::get("ai_global_tokens:{$dateStr}", 0);

        return [
            'date' => $dateStr,
            'requests_today' => $requests,
            'daily_limit' => $limit,
            'remaining_requests' => max(0, $limit - $requests),
            'tokens_today' => $tokens,
            'percent_used' => $limit > 0 ? (int) round(($requests / $limit) * 100) : 0,
        ];
    }

    /**
     * Get cached response if exists.
     */
    public function getCachedResponse(string $hash): ?LlmResponse
    {
        $data = Cache::get("ai_response_cache:{$hash}");
        if (is_array($data)) {
            $data['isCached'] = true;

            return LlmResponse::create($data);
        }

        return null;
    }

    /**
     * Cache response.
     */
    public function cacheResponse(string $hash, LlmResponse $response): void
    {
        $hours = (int) config('ai.quotas.cache_ttl_hours', 24);
        Cache::put("ai_response_cache:{$hash}", $response->toArray(), now()->addHours($hours));
    }

    protected function getUserKey(int $userId): string
    {
        $dateStr = now()->format('Y-m-d');

        return "ai_user_daily:{$userId}:{$dateStr}";
    }

    protected function getGlobalKey(): string
    {
        $dateStr = now()->format('Y-m-d');

        return "ai_global_daily:{$dateStr}";
    }
}
