<?php

namespace App\Providers;

use App\AI\Contracts\EmbeddingClient;
use App\AI\Contracts\LlmClient;
use App\AI\Drivers\FakeEmbeddingClient;
use App\AI\Drivers\FakeLlmClient;
use App\AI\Drivers\GeminiEmbeddingClient;
use App\AI\Drivers\GeminiLlmClient;
use App\Contracts\RecommendationService;
use App\Contracts\SearchEngine;
use App\Services\Recommendation\StatisticalRecommendationService;
use App\Services\Search\DatabaseSearchEngine;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
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

        $this->app->singleton(SearchEngine::class, function () {
            return new DatabaseSearchEngine;
        });

        $this->app->singleton(LlmClient::class, function () {
            $provider = config('ai.provider', 'gemini');
            if ($provider === 'fake' || app()->environment('testing')) {
                return new FakeLlmClient;
            }

            return new GeminiLlmClient;
        });

        $this->app->singleton(EmbeddingClient::class, function () {
            $provider = config('ai.provider', 'gemini');
            if ($provider === 'fake' || app()->environment('testing')) {
                return new FakeEmbeddingClient;
            }

            return new GeminiEmbeddingClient;
        });

        $this->app->singleton(RecommendationService::class, function ($app) {
            $embeddingClient = $app->make(EmbeddingClient::class);

            return new StatisticalRecommendationService($embeddingClient);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || str_starts_with(config('app.url', ''), 'https://')) {
            URL::forceScheme('https');
        }

        // Gate for OpenAPI / Scramble documentation access
        Gate::define('viewApiDocs', function ($user = null) {
            if (app()->environment('local', 'testing')) {
                return true;
            }

            return $user && $user->isAdmin();
        });

        // Prevent lazy loading in non-production to eliminate N+1 queries
        Model::preventLazyLoading(! app()->isProduction());

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

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('api_auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
