<?php

namespace App\Http\Controllers;

use App\Services\LocalizationService;
use Illuminate\Http\Request;

class LocalizationController extends Controller
{
    public function __construct(
        protected LocalizationService $localizationService
    ) {}

    /**
     * Switch user/session application language.
     * Route: GET /locale/{locale}
     */
    public function switchLocale(Request $request, string $locale)
    {
        abort_unless($this->localizationService->isLocaleSupported($locale), 400, 'অনুপলব্ধ ভাষা নির্বাচন করা হয়েছে।');

        app()->setLocale($locale);
        session(['locale' => $locale]);

        if ($user = $request->user()) {
            $user->update(['preferred_locale' => $locale]);
        }

        // Auto-switch currency based on language if not explicitly chosen
        if (! $request->session()->has('user_custom_currency')) {
            $currency = ($locale === 'bn') ? 'BDT' : 'USD';
            session(['currency' => $currency]);
        }

        $cookie = cookie()->make('taallum_locale', $locale, 60 * 24 * 365);

        return back()->withCookie($cookie);
    }

    /**
     * Switch currency (BDT or USD).
     * Route: GET /currency/{currency}
     */
    public function switchCurrency(Request $request, string $currency)
    {
        $upper = strtoupper($currency);
        abort_unless(array_key_exists($upper, LocalizationService::SUPPORTED_CURRENCIES), 400, 'অনুপলব্ধ মুদ্রা নির্বাচন করা হয়েছে।');

        session(['currency' => $upper, 'user_custom_currency' => true]);

        if ($user = $request->user()) {
            $user->update(['preferred_currency' => $upper]);
        }

        return back();
    }
}
