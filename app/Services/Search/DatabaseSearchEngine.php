<?php

namespace App\Services\Search;

use App\Contracts\SearchEngine;
use App\Models\Article;
use App\Models\Ayah;
use App\Models\AyahTranslation;
use App\Models\Course;
use App\Models\Fatwa;
use App\Models\Hadith;
use App\Models\Lesson;
use App\Models\Publication;
use App\Models\Teacher;

class DatabaseSearchEngine implements SearchEngine
{
    /**
     * Perform unified or type-specific search across the platform.
     */
    public function search(string $query, ?string $type = null, int $limit = 20, array $options = []): array
    {
        $rawQuery = trim($query);
        if ($rawQuery === '') {
            return [
                'query' => '',
                'total' => 0,
                'groups' => [],
                'results' => [],
            ];
        }

        $isArabic = TextNormalizer::isArabic($rawQuery);
        $strippedArabic = $isArabic ? TextNormalizer::stripArabicHarakat($rawQuery) : '';
        $arabicPattern = $this->buildArabicWildcardPattern($strippedArabic);

        $synonyms = TextNormalizer::expandBengaliSynonyms($rawQuery);
        $tokens = TextNormalizer::tokenize($rawQuery);
        $searchTerms = array_values(array_unique(array_merge([$rawQuery], $synonyms, $tokens)));

        if ($type !== null) {
            $normalizedType = $this->normalizeTypeKey($type);
            $results = $this->searchSpecificType($normalizedType, $searchTerms, $arabicPattern, $isArabic, $limit, $options);

            return [
                'query' => $rawQuery,
                'type' => $normalizedType,
                'total' => count($results),
                'results' => $results,
            ];
        }

        // Unified Search across all 8 groups
        $perGroupLimit = $options['per_group_limit'] ?? 6;

        $groups = [
            'courses' => $this->searchCourses($searchTerms, $perGroupLimit),
            'teachers' => $this->searchTeachers($searchTerms, $perGroupLimit),
            'lessons' => $this->searchLessons($searchTerms, $perGroupLimit),
            'hadiths' => $this->searchHadiths($searchTerms, $arabicPattern, $isArabic, $perGroupLimit),
            'quran' => $this->searchQuran($searchTerms, $arabicPattern, $isArabic, $perGroupLimit),
            'fatawa' => $this->searchFatawa($searchTerms, $perGroupLimit),
            'articles' => $this->searchArticles($searchTerms, $perGroupLimit),
            'publications' => $this->searchPublications($searchTerms, $perGroupLimit),
        ];

        $total = 0;
        foreach ($groups as $items) {
            $total += count($items);
        }

        return [
            'query' => $rawQuery,
            'total' => $total,
            'groups' => $groups,
        ];
    }

    /**
     * Quick auto-complete suggestion items for Cmd/Ctrl+K palette.
     */
    public function suggest(string $query, int $limit = 8): array
    {
        $unified = $this->search($query, null, $limit, ['per_group_limit' => 3]);
        $suggestions = [];

        foreach ($unified['groups'] as $type => $items) {
            foreach ($items as $item) {
                $suggestions[] = $item;
                if (count($suggestions) >= $limit) {
                    break 2;
                }
            }
        }

        return $suggestions;
    }

