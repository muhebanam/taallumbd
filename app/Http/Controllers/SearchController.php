<?php

namespace App\Http\Controllers;

use App\AI\Services\AiSearchService;
use App\Services\Search\UnifiedSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(
        protected UnifiedSearchService $searchService,
        protected AiSearchService $aiSearchService
    ) {}

    /**
     * Display unified search results page with type filtering.
     */
    public function index(Request $request): Response
    {
        $query = (string) $request->input('q', '');
        $type = $request->input('type');
        if ($type === 'all' || empty($type)) {
            $type = null;
        }

        $results = [];
        $aiSummary = null;

        if (trim($query) !== '') {
            $results = $this->searchService->search(
                query: $query,
                type: $type,
                limit: 30,
                user: $request->user(),
                options: ['per_group_limit' => 8]
            );

            // Optional AI Educational Summary (if enabled)
            if (config('ai.enabled', true) && ! empty($results['items'])) {
                try {
                    $summaryResponse = $this->aiSearchService->summarizeSearch(
                        query: $query,
                        searchResults: $results,
                        user: $request->user()
                    );
                    $aiSummary = $summaryResponse?->toArray();
                } catch (\Throwable $e) {
                    $aiSummary = null;
                }
            }
        }

        return Inertia::render('Search/Index', [
            'searchQuery' => $query,
            'activeType' => $type ?? 'all',
            'searchResults' => $results,
            'aiSummary' => $aiSummary,
            'aiEnabled' => (bool) config('ai.enabled', true),
        ]);
    }

    /**
     * Live search endpoint for Cmd/Ctrl+K Command Palette and autocomplete.
     */
    public function live(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        if (mb_strlen(trim($query)) < 2) {
            return response()->json([
                'query' => $query,
                'items' => [],
                'groups' => [],
            ]);
        }

        $searchData = $this->searchService->search(
            query: $query,
            type: null,
            limit: 10,
            user: $request->user(),
            options: ['per_group_limit' => 3]
        );

        return response()->json([
            'query' => $query,
            'total' => $searchData['total'] ?? 0,
            'items' => array_slice($searchData['items'] ?? [], 0, 10),
            'groups' => $searchData['groups'] ?? [],
        ]);
    }

    /**
     * On-demand AI Search Summary endpoint.
     */
    public function summary(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        if (trim($query) === '') {
            return response()->json(['success' => false, 'summary' => null]);
        }

        $results = $this->searchService->search(
            query: $query,
            type: null,
            limit: 15,
            user: $request->user()
        );

        $response = $this->aiSearchService->summarizeSearch(
            query: $query,
            searchResults: $results,
            user: $request->user()
        );

        return response()->json([
            'success' => $response !== null,
            'summary' => $response?->toArray(),
        ]);
    }
}
