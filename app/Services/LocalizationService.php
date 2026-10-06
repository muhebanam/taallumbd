<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class LocalizationService
{
    public const DEFAULT_LOCALE = 'bn';

    public const DEFAULT_CURRENCY = 'BDT';

    public const SUPPORTED_LOCALES = [
        'bn' => [
            'name' => 'বাংলা',
            'native' => 'বাংলা',
            'dir' => 'ltr',
            'flag' => '🇧🇩',
        ],
        'en' => [
            'name' => 'English',
            'native' => 'English',
            'dir' => 'ltr',
            'flag' => '🇬🇧',
        ],
        'ar' => [
            'name' => 'العربية',
            'native' => 'العربية',
            'dir' => 'rtl',
            'flag' => '🇸🇦',
        ],
    ];

    public const SUPPORTED_CURRENCIES = [
        'BDT' => [
            'code' => 'BDT',
            'symbol' => '৳',
            'name' => 'বাংলাদেশী টাকা',
        ],
        'USD' => [
            'code' => 'USD',
            'symbol' => '$',
            'name' => 'US Dollar',
        ],
    ];

    /**
     * Standard Islamic Hijri month names across 3 languages.
     */
    protected const HIJRI_MONTHS = [
        'bn' => [
            1 => 'মুহাররম', 2 => 'সফর', 3 => 'রবিউল আউয়াল', 4 => 'রবিউস সানি',
            5 => 'জুমাদাল উলা', 6 => 'জুমাদাস সানিয়া', 7 => 'রজব', 8 => 'শাবান',
            9 => 'রমাদান', 10 => 'শাওয়াল', 11 => 'জিলকদ', 12 => 'জিলহজ্জ',
        ],
        'en' => [
            1 => 'Muharram', 2 => 'Safar', 3 => 'Rabi al-Awwal', 4 => 'Rabi al-Thani',
            5 => 'Jumada al-Ula', 6 => 'Jumada al-Akhirah', 7 => 'Rajab', 8 => "Sha'ban",
            9 => 'Ramadan', 10 => 'Shawwal', 11 => "Dhu al-Qa'dah", 12 => 'Dhu al-Hijjah',
        ],
        'ar' => [
            1 => 'محرم', 2 => 'صفر', 3 => 'ربيع الأول', 4 => 'ربيع الآخر',
            5 => 'جمادى الأولى', 6 => 'جمادى الآخرة', 7 => 'رجب', 8 => 'شعبان',
            9 => 'رمضان', 10 => 'شوال', 11 => 'ذو القعدة', 12 => 'ذو الحجة',
        ],
    ];

    /**
     * Check if a given locale is supported.
     */
    public function isLocaleSupported(string $locale): bool
    {
        return array_key_exists($locale, self::SUPPORTED_LOCALES);
    }

    /**
     * Get direction for given or active locale.
     */
    public function getDirection(?string $locale = null): string
    {
        $target = $locale ?: app()->getLocale();

        return ($target === 'ar') ? 'rtl' : 'ltr';
    }

    /**
     * Check if given or active locale is RTL.
     */
    public function isRtl(?string $locale = null): bool
    {
        return $this->getDirection($locale) === 'rtl';
    }

    /**
     * Format number/digits to active locale (Bengali, Arabic, English).
     */
    public function formatNumber(int|float|string $number, ?string $locale = null): string
    {
        $targetLocale = $locale ?: app()->getLocale();
        $str = (string) $number;

        if ($targetLocale === 'bn') {
            $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

            return str_replace($enDigits, $bnDigits, $str);
        }

        if ($targetLocale === 'ar') {
            $enDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $arDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

            return str_replace($enDigits, $arDigits, $str);
        }

        return $str;
    }

    /**
     * Format price with currency symbol and numeral localization.
     */
    public function formatPrice(float $amount, string $currency = 'BDT', ?string $locale = null): string
    {
        $targetLocale = $locale ?: app()->getLocale();
        $currency = strtoupper($currency);

        if ($currency === 'USD') {
            $formattedNumber = number_format($amount, 2);
            $localizedNumber = $this->formatNumber($formattedNumber, $targetLocale);

            return '$'.$localizedNumber;
        }

        // BDT default
        $formattedNumber = number_format($amount, 0);
        $localizedNumber = $this->formatNumber($formattedNumber, $targetLocale);

        if ($targetLocale === 'ar') {
            return $localizedNumber.' د.ب'; // or BDT in Arabic
        }

        return '৳'.$localizedNumber;
    }

    /**
     * Exchange rate calculator (1 USD = 120.00 BDT default).
     */
    public function getExchangeRate(string $from = 'BDT', string $to = 'USD'): float
    {
        $usdToBdt = (float) config('payments.exchange_rates.usd_to_bdt', 120.00);

        if (strtoupper($from) === 'USD' && strtoupper($to) === 'BDT') {
            return $usdToBdt;
        }

        if (strtoupper($from) === 'BDT' && strtoupper($to) === 'USD') {
            return 1.0 / $usdToBdt;
        }

        return 1.0;
    }

    /**
     * Convert currency amount.
     */
    public function convert(float $amount, string $from = 'BDT', string $to = 'USD'): float
    {
        if (strtoupper($from) === strtoupper($to)) {
            return $amount;
        }

        $rate = $this->getExchangeRate($from, $to);

        return round($amount * $rate, 2);
    }

    /**
     * Calculate and format Islamic Hijri date.
     * Uses Umm al-Qura algorithmic conversion with Julian Day Number.
     */
    public function getHijriDate(?Carbon $date = null, ?string $locale = null): string
    {
        $carbon = $date ?: Carbon::now();
        $targetLocale = $locale ?: app()->getLocale();

        [$hYear, $hMonth, $hDay] = $this->gregorianToHijri($carbon->year, $carbon->month, $carbon->day);

        $monthName = self::HIJRI_MONTHS[$targetLocale][$hMonth] ?? self::HIJRI_MONTHS['en'][$hMonth];

        $dayStr = $this->formatNumber($hDay, $targetLocale);
        $yearStr = $this->formatNumber($hYear, $targetLocale);

        return match ($targetLocale) {
            'bn' => "{$dayStr} {$monthName} {$yearStr} হিজরি",
            'ar' => "{$dayStr} {$monthName} {$yearStr} هـ",
            default => "{$dayStr} {$monthName} {$yearStr} AH",
        };
    }

    /**
     * Algorithmic conversion from Gregorian (Y, M, D) to Hijri (Y, M, D).
     * Accurate within +- 1 day globally.
     */
    public function gregorianToHijri(int $year, int $month, int $day): array
    {
        if ($month < 3) {
            $year -= 1;
            $month += 12;
        }

        $a = (int) floor($year / 100);
        $b = 2 - $a + (int) floor($a / 4);

        $jd = (int) floor(365.25 * ($year + 4716))
            + (int) floor(30.6001 * ($month + 1))
            + $day + $b - 1524.5;

        // Julian Day to Hijri
        $z = (int) floor($jd + 0.5);
        $l = $z - 1948440 + 10632;
        $n = (int) floor(($l - 1) / 10631);
        $l = $l - 10631 * $n + 354;
        $j = (int) (floor((10985 - $l) / 5316)) * (int) (floor((50 * $l) / 17719))
            + (int) (floor($l / 5670)) * (int) (floor((43 * $l) / 15238));
        $l = $l - (int) (floor((30 - $j) / 15)) * (int) (floor((17719 * $j) / 50))
            - (int) (floor($j / 16)) * (int) (floor((15238 * $j) / 43)) + 29;

        $m = (int) floor((24 * $l) / 709);
        $d = $l - (int) floor((709 * $m) / 24);
        $y = 30 * $n + $j - 30;

        return [(int) $y, (int) $m, (int) $d];
    }

    /**
     * Load flat translation dictionary for client-side Inertia rendering.
     */
    public function getTranslationsDictionary(string $locale): array
    {
        $translations = [];

        // 1. JSON file (e.g. lang/bn.json)
        $jsonPath = base_path("lang/{$locale}.json");
        if (File::exists($jsonPath)) {
            $decoded = json_decode(File::get($jsonPath), true);
            if (is_array($decoded)) {
                $translations = array_merge($translations, $decoded);
            }
        }

        // 2. PHP lang files inside lang/{locale}/*.php
        $dirPath = base_path("lang/{$locale}");
        if (File::isDirectory($dirPath)) {
            $files = File::files($dirPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    $prefix = $file->getFilenameWithoutExtension();
                    $data = require $file->getPathname();
                    if (is_array($data)) {
                        foreach ($data as $key => $val) {
                            if (is_string($val)) {
                                $translations["{$prefix}.{$key}"] = $val;
                            }
                        }
                    }
                }
            }
        }

        return $translations;
    }
}
