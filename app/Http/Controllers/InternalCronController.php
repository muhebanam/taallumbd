<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Throwable;

class InternalCronController extends Controller
{
    /**
     * Run scheduled tasks and process queued jobs in a single request.
     * Intended to be invoked by external cron service (e.g., cron-job.org) every 1-5 minutes.
     */
    public function run(Request $request): JsonResponse
    {
        $cronSecret = config('app.cron_secret') ?: env('CRON_SECRET');

        // Security token verification
        $token = $request->header('X-Cron-Token') ?: $request->query('token');

        if (empty($cronSecret) || empty($token) || ! hash_equals($cronSecret, (string) $token)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized cron request.',
            ], 403);
        }

        // Prevent overlapping runs within 50 seconds
        $lock = Cache::lock('internal:cron:lock', 50);

        if (! $lock->get()) {
            return response()->json([
                'status' => 'busy',
                'message' => 'Another cron cycle is currently executing.',
            ], 429);
        }

        $start = microtime(true);
        $output = [];

        try {
            // 1. Run Laravel Task Scheduler
            Artisan::call('schedule:run');
            $output['schedule'] = trim(Artisan::output());

            // 2. Process Queue jobs up to 40 seconds
            Artisan::call('queue:work', [
                '--stop-when-empty' => true,
                '--max-time' => 40,
                '--tries' => 3,
            ]);
            $output['queue'] = trim(Artisan::output());

            Cache::forever('internal:cron:last_run', now()->toIso8601String());

            return response()->json([
                'status' => 'success',
                'executed_at' => now()->toIso8601String(),
                'duration_seconds' => round(microtime(true) - $start, 2),
                'details' => $output,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], 500);
        } finally {
            $lock->release();
        }
    }
}
