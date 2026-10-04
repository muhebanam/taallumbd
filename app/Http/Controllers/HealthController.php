<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * Comprehensive system health check endpoint.
     */
    public function check(): JsonResponse
    {
        $status = 'healthy';
        $checks = [];

        // 1. Database Check
        try {
            $dbStart = microtime(true);
            DB::select('SELECT 1');
            $checks['database'] = [
                'status' => 'ok',
                'latency_ms' => round((microtime(true) - $dbStart) * 1000, 2),
            ];
        } catch (Throwable $e) {
            $status = 'unhealthy';
            $checks['database'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        // 2. Cache Check
        try {
            $cacheKey = 'health_test_key_'.time();
            Cache::put($cacheKey, 'ok', 10);
            $val = Cache::get($cacheKey);
            Cache::forget($cacheKey);

            $checks['cache'] = [
                'status' => $val === 'ok' ? 'ok' : 'mismatch',
                'driver' => config('cache.default'),
            ];
        } catch (Throwable $e) {
            $status = 'degraded';
            $checks['cache'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }

        // 3. Queue backlog
        try {
            $queueCount = DB::table('jobs')->count();
            $failedQueueCount = DB::table('failed_jobs')->count();
            $checks['queue'] = [
                'status' => 'ok',
                'pending_jobs' => $queueCount,
                'failed_jobs' => $failedQueueCount,
            ];
        } catch (Throwable $e) {
            $checks['queue'] = [
                'status' => 'unknown',
                'message' => $e->getMessage(),
            ];
        }

        // 4. Last Cron Run
        $checks['last_cron_run'] = Cache::get('internal:cron:last_run', 'never');

        $httpCode = $status === 'healthy' ? 200 : ($status === 'degraded' ? 200 : 503);

        return response()->json([
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
            'app_env' => app()->environment(),
            'php_version' => PHP_VERSION,
            'checks' => $checks,
        ], $httpCode);
    }
}
