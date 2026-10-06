<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Search\UnifiedSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnifiedSearchApiController extends Controller
{
    public function __construct(
        protected UnifiedSearchService $searchService
    ) {}

    /**
     * Unified search endpoint for mobile application & third-party consumers.
     */
    public function index(Request $request): JsonResponse
    {
        $query = (string) $request->input('q', '');
        $type = $request->input('type');
        if ($type === 'all' || empty($type)) {
            $type = null;
        }

        $limit = min(50, max(1, (int) $request->input('limit', 20)));

        $data = $this->searchService->search(
            query: $query,
            type: $type,
            limit: $limit,
            user: $request->user(),
            options: [
                'per_group_limit' => (int) $request->input('per_group_limit', 6),
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
