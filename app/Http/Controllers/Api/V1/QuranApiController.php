<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\SurahResource;
use App\Models\MemorizationProgress;
use App\Models\Surah;
use App\Services\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class QuranApiController extends Controller
{
    /**
     * Get list of all 114 Surahs.
     */
    public function surahs(Request $request): JsonResponse
    {
        $surahs = Cache::remember('api_quran_surahs_list', 86400, function () {
            return Surah::orderBy('number')->get();
        });

        if ($search = $request->input('search')) {
            $surahs = $surahs->filter(function ($s) use ($search) {
                return str_contains(strtolower($s->name_bangla), strtolower($search))
                    || str_contains(strtolower($s->name_transliteration), strtolower($search))
                    || str_contains((string) $s->number, $search);
            })->values();
        }

        return response()->json([
            'success' => true,
            'total' => $surahs->count(),
            'data' => SurahResource::collection($surahs),
        ]);
    }

    /**
     * Get specific Surah details with Ayahs, Translations, and audio links.
     */
    public function surah(Request $request, int $number): JsonResponse
    {
        $surah = Cache::remember("api_quran_surah_{$number}", 86400, function () use ($number) {
            return Surah::where('number', $number)
                ->with(['ayahs' => fn ($q) => $q->orderBy('number')->with('banglaTranslation')])
                ->first();
        });

        if (! $surah) {
            return response()->json([
                'success' => false,
                'message' => 'অনুরোধকৃত সূরাটি পাওয়া যায়নি।',
            ], 404);
        }

        if ($request->user()) {
            app(EventTracker::class)->trackQuranRead($request->user(), $number);
        }

        return response()->json([
            'success' => true,
            'surah' => new SurahResource($surah),
        ]);
    }

    /**
     * Save student Quran reading / memorization progress.
     */
    public function updateProgress(Request $request): JsonResponse
    {
        $request->validate([
            'surah_number' => 'required|integer|min:1|max:114',
            'ayah_from' => 'nullable|integer|min:1',
            'ayah_to' => 'nullable|integer|min:1',
            'status' => 'required|string|in:reading,memorized,in_progress',
            'notes' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        $surah = Surah::where('number', $request->surah_number)->firstOrFail();

        $progress = MemorizationProgress::updateOrCreate(
            ['user_id' => $user->id, 'surah_id' => $surah->id],
            [
                'ayah_from' => $request->ayah_from ?: 1,
                'ayah_to' => $request->ayah_to ?: $surah->ayah_count,
                'status' => $request->status,
                'notes' => $request->notes,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'কুরআন অধ্যায়ন ও হিফজ প্রগ্রেস সংরক্ষিত হয়েছে।',
            'progress' => [
                'surah_number' => $surah->number,
                'surah_name' => $surah->name_bangla,
                'status' => $progress->status,
                'ayah_from' => $progress->ayah_from,
                'ayah_to' => $progress->ayah_to,
            ],
        ]);
    }
}
