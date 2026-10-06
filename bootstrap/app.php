<?php

use App\Http\Middleware\CaptureUtmParameters;
use App\Http\Middleware\EnsureOrganizationMember;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\IdentifyTenant;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
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
            'events',
            'events/*',
            'api/*',
            'api/v1/*',
        ]);
        $middleware->web(append: [
            SecurityHeaders::class,
            CaptureUtmParameters::class,
            SetLocale::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->alias([
            'role' => EnsureRole::class,
            'tenant' => IdentifyTenant::class,
            'org.member' => EnsureOrganizationMember::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'প্রদত্ত তথ্যে ত্রুটি রয়েছে।',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'অননুমোদিত অ্যাক্সেস। অনুগ্রহ করে লগইন করুন।',
                ], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'এই রিসোর্সে আপনার অ্যাক্সেসের অনুমতি নেই।',
                ], 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'অনুরোধকৃত রিসোর্সটি পাওয়া যায়নি।',
                ], 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'অনুরোধকৃত এন্ডপয়েন্ট বা রিসোর্সটি পাওয়া যায়নি।',
                ], 404);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'অতিরিক্ত অনুরোধ পাঠানো হয়েছে। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।',
                ], 429);
            }
        });
    })->create();
