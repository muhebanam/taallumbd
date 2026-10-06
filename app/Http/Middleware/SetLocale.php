<?php

namespace App\Http\Middleware;

use App\Services\LocalizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(
        protected LocalizationService $localizationService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        app()->setLocale($locale);
        session(['locale' => $locale]);

        // Currency resolution
        $currency = $this->determineCurrency($request, $locale);
        session(['currency' => $currency]);

        $response = $next($request);

        // Queue cookie so next visit preserves language preference (use final app locale)
        $finalLocale = app()->getLocale();

        return $response->withCookie(cookie()->make('taallum_locale', $finalLocale, 60 * 24 * 365));
    }

    /**
     * Determine preferred locale from URL segment, query param, session, user, or Accept-Language header.
     */
    protected function determineLocale(Request $request): string
    {
        // 0. Explicit locale route (e.g. /locale/en or /locale/ar)
        if ($request->is('locale/*')) {
            $switchLocale = $request->segment(2);
            if ($switchLocale && $this->localizationService->isLocaleSupported($switchLocale)) {
                return $switchLocale;
            }
        }

        // 1. URL prefix (e.g. /en/... or /ar/...)
        $firstSegment = $request->segment(1);
        if ($firstSegment && $this->localizationService->isLocaleSupported($firstSegment)) {
            return $firstSegment;
        }

        // 2. Explicit query param (e.g. ?lang=ar or ?locale=en)
        $queryLocale = $request->query('lang') ?: $request->query('locale');
        if ($queryLocale && $this->localizationService->isLocaleSupported($queryLocale)) {
            return $queryLocale;
        }

        // 3. User preference if authenticated
        $user = $request->user();
        if ($user && ! empty($user->preferred_locale) && $this->localizationService->isLocaleSupported($user->preferred_locale)) {
            return $user->preferred_locale;
        }

        // 4. Session or Cookie
        $sessionLocale = $request->session()->get('locale') ?: $request->cookie('taallum_locale');
        if ($sessionLocale && $this->localizationService->isLocaleSupported($sessionLocale)) {
            return $sessionLocale;
        }

        // 5. Accept-Language header negotiation (e.g. Arabic preferred visitors)
        $header = $request->header('Accept-Language');
        if ($header) {
            $parsed = strtolower($header);
            if (str_starts_with($parsed, 'ar') || str_contains($parsed, 'ar-') || str_contains($parsed, 'ar;')) {
                return 'ar';
            }
        }

        // Default
        return LocalizationService::DEFAULT_LOCALE;
    }

    /**
     * Determine active currency (BDT or USD).
     */
    protected function determineCurrency(Request $request, string $locale): string
    {
        // Explicit query param
        $queryCurrency = $request->query('currency');
        if ($queryCurrency && array_key_exists(strtoupper($queryCurrency), LocalizationService::SUPPORTED_CURRENCIES)) {
            return strtoupper($queryCurrency);
        }

        // User preference
        $user = $request->user();
        if ($user && ! empty($user->preferred_currency) && array_key_exists(strtoupper($user->preferred_currency), LocalizationService::SUPPORTED_CURRENCIES)) {
            return strtoupper($user->preferred_currency);
        }

        // Session
        $sessionCurrency = $request->session()->get('currency');
        if ($sessionCurrency && array_key_exists(strtoupper($sessionCurrency), LocalizationService::SUPPORTED_CURRENCIES)) {
            return strtoupper($sessionCurrency);
        }

        // Auto default based on locale: Bangla -> BDT, English / Arabic -> USD
        return ($locale === 'bn') ? 'BDT' : 'USD';
    }
}
