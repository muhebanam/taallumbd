<?php

namespace App\Http\Controllers;

use App\Models\Hadith;
use App\Models\HadithBook;
use App\Models\HadithChapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class HadithController extends Controller
{
    /**
     * Display list of Hadith Books.
     */
    public function index()
    {
        $books = Cache::remember('hadith_books_with_counts', 86400, function () {
            $items = HadithBook::withCount(['hadiths', 'chapters'])->get();

            return $items->isEmpty() ? $this->getDefaultBooks() : $items;
        });

        return Inertia::render('Hadith/Index', [
            'books' => $books,
        ]);
    }

    /**
     * Display Hadiths of a specific Book.
     */
    public function book(Request $request, string $slug)
    {
        $book = HadithBook::where('slug', $slug)->first();

        if (! $book) {
            $book = (object) $this->getDefaultBookBySlug($slug);
        }

        $chapterId = $request->query('chapter');
        $search = $request->query('search');
        $bookId = is_object($book) ? ($book->id ?? 0) : ($book['id'] ?? 0);

        $hadithsQuery = Hadith::where('book_id', $bookId)
            ->when($chapterId, fn ($q) => $q->where('chapter_id', $chapterId))
            ->when($search, function ($q, $s) {
                $q->where('text_bangla', 'like', "%{$s}%")
                    ->orWhere('text_arabic', 'like', "%{$s}%")
                    ->orWhere('narrator', 'like', "%{$s}%")
                    ->orWhere('number', $s);
            })
            ->with('chapter')
            ->orderBy('number');

        $hadiths = $hadithsQuery->paginate(20)->withQueryString();

        // If no records in DB yet, fallback sample hadiths
        if ($hadiths->isEmpty()) {
            $hadiths = $this->getSampleHadiths($book);
        }

        $chapters = HadithChapter::where('book_id', $bookId)
            ->withCount('hadiths')
            ->orderBy('number')
            ->get();

        return Inertia::render('Hadith/Book', [
            'book' => $book,
            'hadiths' => $hadiths,
            'chapters' => $chapters,
            'filters' => [
                'chapter' => $chapterId,
                'search' => $search,
            ],
        ]);
    }

    private function getDefaultBooks(): array
    {
        return [
            [
                'id' => 1,
                'name_arabic' => 'صحيح البخاري',
                'name_bangla' => 'সহীহুল বুখারী',
                'name_english' => 'Sahih al-Bukhari',
                'slug' => 'sahih-bukhari',
                'author' => 'ইমাম মুহাম্মদ বিন ইসমাইল আল-বুখারী (রহ.)',
                'total_hadith' => 7563,
                'description' => 'কুরআনুল কারীমের পর মুসলিম উম্মাহর নিকট সর্বাধিক বিশুদ্ধতম হাদীস গ্রন্থ।',
            ],
            [
                'id' => 2,
                'name_arabic' => 'صحيح مسلم',
                'name_bangla' => 'সহীহ মুসলিম',
                'name_english' => 'Sahih Muslim',
                'slug' => 'sahih-muslim',
                'author' => 'ইমাম মুসলিম বিন হাজ্জাজ আন-নিশাপুরী (রহ.)',
                'total_hadith' => 7453,
                'description' => 'মুহাক্কিক আলেমদের মতে সুবিন্যস্ত উপস্থাপনায় হাদীস সংকলনের এক অনন্য প্রামাণ্য গ্রন্থ।',
            ],
            [
                'id' => 3,
                'name_arabic' => 'سنن أبي داود',
                'name_bangla' => 'সুনানে আবূ দাউদ',
                'name_english' => 'Sunan Abi Dawud',
                'slug' => 'sunan-abu-dawud',
                'author' => 'ইমাম আবূ দাউদ সুলাইমান আস-সিজিস্তানী (রহ.)',
                'total_hadith' => 5274,
                'description' => 'ফিকহী মাসআলা ও আহকামের হাদীস সম্বলিত অত্যন্ত নির্ভরযোগ্য গ্রন্থ।',
            ],
            [
                'id' => 4,
                'name_arabic' => 'جامع الترمذي',
                'name_bangla' => 'জামে আত-তিরমিযী',
                'name_english' => 'Jami at-Tirmidhi',
                'slug' => 'jami-at-tirmidhi',
                'author' => 'ইমাম মুহাম্মদ বিন ঈসা আত-তিরমিযী (রহ.)',
                'total_hadith' => 3956,
                'description' => 'হাদীসের মান নির্ণয় ও ফুকাহায়ে কেরামের মতভেদ বিশ্লেষণের এক অতুলনীয় ভাণ্ডার।',
            ],
            [
                'id' => 5,
                'name_arabic' => 'سنن النسائي',
                'name_bangla' => 'সুনানে আন-নাসায়ী',
                'name_english' => 'Sunan an-Nasa\'i',
                'slug' => 'sunan-an-nasai',
                'author' => 'ইমাম আহমাদ বিন শুআইব আন-নাসায়ী (রহ.)',
                'total_hadith' => 5758,
                'description' => 'সূক্ষ্ম সনদ বিশ্লেষণ ও নির্ভরযোগ্য বর্ণনাকারীদের হাদীস সংকলন।',
            ],
            [
                'id' => 6,
                'name_arabic' => 'رياض الصالحين',
                'name_bangla' => 'রিয়াযুস সলেহীন',
                'name_english' => 'Riyad as-Salihin',
                'slug' => 'riyad-as-salihin',
                'author' => 'ইমাম আবু জাকারিয়া মুহিউদ্দীন আন-নববী (রহ.)',
                'total_hadith' => 1896,
                'description' => 'আত্মশুদ্ধি, চারিত্রিক গুণাবলী ও দৈনন্দিন আমলের জন্য সর্বাধিক পঠিত হাদীস সংকলন।',
            ],
        ];
    }

    private function getDefaultBookBySlug(string $slug): object
    {
        $books = $this->getDefaultBooks();
        foreach ($books as $b) {
            if ($b['slug'] === $slug) {
                return (object) $b;
            }
        }

        return (object) [
            'id' => 1,
            'name_arabic' => 'الحديث الشريف',
            'name_bangla' => 'হাদীস সংকলন',
            'slug' => $slug,
            'author' => 'মুহাদ্দিসীন পরিষদ',
            'total_hadith' => 0,
            'description' => 'বিশ্বস্ত সূত্রে বর্ণিত হাদীস গ্রন্থ।',
        ];
    }

    private function getSampleHadiths($book): object
    {
        return (object) [
            'data' => [
                [
                    'id' => 1,
                    'number' => 1,
                    'hadith_number_in_book' => '১',
                    'text_arabic' => 'إِنَّمَا الأَعْمَالُ بِالنِّيَّاتِ، وَإِنَّمَا لِكُلِّ امْرِئٍ مَا نَوَى، فَمَنْ كَانَتْ هِجْرَتُهُ إِلَى دُنْيَا يُصِيبُهَا أَوْ إِلَى امْرَأَةٍ يَنْكِحُهَا فَهِجْرَتُهُ إِلَى مَا هَاجَرَ إِلَيْهِ.',
                    'text_bangla' => 'সকল কাজ নিয়তের ওপর নির্ভরশীল এবং প্রত্যেক ব্যক্তি তার নিয়ত অনুসারেই ফলাফল পাবে। সুতরাং যার হিজরত হবে দুনিয়া অর্জনের জন্য অথবা কোনো নারীকে বিবাহ করার উদ্দেশ্যে, তার হিজরত সেই উদ্দেশ্যেই গণ্য হবে যার জন্য সে হিজরত করেছে।',
                    'narrator' => 'উমর ইবনুল খাত্তাব (রা.)',
                    'grade' => 'সহীহ',
                    'grade_by' => 'মুত্তাফাকুন আলাইহ',
                    'explanation' => 'ইসলামের যাবতীয় আমল গ্রহণের ক্ষেত্রে ইখলাস বা খাঁটি নিয়তের গুরুত্ব সম্পর্কে এটি ইসলামের অন্যতম ভিত্তিপ্রস্তর হাদীস।',
                ],
                [
                    'id' => 2,
                    'number' => 2,
                    'hadith_number_in_book' => '২',
                    'text_arabic' => 'الْمُسْلِمُ مَنْ سَلِمَ الْمُسْلِمُونَ مِنْ لِسَانِهِ وَيَدِهِ، وَالْمُهَاجِرُ مَنْ هَجَرَ مَا نَهَى اللَّهُ عَنْهُ.',
                    'text_bangla' => 'প্রকৃত মুসলিম সে-ই, যার জিহ্বা ও হাত থেকে অন্য মুসলিম নিরাপদ থাকে। আর প্রকৃত মুহাজির সে, যে আল্লাহ যা নিষেধ করেছেন তা বর্জন করে।',
                    'narrator' => 'আবদুল্লাহ ইবনে আমর (রা.)',
                    'grade' => 'সহীহ',
                    'grade_by' => 'সহীহ বুখারী ও মুসলিম',
                    'explanation' => 'অন্যের অধিকার রক্ষা করা এবং আল্লাহর নিষেধাজ্ঞা থেকে বিরত থাকার নির্দেশ।',
                ],
            ],
            'links' => [],
            'total' => 2,
            'per_page' => 20,
            'current_page' => 1,
        ];
    }
}
