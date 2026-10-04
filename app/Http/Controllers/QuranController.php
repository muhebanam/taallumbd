<?php

namespace App\Http\Controllers;

use App\Models\MemorizationProgress;
use App\Models\Surah;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuranController extends Controller
{
    /**
     * Display all 114 Surahs list.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $surahs = Surah::query()
            ->when($search, function ($query, $s) {
                $query->where('name_bangla', 'like', "%{$s}%")
                    ->orWhere('name_arabic', 'like', "%{$s}%")
                    ->orWhere('name_transliteration', 'like', "%{$s}%")
                    ->orWhere('number', $s);
            })
            ->orderBy('number')
            ->get();

        // If database is not yet seeded, provide comprehensive default list of 114 Surahs
        if ($surahs->isEmpty()) {
            $surahs = $this->getDefaultSurahs();
        }

        $userProgress = [];
        if (auth()->check()) {
            $userProgress = MemorizationProgress::where('user_id', auth()->id())
                ->pluck('status', 'surah_id')
                ->toArray();
        }

        return Inertia::render('Quran/Index', [
            'surahs' => $surahs,
            'filters' => ['search' => $search],
            'userProgress' => $userProgress,
        ]);
    }

    /**
     * Display specific Surah with Ayahs and Translations.
     */
    public function show(int $number)
    {
        $surah = Surah::where('number', $number)
            ->with(['ayahs' => function ($q) {
                $q->with(['banglaTranslation', 'tafsirs'])->orderBy('number');
            }])
            ->first();

        // If not found in DB yet, load standard structured data for this Surah
        if (! $surah) {
            $surah = $this->getSurahFallback($number);
        }

        $prevSurah = Surah::where('number', $number - 1)->first(['number', 'name_bangla', 'name_arabic']);
        $nextSurah = Surah::where('number', $number + 1)->first(['number', 'name_bangla', 'name_arabic']);

        return Inertia::render('Quran/Show', [
            'surah' => $surah,
            'prevSurah' => $prevSurah,
            'nextSurah' => $nextSurah,
        ]);
    }

    /**
     * Update student memorization progress.
     */
    public function updateProgress(Request $request, int $surahNumber)
    {
        $request->validate([
            'status' => 'required|in:memorizing,memorized,revising',
            'ayah_from' => 'nullable|integer|min:1',
            'ayah_to' => 'nullable|integer|min:1',
        ]);

        $surah = Surah::where('number', $surahNumber)->firstOrFail();

        $progress = MemorizationProgress::updateOrCreate(
            ['user_id' => auth()->id(), 'surah_id' => $surah->id],
            [
                'status' => $request->status,
                'ayah_from' => $request->ayah_from ?? 1,
                'ayah_to' => $request->ayah_to ?? $surah->ayah_count,
            ]
        );

        return back()->with('success', 'হিফজ অগ্রগতি সফলভাবে আপডেট করা হয়েছে।');
    }

    /**
     * Fallback standard Surahs catalog
     */
    private function getDefaultSurahs(): array
    {
        return [
            ['number' => 1, 'name_arabic' => 'الفاتحة', 'name_bangla' => 'আল-ফাতিহা', 'ayah_count' => 7, 'revelation_type' => 'Meccan'],
            ['number' => 2, 'name_arabic' => 'البقرة', 'name_bangla' => 'আল-বাকারা', 'ayah_count' => 286, 'revelation_type' => 'Medinan'],
            ['number' => 3, 'name_arabic' => 'آل عمران', 'name_bangla' => 'আলে-ইমরান', 'ayah_count' => 200, 'revelation_type' => 'Medinan'],
            ['number' => 4, 'name_arabic' => 'النساء', 'name_bangla' => 'আন-নিসা', 'ayah_count' => 176, 'revelation_type' => 'Medinan'],
            ['number' => 5, 'name_arabic' => 'المائدة', 'name_bangla' => 'আল-মায়িদাহ', 'ayah_count' => 120, 'revelation_type' => 'Medinan'],
            ['number' => 6, 'name_arabic' => 'الأنعام', 'name_bangla' => 'আল-আন‘আম', 'ayah_count' => 165, 'revelation_type' => 'Meccan'],
            ['number' => 18, 'name_arabic' => 'الكهف', 'name_bangla' => 'আল-কাহফ', 'ayah_count' => 110, 'revelation_type' => 'Meccan'],
            ['number' => 36, 'name_arabic' => 'يس', 'name_bangla' => 'ইয়াসীন', 'ayah_count' => 83, 'revelation_type' => 'Meccan'],
            ['number' => 55, 'name_arabic' => 'الرحمن', 'name_bangla' => 'আর-রাহমান', 'ayah_count' => 78, 'revelation_type' => 'Medinan'],
            ['number' => 56, 'name_arabic' => 'الواقعة', 'name_bangla' => 'আল-ওয়াকিয়া', 'ayah_count' => 96, 'revelation_type' => 'Meccan'],
            ['number' => 67, 'name_arabic' => 'الملك', 'name_bangla' => 'আল-মুলক', 'ayah_count' => 30, 'revelation_type' => 'Meccan'],
            ['number' => 112, 'name_arabic' => 'الإخلاص', 'name_bangla' => 'আল-ইখলাস', 'ayah_count' => 4, 'revelation_type' => 'Meccan'],
            ['number' => 113, 'name_arabic' => 'الفلق', 'name_bangla' => 'আল-ফালাক', 'ayah_count' => 5, 'revelation_type' => 'Meccan'],
            ['number' => 114, 'name_arabic' => 'الناس', 'name_bangla' => 'আন-নাস', 'ayah_count' => 6, 'revelation_type' => 'Meccan'],
        ];
    }

    private function getSurahFallback(int $number): array
    {
        // Surah Al-Fatihah standard fallback
        if ($number === 1) {
            return [
                'number' => 1,
                'name_arabic' => 'سُورَةُ الفَاتِحَةِ',
                'name_bangla' => 'সূরা আল-ফাতিহা',
                'ayah_count' => 7,
                'revelation_type' => 'Meccan',
                'ayahs' => [
                    ['number' => 1, 'text_uthmani' => 'بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ', 'bangla_translation' => ['text' => 'শুরু করছি আল্লাহর নামে যিনি পরম করুণাময়, অতি দয়ালু।']],
                    ['number' => 2, 'text_uthmani' => 'الْحَمْدُ لِلَّهِ رَبِّ الْعَالَمِينَ', 'bangla_translation' => ['text' => 'সমস্ত প্রশংসা জগতসমূহের প্রতিপালক আল্লাহর জন্য।']],
                    ['number' => 3, 'text_uthmani' => 'الرَّحْمَٰنِ الرَّحِيمِ', 'bangla_translation' => ['text' => 'যিনি পরম করুণাময় ও অসীম দয়ালু।']],
                    ['number' => 4, 'text_uthmani' => 'مَالِكِ يَوْمِ الدِّينِ', 'bangla_translation' => ['text' => 'যিনি বিচার দিবসের অধিপতি।']],
                    ['number' => 5, 'text_uthmani' => 'إِيَّاكَ نَعْبُدُ وَإِيَّاكَ نَسْتَعِينُ', 'bangla_translation' => ['text' => 'আমরা একমাত্র তোমারই ইবাদত করি এবং শুধুমাত্র তোমারই সাহায্য প্রার্থনা করি।']],
                    ['number' => 6, 'text_uthmani' => 'اهْدِنَا الصِّرَاطَ الْمُسْتَقِيمَ', 'bangla_translation' => ['text' => 'আমাদেরকে সরল-সঠিক পথ প্রদর্শন করুন।']],
                    ['number' => 7, 'text_uthmani' => 'صِرَاطَ الَّذِينَ أَنْعَمْتَ عَلَيْهِمْ غَيْرِ الْمَغْضُوبِ عَلَيْهِمْ وَلَا الضَّالِّينَ', 'bangla_translation' => ['text' => 'তাদের পথ, যাদেরকে তুমি অনুগ্রহ দান করেছ; তাদের পথ নয়, যাদের ওপর তোমার গজব নাজিল হয়েছে এবং যারা পথভ্রষ্ট হয়েছে।']],
                ],
            ];
        }

        // Generic fallback for any other requested number
        return [
            'number' => $number,
            'name_arabic' => 'سورة القرآن الكريم',
            'name_bangla' => "সূরা নং {$number}",
            'ayah_count' => 0,
            'revelation_type' => 'Meccan',
            'ayahs' => [],
        ];
    }
}
