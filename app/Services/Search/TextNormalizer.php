<?php

namespace App\Services\Search;

class TextNormalizer
{
    /**
     * Common Islamic synonyms in Bengali.
     * Each group contains mutually interchangeable terms.
     */
    protected static array $synonymGroups = [
        ['নামায', 'সালাত', 'নামাজ'],
        ['রোজা', 'সওম', 'সিয়াম', 'সিযাম', 'রোযা'],
        ['যাকাত', 'জাকাত', 'যাকাহ'],
        ['ওজু', 'ওযু', 'অজু'],
        ['হজ', 'হজ্ব', 'হজ্জ'],
        ['কুরআন', 'কোরআন', 'কোরান', 'কুরান'],
        ['নবী', 'রাসূল', 'রাসুল', 'পয়গম্বর'],
        ['দোয়া', 'দুআ', 'দোয়া', 'মুনাজাত'],
        ['হাদিস', 'হাদীস', 'রেওয়াত'],
        ['ফিকহ', 'ফিক্বহ', 'শরীয়ত', 'শরিয়ত'],
        ['ইমান', 'ঈমান', 'আকীদা', 'আকিদা'],
        ['তাওহীদ', 'তাওহিদ', 'একত্ববাদ'],
        ['তাফসির', 'তাফসীর'],
        ['সুন্নাহ', 'সুন্নত'],
        ['জান্নাত', 'বেহেশত'],
        ['জাহান্নাম', 'দোযখ', 'দোজখ'],
    ];

    /**
     * Remove Arabic harakat/tashkeel diacritics.
     */
    public static function stripArabicHarakat(string $text): string
    {
        // Unicode range for Arabic tashkeel / tanween / sukun / shaddah / dagger alif
        return preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $text) ?? $text;
    }

    /**
     * Fully normalize Arabic text: strip diacritics, unify alef, alif maqsura, ta marbuta.
     */
    public static function normalizeArabic(string $text): string
    {
        $text = self::stripArabicHarakat($text);

        // Normalize Alef variations: أ, إ, آ, ٱ -> ا
        $text = preg_replace('/[إأآٱ]/u', 'ا', $text) ?? $text;

        // Normalize Alif Maqsura: ى -> ي
        $text = preg_replace('/ى/u', 'ي', $text) ?? $text;

        // Normalize Ta Marbuta: ة -> ه
        $text = preg_replace('/ة/u', 'ه', $text) ?? $text;

        // Remove Tatweel / Kashida: ـ
        $text = preg_replace('/ـ/u', '', $text) ?? $text;

        // Remove Arabic punctuation (، ؛ ؟)
        $text = preg_replace('/[،؛؟]/u', ' ', $text) ?? $text;

        return trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
    }

    /**
     * Detect if text contains Arabic characters.
     */
    public static function isArabic(string $text): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}]/u', $text);
    }

    /**
     * Expand query tokens with Bengali Islamic synonyms.
     *
     * @return array<string>
     */
    public static function expandBengaliSynonyms(string $query): array
    {
        $words = preg_split('/[\s,;:!?।]+/u', trim($query), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $expanded = [];

        foreach ($words as $word) {
            $word = trim($word);
            if ($word === '') {
                continue;
            }

            $matchedGroup = false;
            foreach (self::$synonymGroups as $group) {
                if (in_array($word, $group, true)) {
                    $expanded = array_merge($expanded, $group);
                    $matchedGroup = true;
                    break;
                }
            }

            if (! $matchedGroup) {
                $expanded[] = $word;
            }
        }

        return array_values(array_unique($expanded));
    }

    /**
     * Tokenize text into search terms, handling Arabic and Bengali properly.
     *
     * @return array<string>
     */
    public static function tokenize(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        // Split by whitespace and common punctuation (English, Arabic, Bengali danda)
        $tokens = preg_split('/[\s,;:!?।—\-_\(\)\[\]\{\}"\'`«»]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $result = [];
        foreach ($tokens as $token) {
            $token = trim($token);
            if (mb_strlen($token) < 2) {
                continue;
            }

            if (self::isArabic($token)) {
                $result[] = self::normalizeArabic($token);
            } else {
                $result[] = $token;
            }
        }

        return array_values(array_unique($result));
    }
}
