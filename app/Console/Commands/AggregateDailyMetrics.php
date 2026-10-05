<?php

namespace App\Console\Commands;

use App\Models\DailyMetric;
use App\Models\Enrollment;
use App\Models\LearningEvent;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AggregateDailyMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:aggregate {--date= : The date to aggregate in YYYY-MM-DD format. Defaults to yesterday.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate learning events, users, and revenue into daily metrics (DAU/WAU/MAU, funnel, enrollments)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateInput = $this->option('date');
        $targetDate = $dateInput ? Carbon::parse($dateInput)->toDateString() : now()->subDay()->toDateString();

        $startOfDay = Carbon::parse($targetDate)->startOfDay();
        $endOfDay = Carbon::parse($targetDate)->endOfDay();

        $this->info("Aggregating daily metrics for: {$targetDate}");

        // 1. DAU: Unique active users on this day
        $dau = (int) LearningEvent::whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // 2. WAU: Unique active users in trailing 7 days
        $startOfWau = Carbon::parse($targetDate)->subDays(6)->startOfDay();
        $wau = (int) LearningEvent::whereBetween('occurred_at', [$startOfWau, $endOfDay])
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // 3. MAU: Unique active users in trailing 30 days
        $startOfMau = Carbon::parse($targetDate)->subDays(29)->startOfDay();
        $mau = (int) LearningEvent::whereBetween('occurred_at', [$startOfMau, $endOfDay])
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // 4. New users registered
        $newUsers = (int) User::whereBetween('created_at', [$startOfDay, $endOfDay])->count();

        // 5. Enrollments on this day
        $eventEnrollments = (int) LearningEvent::where('event_type', 'course_enrolled')
            ->whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->count();
        $dbEnrollments = (int) Enrollment::whereBetween('created_at', [$startOfDay, $endOfDay])->count();
        $enrollments = max($eventEnrollments, $dbEnrollments);

        // 6. Course completions
        $eventCompletions = (int) LearningEvent::where('event_type', 'course_completed')
            ->whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->count();
        $dbCompletions = (int) Enrollment::where('status', 'completed')
            ->whereBetween('completed_at', [$startOfDay, $endOfDay])
            ->count();
        $completions = max($eventCompletions, $dbCompletions);

        // 7. Completion rate percentage
        $completionRate = $enrollments > 0 ? round(($completions / $enrollments) * 100, 2) : 0;

        // 8. Revenue from successful orders
        $orderRevenue = (float) Order::whereIn('status', ['paid', 'approved', 'completed'])
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        // 9. Refunds
        $refunds = (float) PaymentTransaction::where('type', 'refund')
            ->where('status', 'refunded')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->sum('amount');

        // 10. Funnel steps
        $searches = (int) LearningEvent::where('event_type', 'search_performed')
            ->whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->count();
        $checkoutStarted = (int) LearningEvent::where('event_type', 'checkout_started')
            ->whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->count();
        $checkoutCompleted = (int) LearningEvent::where('event_type', 'checkout_completed')
            ->whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->count();
        $lessonsStarted = (int) LearningEvent::where('event_type', 'lesson_started')
            ->whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->count();
        $lessonsCompleted = (int) LearningEvent::where('event_type', 'lesson_completed')
            ->whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->count();

        $funnelData = [
            'searches' => $searches,
            'checkout_started' => $checkoutStarted,
            'checkout_completed' => $checkoutCompleted,
            'enrollments' => $enrollments,
            'lessons_started' => $lessonsStarted,
            'lessons_completed' => $lessonsCompleted,
        ];

        // 11. Top courses active on this day
        $topCourses = LearningEvent::whereBetween('occurred_at', [$startOfDay, $endOfDay])
            ->whereNotNull('course_id')
            ->selectRaw('course_id, count(*) as event_count')
            ->groupBy('course_id')
            ->orderByDesc('event_count')
            ->limit(5)
            ->get()
            ->pluck('event_count', 'course_id')
            ->toArray();

        // Save into daily_metrics
        $metricsToStore = [
            'dau' => ['value' => $dau, 'breakdown' => ['active_users' => $dau]],
            'wau' => ['value' => $wau, 'breakdown' => ['active_users_7d' => $wau]],
            'mau' => ['value' => $mau, 'breakdown' => ['active_users_30d' => $mau]],
            'new_users' => ['value' => $newUsers, 'breakdown' => null],
            'enrollments' => ['value' => $enrollments, 'breakdown' => null],
            'course_completions' => ['value' => $completions, 'breakdown' => null],
            'completion_rate' => ['value' => $completionRate, 'breakdown' => null],
            'revenue' => ['value' => $orderRevenue, 'breakdown' => null],
            'refunds' => ['value' => $refunds, 'breakdown' => null],
            'conversion_funnel' => ['value' => $checkoutCompleted, 'breakdown' => $funnelData],
            'top_courses' => ['value' => count($topCourses), 'breakdown' => $topCourses],
        ];

        foreach ($metricsToStore as $key => $data) {
            DailyMetric::updateOrCreate(
                ['date' => $targetDate, 'metric_key' => $key],
                [
                    'metric_value' => $data['value'],
                    'breakdown' => $data['breakdown'],
                ]
            );
        }

        $this->info("Daily metrics stored successfully for {$targetDate}: DAU={$dau}, WAU={$wau}, MAU={$mau}, Revenue=৳{$orderRevenue}");

        return self::SUCCESS;
    }
}
