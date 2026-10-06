<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HadithBookResource;
use App\Http\Resources\Api\V1\HadithResource;
use App\Models\Hadith;
use App\Models\HadithBook;
use App\Services\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HadithApiController extends Controller
{
    /**
     * Get list of Hadith books.
     */
    public function books(Request $request): JsonResponse
    {
        $books = Cache::remember('api_hadith_books_list', 86400, function () {
            return HadithBook::withCount(['hadiths', 'chapters'])->get();
        });

        return response()->json([
            'success' => true,
            'total' => $books->count(),
            'data' => HadithBookResource::collection($books),
        ]);
    }

    /**
     * Get chapters for a specific Hadith book.
     */
    public function chapters(Request $request, string $slug): JsonResponse
    {
        $book = HadithBook::where('slug', $slug)->with(['chapters'])->first();

        if (! $book) {
            return response()->json([
                'success' => false,
                'message' => 'হাদিস গ্রন্থটি পাওয়া যায়নি।',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'book' => [
                'id' => $book->id,
                'slug' => $book->slug,
                'name_bangla' => $book->name_bangla,
                'name_arabic' => $book->name_arabic,
            ],
            'chapters' => $book->chapters->map(fn ($c) => [
                'id' => $c->id,
                'number' => (int) $c->number,
                'name_arabic' => $c->name_arabic,
                'name_bangla' => $c->name_bangla,
                'name_english' => $c->name_english,
            ]),
        ]);
    }

    /**
     * Get paginated hadiths of a specific book.
     */
    public function hadiths(Request $request, string $slug): JsonResponse
    {
        $book = HadithBook::where('slug', $slug)->first();

        if (! $book) {
            return response()->json([
                'success' => false,
                'message' => 'হাদিস গ্রন্থটি পাওয়া যায়নি।',
            ], 404);
        }

        $query = Hadith::where('book_id', $book->id)->with(['book', 'chapter']);

        if ($chapterId = $request->input('chapter_id')) {
            $query->where('chapter_id', $chapterId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('text_bangla', 'like', "%{$search}%")
                    ->orWhere('text_arabic', 'like', "%{$search}%")
                    ->orWhere('narrator', 'like', "%{$search}%")
                    ->orWhere('hadith_number_in_book', 'like', "%{$search}%");
            });

            if ($request->user()) {
                app(EventTracker::class)->trackSearchPerformed($request->user(), $search, $query->count());
            }
        }

        $perPage = min(50, max(5, (int) $request->input('per_page', 20)));
        $hadiths = $query->orderBy('number')->paginate($perPage);

        return response()->json([
            'success' => true,
            'book' => [
                'id' => $book->id,
                'slug' => $book->slug,
                'name_bangla' => $book->name_bangla,
            ],
            'data' => HadithResource::collection($hadiths),
            'meta' => [
                'current_page' => $hadiths->currentPage(),
                'last_page' => $hadiths->lastPage(),
                'per_page' => $hadiths->perPage(),
                'total' => $hadiths->total(),
            ],
        ]);
    }

    /**
     * Get single Hadith detail.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $hadith = Hadith::with(['book', 'chapter'])->find($id);

        if (! $hadith) {
            return response()->json([
                'success' => false,
                'message' => 'হাদিসটি পাওয়া যায়নি।',
            ], 404);
        }

        if ($request->user() && $hadith->book) {
            app(EventTracker::class)->trackHadithViewed($request->user(), $hadith->book->slug, (int) $hadith->number);
        }

        return response()->json([
            'success' => true,
            'hadith' => new HadithResource($hadith),
        ]);
    }
}
