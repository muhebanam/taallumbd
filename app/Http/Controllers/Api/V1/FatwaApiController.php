<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\FatwaResource;
use App\Models\Category;
use App\Models\Fatwa;
use App\Services\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FatwaApiController extends Controller
{
    /**
     * List public answered fatawa with search and category filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Fatwa::where('status', 'answered')
            ->where('is_private', false)
            ->with(['category', 'mufti', 'teacher']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question_title', 'like', "%{$search}%")
                    ->orWhere('question_body', 'like', "%{$search}%")
                    ->orWhere('answer_body', 'like', "%{$search}%");
            });

            if ($request->user()) {
                app(EventTracker::class)->trackSearchPerformed($request->user(), $search, $query->count());
            }
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($scholarId = $request->input('scholar_id')) {
            $query->where('teacher_id', $scholarId);
        }

        $perPage = min(50, max(5, (int) $request->input('per_page', 15)));
        $fatawa = $query->latest('answered_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => FatwaResource::collection($fatawa),
            'meta' => [
                'current_page' => $fatawa->currentPage(),
                'last_page' => $fatawa->lastPage(),
                'per_page' => $fatawa->perPage(),
                'total' => $fatawa->total(),
            ],
        ]);
    }

    /**
     * Get single fatwa detail.
     */
    public function show(Request $request, Fatwa $fatwa): JsonResponse
    {
        if ($fatwa->is_private && (! $request->user() || ($request->user()->id !== $fatwa->question_user_id && ! $request->user()->isAdmin()))) {
            return response()->json([
                'success' => false,
                'message' => 'এই ফতোয়াটি ব্যক্তিগত।',
            ], 403);
        }

        $fatwa->increment('views_count');
        $fatwa->load(['category', 'mufti', 'teacher']);

        return response()->json([
            'success' => true,
            'fatwa' => new FatwaResource($fatwa),
        ]);
    }

    /**
     * Submit a new fatwa question.
     */
    public function ask(Request $request): JsonResponse
    {
        $request->validate([
            'question_title' => 'required|string|max:255',
            'question_body' => 'required|string|min:10',
            'category_id' => 'nullable|exists:categories,id',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_private' => 'boolean',
            'questioner_name' => 'nullable|string|max:100',
            'questioner_phone' => 'nullable|string|max:30',
        ]);

        $user = $request->user();
        $categoryId = $request->category_id;
        if (! $categoryId) {
            $category = Category::first() ?? Category::create(['name' => 'সাধারণ প্রশ্নোত্তর', 'slug' => 'general-qa']);
            $categoryId = $category->id;
        }

        $fatwa = Fatwa::create([
            'question_user_id' => $user?->id,
            'category_id' => $categoryId,
            'teacher_id' => $request->teacher_id,
            'questioner_name' => $user?->name ?: $request->input('questioner_name', 'অজ্ঞাত'),
            'questioner_email' => $user?->email,
            'questioner_phone' => $request->questioner_phone ?: $user?->phone,
            'question_title' => $request->question_title,
            'question_body' => $request->question_body,
            'is_private' => $request->boolean('is_private', false),
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'আপনার প্রশ্নটি সফলভাবে জমা নেওয়া হয়েছে। বিজ্ঞ মুফতি সাহেব পর্যালোচনা করে উত্তর প্রদান করবেন।',
            'fatwa_id' => $fatwa->id,
            'status' => 'pending',
        ], 201);
    }
}
