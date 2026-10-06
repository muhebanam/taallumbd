<?php

namespace App\Services\Search;

use App\Contracts\SearchEngine;
use App\Models\User;
use App\Services\EventTracker;

class UnifiedSearchService
{
    public function __construct(
        protected SearchEngine $engine,
        protected EventTracker $tracker
    ) {}

    /**
     * Search across platform and record analytics event.
     */
    public function search(string $query, ?string $type = null, int $limit = 20, ?User $user = null, array $options = []): array
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return [
                'query' => '',
                'total' => 0,
                'groups' => [],
                'results' => [],
            ];
        }

        $results = $this->engine->search($trimmed, $type, $limit, $options);
        $totalCount = $results['total'] ?? 0;

        // Track search event via Phase 8 EventTracker
        try {
            $this->tracker->trackSearchPerformed(
                user: $user,
                query: $trimmed,
                resultsCount: $totalCount,
                extra: ['type' => $type ?? 'unified']
            );
        } catch (\Throwable $e) {
            if (app()->environment('testing')) {
                throw $e;
            }
        }

        return $results;
    }

    /**
     * Quick auto-complete suggestion items for Cmd/Ctrl+K palette.
     */
    public function suggest(string $query, int $limit = 8): array
    {
        $trimmed = trim($query);
        if ($trimmed === '') {
            return [];
        }

        return $this->engine->suggest($trimmed, $limit);
    }
}
