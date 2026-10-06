<?php

namespace App\Http\Controllers;

use App\Services\Search\UnifiedSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __construct(
        protected UnifiedSearchService $searchService
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
        if (trim($query) !== '') {
            $results = $this->searchService->search(
                query: $query,
                type: $type,
                limit: 30,
                user: $request->user(),
                options: ['per_group_limit' => 8]
            );
        }

        return Inertia::render('Search/Index', [
            'searchQuery' => $query,
            'activeType' => $type ?? 'all',
            'searchResults' => $results,
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
            limit: 20,
            user: $request->user(),
            options: ['per_group_limit' => 4]
        );

        $flatItems = [];
        if (! empty($searchData['groups'])) {
            foreach ($searchData['groups'] as $items) {
                foreach ($items as $item) {
                    $flatItems[] = $item;
                    if (count($flatItems) >= 12) {
                        break 2;
                    }
                }
            }
        }

        return response()->json([
            'query' => $query,
            'total' => $searchData['total'] ?? 0,
            'items' => $flatItems,
            'groups' => $searchData['groups'] ?? [],
        ]);
    }
}
