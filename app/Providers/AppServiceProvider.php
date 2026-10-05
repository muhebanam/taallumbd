<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once __DIR__.'/../helpers.php';
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || str_starts_with(config('app.url', ''), 'https://')) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        // Security password defaults
        Password::defaults(function () {
            $rule = Password::min(8)->letters()->numbers();

            return app()->isProduction() ? $rule->uncompromised() : $rule;
        });

        // Slow query logging (> 500ms)
        DB::listen(function ($query) {
            if ($query->time > 500) {
                Log::warning("Slow database query ({$query->time}ms): {$query->sql}", [
                    'time_ms' => $query->time,
                ]);
            }
        });

        // Rate Limiters
        RateLimiter::for('internal_cron', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.$request->input('email'));
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perHour(10)->by($request->ip());
        });

        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(15)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('manual_payment', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('coupon_check', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('fatwa_ask', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('forum_write', function (Request $request) {
            return Limit::perMinute(6)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('contact_form', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });
    }
}