    /**
     * Search courses.
     */
    protected function searchCourses(array $terms, int $limit): array
    {
        $query = Course::query()->whereIn('status', ['published', 'coming_soon']);

        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('short_description', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }
        });

        return $query->with(['instructor', 'category'])
            ->limit($limit)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'type' => 'course',
                    'type_label' => 'কোর্স',
                    'title' => $course->title,
                    'subtitle' => $course->category?->name ?? 'সাধারণ',
                    'description' => $course->short_description,
                    'thumbnail' => $course->thumbnail,
                    'url' => route('courses.show', $course->slug ?? $course->id),
                    'meta' => [
                        'price' => (float) $course->price,
                        'is_free' => (bool) $course->is_free,
                        'level' => $course->level,
                    ],
                ];
            })
            ->all();
    }

    /**
     * Search teachers.
     */
    protected function searchTeachers(array $terms, int $limit): array
    {
        $query = Teacher::query()->where('status', 'active');

        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('name', 'like', "%{$term}%")
                    ->orWhere('designation', 'like', "%{$term}%")
                    ->orWhere('headline', 'like', "%{$term}%")
                    ->orWhere('short_bio', 'like', "%{$term}%");
            }
        });

        return $query->limit($limit)
            ->get()
            ->map(function ($teacher) {
                return [
                    'id' => $teacher->id,
                    'type' => 'teacher',
                    'type_label' => 'শিক্ষক/স্কলার',
                    'title' => $teacher->name,
                    'subtitle' => $teacher->designation ?? $teacher->headline,
                    'description' => $teacher->short_bio,
                    'thumbnail' => $teacher->avatar,
                    'url' => route('teachers.show', $teacher->slug ?? $teacher->id),
                    'meta' => [
                        'is_verified' => (bool) $teacher->is_verified,
                    ],
                ];
            })
            ->all();
    }

    /**
     * Search lessons by title.
     */
    protected function searchLessons(array $terms, int $limit): array
    {
        $query = Lesson::query()->with('course');

        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('title', 'like', "%{$term}%");
            }
        });

        // Only lessons from published courses
        $query->whereHas('course', function ($q) {
            $q->whereIn('status', ['published', 'coming_soon']);
        });

        return $query->limit($limit)
            ->get()
            ->map(function ($lesson) {
                $courseSlug = $lesson->course?->slug ?? $lesson->course_id;

                return [
                    'id' => $lesson->id,
                    'type' => 'lesson',
                    'type_label' => 'পাঠ',
                    'title' => $lesson->title,
                    'subtitle' => $lesson->course?->title ?? 'কোর্স পাঠ',
                    'description' => null,
                    'thumbnail' => $lesson->course?->thumbnail,
                    'url' => route('courses.show', $courseSlug),
                    'meta' => [
                        'course_id' => $lesson->course_id,
                        'is_preview' => (bool) $lesson->is_preview,
                    ],
                ];
            })
            ->all();
    }

    /**
     * Search hadiths (Bangla + Arabic with harakat tolerance).
     */
    protected function searchHadiths(array $terms, string $arabicPattern, bool $isArabic, int $limit): array
    {
        $query = Hadith::query()->with(['book', 'chapter']);

        $query->where(function ($q) use ($terms, $arabicPattern, $isArabic) {
            if ($isArabic && $arabicPattern !== '') {
                $q->orWhere('text_arabic', 'like', $arabicPattern);
            }

            foreach ($terms as $term) {
                $q->orWhere('text_bangla', 'like', "%{$term}%")
                    ->orWhere('narrator', 'like', "%{$term}%")
                    ->orWhere('number', 'like', "%{$term}%")
                    ->orWhere('hadith_number_in_book', 'like', "%{$term}%");
            }
        });

        return $query->limit($limit)
            ->get()
            ->map(function ($hadith) {
                return [
                    'id' => $hadith->id,
                    'type' => 'hadith',
                    'type_label' => 'হাদিস',
                    'title' => ($hadith->book?->name_bangla ?? 'হাদিস').' - নং '.$hadith->number,
                    'subtitle' => $hadith->narrator ? 'বর্ণনাকারী: '.$hadith->narrator : ($hadith->chapter?->name_bangla ?? ''),
                    'description' => mb_substr(strip_tags($hadith->text_bangla ?? ''), 0, 160).'...',
                    'thumbnail' => null,
                    'url' => url('/hadith/'.($hadith->book?->slug ?? 'sahih-bukhari')."?number={$hadith->number}"),
                    'meta' => [
                        'grade' => $hadith->grade,
                        'text_arabic' => $hadith->text_arabic,
                    ],
                ];
            })
            ->all();
    }

    /**
     * Search Quran ayahs (Arabic text + Bangla translations).
     */
    protected function searchQuran(array $terms, string $arabicPattern, bool $isArabic, int $limit): array
    {
        if ($isArabic && $arabicPattern !== '') {
            $ayahs = Ayah::query()
                ->where('text_uthmani', 'like', $arabicPattern)
                ->with(['surah', 'banglaTranslation'])
                ->limit($limit)
                ->get();
        } else {
            // Search via Bengali translations
            $translations = AyahTranslation::query()
                ->where('language', 'bn')
                ->where(function ($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->orWhere('text', 'like', "%{$term}%");
                    }
                })
                ->with(['ayah.surah'])
                ->limit($limit)
                ->get();

            $ayahs = $translations->map(function ($tr) {
                $ayah = $tr->ayah;
                if ($ayah) {
                    $ayah->setRelation('banglaTranslation', $tr);
                }

                return $ayah;
            })->filter();
        }

        return $ayahs->map(function ($ayah) {
            $surahName = $ayah->surah?->name_bangla ?? ('সূরা নং '.$ayah->surah_id);

            return [
                'id' => $ayah->id,
                'type' => 'quran',
                'type_label' => 'আল-কুরআন',
                'title' => "{$surahName} [আয়াত {$ayah->number}]",
                'subtitle' => $ayah->text_uthmani,
                'description' => $ayah->banglaTranslation?->text ?? '',
                'thumbnail' => null,
                'url' => url('/quran/'.($ayah->surah?->number ?? $ayah->surah_id)."?ayah={$ayah->number}"),
                'meta' => [
                    'surah_id' => $ayah->surah_id,
                    'ayah_number' => $ayah->number,
                ],
            ];
        })->values()->all();
    }

    /**
     * Search fatawa.
     */
    protected function searchFatawa(array $terms, int $limit): array
    {
        $query = Fatwa::published();

        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('question_title', 'like', "%{$term}%")
                    ->orWhere('question_body', 'like', "%{$term}%")
                    ->orWhere('answer_body', 'like', "%{$term}%");
            }
        });

        return $query->with(['assignedScholar'])
            ->limit($limit)
            ->get()
            ->map(function ($fatwa) {
                return [
                    'id' => $fatwa->id,
                    'type' => 'fatwa',
                    'type_label' => 'ফতোয়া',
                    'title' => $fatwa->question_title,
                    'subtitle' => $fatwa->assignedScholar?->name ? 'উত্তর দিয়েছেন: '.$fatwa->assignedScholar->name : 'উত্তর প্রকাশিত',
                    'description' => mb_substr(strip_tags($fatwa->answer_body ?? $fatwa->question_body ?? ''), 0, 160).'...',
                    'thumbnail' => null,
                    'url' => route('fatawa.show', $fatwa->id),
                    'meta' => [
                        'views_count' => $fatwa->views_count,
                    ],
                ];
            })
            ->all();
    }

    /**
     * Search articles.
     */
    protected function searchArticles(array $terms, int $limit): array
    {
        $query = Article::published();

        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('excerpt', 'like', "%{$term}%")
                    ->orWhere('body', 'like', "%{$term}%");
            }
        });

        return $query->with(['author', 'category'])
            ->limit($limit)
            ->get()
            ->map(function ($article) {
                return [
                    'id' => $article->id,
                    'type' => 'article',
                    'type_label' => 'প্রবন্ধ',
                    'title' => $article->title,
                    'subtitle' => $article->author?->name ?? 'তাল্লুম আর্টিকেল',
                    'description' => $article->excerpt ?? mb_substr(strip_tags($article->body ?? ''), 0, 160).'...',
                    'thumbnail' => $article->thumbnail,
                    'url' => route('articles.show', $article->slug ?? $article->id),
                    'meta' => [
                        'category' => $article->category?->name,
                    ],
                ];
            })
            ->all();
    }

    /**
     * Search publications.
     */
    protected function searchPublications(array $terms, int $limit): array
    {
        $query = Publication::published();

        $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $q->orWhere('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            }
        });

        return $query->with(['author', 'category'])
            ->limit($limit)
            ->get()
            ->map(function ($pub) {
                return [
                    'id' => $pub->id,
                    'type' => 'publication',
                    'type_label' => 'প্রকাশনা',
                    'title' => $pub->title,
                    'subtitle' => $pub->type ? ucfirst($pub->type) : 'প্রকাশনা',
                    'description' => mb_substr(strip_tags($pub->description ?? ''), 0, 160).'...',
                    'thumbnail' => $pub->thumbnail,
                    'url' => $pub->file_url ? url($pub->file_url) : url('/publications'),
                    'meta' => [
                        'type' => $pub->type,
                        'file_url' => $pub->file_url,
                    ],
                ];
            })
            ->all();
    }

    /**
     * Search specific type.
     */
    protected function searchSpecificType(string $type, array $terms, string $arabicPattern, bool $isArabic, int $limit, array $options): array
    {
        return match ($type) {
            'courses' => $this->searchCourses($terms, $limit),
            'teachers' => $this->searchTeachers($terms, $limit),
            'lessons' => $this->searchLessons($terms, $limit),
            'hadiths' => $this->searchHadiths($terms, $arabicPattern, $isArabic, $limit),
            'quran' => $this->searchQuran($terms, $arabicPattern, $isArabic, $limit),
            'fatawa' => $this->searchFatawa($terms, $limit),
            'articles' => $this->searchArticles($terms, $limit),
            'publications' => $this->searchPublications($terms, $limit),
            default => [],
        };
    }

    /**
     * Normalize type key alias (e.g. course -> courses).
     */
    protected function normalizeTypeKey(string $type): string
    {
        $map = [
            'course' => 'courses',
            'courses' => 'courses',
            'teacher' => 'teachers',
            'teachers' => 'teachers',
            'lesson' => 'lessons',
            'lessons' => 'lessons',
            'hadith' => 'hadiths',
            'hadiths' => 'hadiths',
            'quran' => 'quran',
            'ayah' => 'quran',
            'ayahs' => 'quran',
            'fatwa' => 'fatawa',
            'fatawa' => 'fatawa',
            'article' => 'articles',
            'articles' => 'articles',
            'publication' => 'publications',
            'publications' => 'publications',
        ];

        return $map[strtolower(trim($type))] ?? 'courses';
    }

    /**
     * Build Arabic wildcard pattern that matches characters across optional harakat.
     */
    protected function buildArabicWildcardPattern(string $stripped): string
    {
        $trimmed = trim($stripped);
        if ($trimmed === '') {
            return '';
        }

        $chars = mb_str_split($trimmed);
        $cleanChars = [];
        foreach ($chars as $char) {
            if ($char === ' ') {
                $cleanChars[] = '%';
            } elseif (preg_match('/[\x{0600}-\x{06FF}]/u', $char)) {
                $cleanChars[] = $char;
            }
        }

        if (empty($cleanChars)) {
            return '';
        }

        return '%'.implode('%', $cleanChars).'%';
    }
}
