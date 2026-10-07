<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        // 3. Storage Check
        try {
            $diskName = config('filesystems.default', 'local');
            $testFile = 'health_check_test_'.time().'.txt';
            Storage::disk($diskName)->put($testFile, 'health-ok');
            $read = Storage::disk($diskName)->get($testFile);
            Storage::disk($diskName)->delete($testFile);

            $checks['storage'] = [
                'status' => $read === 'health-ok' ? 'ok' : 'mismatch',
                'disk' => $diskName,
            ];
        } catch (Throwable $e) {
            $status = 'degraded';
            $checks['storage'] = [
                'status' => 'error',
                'message' => $e->getMessage(),
                'disk' => config('filesystems.default', 'local'),
            ];
        }

        // 4. Queue backlog
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

        // 5. Disk Space & Capacity
        try {
            $freeSpace = @disk_free_space(storage_path());
            $totalSpace = @disk_total_space(storage_path());
            if ($freeSpace !== false && $totalSpace !== false && $totalSpace > 0) {
                $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 1);
                $checks['disk_space'] = [
                    'status' => $usedPercent > 90 ? 'warning' : 'ok',
                    'used_percent' => $usedPercent,
                    'free_mb' => round($freeSpace / (1024 * 1024), 1),
                    'total_mb' => round($totalSpace / (1024 * 1024), 1),
                ];
            }
        } catch (Throwable $e) {
            // Ignore if restricted in shared environment
        }

        // 6. Payment Gateways Readiness
        $checks['payment_gateways'] = [
            'stripe' => ! empty(config('payments.gateways.stripe.secret')) ? 'configured' : 'sandbox_default',
            'bkash' => ! empty(config('payments.gateways.bkash.app_key')) ? 'configured' : 'sandbox_default',
            'sslcommerz' => ! empty(config('payments.gateways.sslcommerz.store_id')) ? 'configured' : 'sandbox_default',
            'manual' => 'ready',
        ];

        // 7. Last Cron Run
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
