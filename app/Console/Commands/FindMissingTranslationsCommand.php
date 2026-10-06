<?php

namespace App\Console\Commands;

use App\Services\LocalizationService;
use Illuminate\Console\Command;

class FindMissingTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'taallum:missing-translations {--locale=all : Specific locale to audit (en, ar, or all)}';

    /**
     * The console command description.
     */
    protected $description = 'Find missing translation keys across Bengali, English, and Arabic locales';

    /**
     * Execute the console command.
     */
    public function handle(LocalizationService $localizationService): int
    {
        $this->info('Auditing Taallum BD translation keys...');

        $baseLocale = 'bn';
        $baseDict = $localizationService->getTranslationsDictionary($baseLocale);

        if (empty($baseDict)) {
            $this->error('Base translation dictionary (bn) is empty or not found in lang/bn.json');

            return 1;
        }

        $targetLocales = $this->option('locale') === 'all'
            ? ['en', 'ar']
            : [$this->option('locale')];

        $totalMissing = 0;
        $auditResults = [];

        foreach ($targetLocales as $locale) {
            if (! $localizationService->isLocaleSupported($locale) && $locale !== 'all') {
                $this->warn("Unsupported target locale: {$locale}");

                continue;
            }

            if ($locale === $baseLocale) {
                continue;
            }

            $targetDict = $localizationService->getTranslationsDictionary($locale);
            $missingKeys = [];

            foreach ($baseDict as $key => $baseValue) {
                if (! array_key_exists($key, $targetDict) || trim($targetDict[$key]) === '') {
                    $missingKeys[] = $key;
                }
            }

            $missingCount = count($missingKeys);
            $totalMissing += $missingCount;

            $auditResults[] = [
                'locale' => $locale,
                'name' => LocalizationService::SUPPORTED_LOCALES[$locale]['name'] ?? $locale,
                'total_base_keys' => count($baseDict),
                'translated_keys' => count($targetDict),
                'missing_count' => $missingCount,
                'missing_keys_sample' => $missingCount > 0 ? implode(', ', array_slice($missingKeys, 0, 5)) : 'None (100% complete)',
            ];
        }

        $this->table(
            ['Locale', 'Language', 'Base Keys (bn)', 'Target Keys', 'Missing', 'Sample Missing Keys'],
            $auditResults
        );

        if ($totalMissing === 0) {
            $this->info('Alhamdulillah! All translation keys are 100% translated across all target locales.');

            return 0;
        }

        $this->warn("Found {$totalMissing} missing translation keys across audited locales.");

        return 0;
    }
}
