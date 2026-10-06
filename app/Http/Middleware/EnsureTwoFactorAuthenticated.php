<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // If user does not require 2FA, proceed
        if (! $user->requiresTwoFactor()) {
            return $next($request);
        }

        // Whitelisted route names
        $whitelistedRoutes = [
            'two-factor.setup',
            'two-factor.confirm',
            'two-factor.challenge',
            'two-factor.verify',
            'logout',
        ];

        if (in_array($request->route()?->getName(), $whitelistedRoutes, true)) {
            return $next($request);
        }

        // Case 1: 2FA not yet set up & confirmed -> Mandatory setup
        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup')->with(
                'warning',
                'অ্যাডমিন ও ইনস্ট্রাক্টর অ্যাকাউন্টের সুরক্ষার জন্য ২-ধাপ যাচাইকরণ (2FA/TOTP) বাধ্যতামূলক।'
            );
        }

        // Case 2: 2FA set up but not verified in current session
        if (! $request->session()->get('two_factor_verified')) {
            return redirect()->route('two-factor.challenge');
        }

        return $next($request);
    }
}
