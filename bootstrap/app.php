<?php

use App\Http\Middleware\CaptureUtmParameters;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::get('/init-neon-db', function () {
                @set_time_limit(300);
                $log = [];
                $log[] = 'Taallum BD - Neon PostgreSQL Database Initializer';
                $log[] = 'Timestamp: '.date('Y-m-d H:i:s T');
                $log[] = '--------------------------------------------------';

                // Ensure Direct Connection (PgBouncer pooler fails on DDL migrations)
                $dbUrl = config('database.connections.pgsql.url') ?: env('DATABASE_URL', '');
                if (str_contains($dbUrl, '-pooler.')) {
                    $directUrl = str_replace('-pooler.', '.', $dbUrl);
                    config(['database.connections.pgsql.url' => $directUrl]);
                    DB::purge('pgsql');
                    DB::reconnect('pgsql');
                    $log[] = 'Converted connection to DIRECT Neon endpoint (bypassing PgBouncer pooler).';
                }

                // Step 1: Run Migrations
                try {
                    $log[] = "\n[1/2] RUNNING MIGRATIONS (php artisan migrate --force)...";
                    Artisan::call('migrate', ['--force' => true]);
                    $log[] = Artisan::output();
                    $log[] = '>>> MIGRATIONS COMPLETED SUCCESSFULLY! <<<';
                } catch (Throwable $e) {
                    $log[] = '>>> MIGRATION ERROR: '.$e->getMessage();
                    $log[] = $e->getTraceAsString();
                }

                // Step 2: Run Seeders
                try {
                    $log[] = "\n[2/2] RUNNING SEEDERS (php artisan db:seed --force)...";
                    Artisan::call('db:seed', ['--force' => true]);
                    $log[] = Artisan::output();
                    $log[] = '>>> SEEDERS COMPLETED SUCCESSFULLY! <<<';
                } catch (Throwable $e) {
                    $log[] = '>>> SEEDER NOTE: '.$e->getMessage();
                }

                $text = implode("\n", $log);

                return response("<pre style='font-family: Menlo, Consolas, Monaco, monospace; background: #0f172a; color: #38bdf8; padding: 24px; font-size: 13px; line-height: 1.6; border-radius: 8px; overflow-x: auto;'>".e($text).'</pre>', 200)
                    ->header('Content-Type', 'text/html; charset=UTF-8');
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'init-neon-db',
            'init-neon-db/*',
            'internal/cron',
            'webhooks/*',
            'payments/*/callback/*',
            'payments/*/callback',
        ]);
        $middleware->web(append: [
            SecurityHeaders::class,
            CaptureUtmParameters::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
