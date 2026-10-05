<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    /**
     * Handle an incoming request and capture UTM / Referral attribution.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Capture UTM parameters
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        $utmData = [];

        foreach ($utmKeys as $key) {
            if ($request->has($key)) {
                $utmData[$key] = substr((string) $request->query($key), 0, 100);
            }
        }

        if (! empty($utmData)) {
            $existing = $request->session()->get('utm_attribution', []);
            $request->session()->put('utm_attribution', array_merge($existing, $utmData));
        }

        // 2. Capture Referral Code
        if ($request->has('ref')) {
            $refCode = substr((string) $request->query('ref'), 0, 32);
            $request->session()->put('referral_code', $refCode);
        }

        return $next($request);
    }
}
