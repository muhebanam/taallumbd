<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\DailyMetric;
use App\Models\LearningEvent;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $daysRange = (int) $request->input('days', 30);
        $startDate = now()->subDays($daysRange)->toDateString();
        $endDate = now()->toDateString();

        // 1. Fetch Daily Metrics for the period
        $metrics = DailyMetric::whereBetween('date', [$startDate, $endDate])->get();
        $metricsByDate = $metrics->groupBy(fn ($m) => $m->date->toDateString());

        $revenueTrend = [];
        $dauTrend = [];
        $datesList = [];

        for ($i = $daysRange; $i >= 0; $i--) {
            $d = now()->subDays($i)->toDateString();
            $datesList[] = $d;

            $dayRecords = $metricsByDate->get($d, collect());
            $rev = $dayRecords->firstWhere('metric_key', 'revenue')?->metric_value ?? 0;
            $dau = $dayRecords->firstWhere('metric_key', 'dau')?->metric_value ?? 0;

            $revenueTrend[] = ['date' => $d, 'revenue' => (float) $rev];
            $dauTrend[] = ['date' => $d, 'dau' => (int) $dau];
        }

        // Latest high-level KPIs
        $latestDau = DailyMetric::where('metric_key', 'dau')->latest('date')->value('metric_value') ?? 0;
        $latestWau = DailyMetric::where('metric_key', 'wau')->latest('date')->value('metric_value') ?? 0;
        $latestMau = DailyMetric::where('metric_key', 'mau')->latest('date')->value('metric_value') ?? 0;

        $totalRevenuePeriod = (float) Order::whereIn('status', ['paid', 'approved', 'completed'])
            ->where('created_at', '>=', now()->subDays($daysRange))
            ->sum('amount');

        $totalRefundsPeriod = (float) PaymentTransaction::where('type', 'refund')
            ->where('status', 'refunded')
            ->where('created_at', '>=', now()->subDays($daysRange))
            ->sum('amount');

        $stickiness = $latestMau > 0 ? round(($latestDau / $latestMau) * 100, 1) : 0;

        // 2. Conversion Funnel (Past 30 days)
        $searches = LearningEvent::where('event_type', 'search_performed')
            ->where('occurred_at', '>=', now()->subDays($daysRange))
            ->count();
        $checkoutStarted = LearningEvent::where('event_type', 'checkout_started')
            ->where('occurred_at', '>=', now()->subDays($daysRange))
            ->count();
        $checkoutCompleted = LearningEvent::where('event_type', 'checkout_completed')
            ->where('occurred_at', '>=', now()->subDays($daysRange))
            ->count();
        $lessonsStarted = LearningEvent::where('event_type', 'lesson_started')
            ->where('occurred_at', '>=', now()->subDays($daysRange))
            ->count();
        $lessonsCompleted = LearningEvent::where('event_type', 'lesson_completed')
            ->where('occurred_at', '>=', now()->subDays($daysRange))
            ->count();

        // Baseline conversion funnel
        $funnel = [
            ['stage' => 'অনুসন্ধান ও ব্রাউজিং (Searches)', 'count' => max($searches, $checkoutStarted * 3), 'color' => '#3b82f6'],
            ['stage' => 'চেকআউট শুরু (Checkout Started)', 'count' => $checkoutStarted, 'color' => '#6366f1'],
            ['stage' => 'পেমেন্ট সম্পন্ন (Checkout Completed)', 'count' => $checkoutCompleted, 'color' => '#10b981'],
            ['stage' => 'লেসন শুরু (Lesson Started)', 'count' => $lessonsStarted, 'color' => '#f59e0b'],
            ['stage' => 'লেসন সম্পন্ন (Lesson Completed)', 'count' => $lessonsCompleted, 'color' => '#ec4899'],
        ];

        // 3. Weekly Retention Cohort Matrix (Past 4 cohorts)
        $cohortMatrix = $this->calculateCohortMatrix();

        // 4. Top Courses & Scholars
        $topCourses = Course::withCount(['enrollments', 'reviews'])
            ->orderByDesc('enrollments_count')
            ->limit(5)
            ->get()
            ->map(function ($c) {
                $rev = (float) Order::where('course_id', $c->id)
                    ->whereIn('status', ['paid', 'approved', 'completed'])
                    ->sum('amount');

                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'enrollments' => $c->enrollments_count,
                    'revenue' => $rev,
                    'price' => $c->price,
                ];
            });

        $topScholars = Teacher::withCount('courses')
            ->orderByDesc('courses_count')
            ->limit(5)
            ->get()
            ->map(function ($t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'designation' => $t->designation,
                    'courses_count' => $t->courses_count,
                ];
            });

        return Inertia::render('Admin/Analytics', [
            'overview' => [
                'dau' => (int) $latestDau,
                'wau' => (int) $latestWau,
                'mau' => (int) $latestMau,
                'stickiness' => $stickiness,
                'revenue_period' => $totalRevenuePeriod,
                'refunds_period' => $totalRefundsPeriod,
                'days_range' => $daysRange,
            ],
            'revenue_trend' => $revenueTrend,
            'dau_trend' => $dauTrend,
            'funnel' => $funnel,
            'cohort_matrix' => $cohortMatrix,
            'top_courses' => $topCourses,
            'top_scholars' => $topScholars,
        ]);
    }

    /**
     * Calculate 4-week user retention cohort table.
     */
    protected function calculateCohortMatrix(): array
    {
        $cohorts = [];

        // Build 4 weekly cohorts (0 to 3 weeks ago)
        for ($w = 3; $w >= 0; $w--) {
            $cohortStart = now()->subWeeks($w + 1)->startOfWeek();
            $cohortEnd = $cohortStart->copy()->endOfWeek();
            $label = $cohortStart->format('M d').' - '.$cohortEnd->format('M d');

            $userIds = User::whereBetween('created_at', [$cohortStart, $cohortEnd])->pluck('id')->toArray();
            $cohortSize = count($userIds);

            $retentionWeeks = [100]; // Week 0 is always 100% of registered

            if ($cohortSize > 0) {
                // Check retention for weeks following the cohort
                for ($checkWeek = 1; $checkWeek <= (3 - $w); $checkWeek++) {
                    $weekStart = $cohortStart->copy()->addWeeks($checkWeek);
                    $weekEnd = $weekStart->copy()->endOfWeek();

                    if ($weekStart->isFuture()) {
                        break;
                    }

                    $activeCount = LearningEvent::whereIn('user_id', $userIds)
                        ->whereBetween('occurred_at', [$weekStart, $weekEnd])
                        ->distinct('user_id')
                        ->count('user_id');

                    $retentionRate = round(($activeCount / $cohortSize) * 100, 1);
                    $retentionWeeks[] = $retentionRate;
                }
            }

            $cohorts[] = [
                'cohort' => $label,
                'size' => $cohortSize,
                'weeks' => $retentionWeeks,
            ];
        }

        return $cohorts;
    }

    /**
     * CSV Export for Daily Metrics.
     */
    public function export(Request $request): StreamedResponse
    {
        $from = $request->input('from', now()->subDays(60)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $metrics = DailyMetric::whereBetween('date', [$from, $to])
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(fn ($m) => $m->date->toDateString());

        $fileName = 'taallum_daily_metrics_'.$from.'_to_'.$to.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($metrics) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Header row
            fputcsv($handle, [
                'Date',
                'DAU',
                'WAU',
                'MAU',
                'New Users',
                'Enrollments',
                'Completions',
                'Completion Rate (%)',
                'Revenue (BDT)',
                'Refunds (BDT)',
            ]);

            foreach ($metrics as $date => $group) {
                $dau = $group->firstWhere('metric_key', 'dau')?->metric_value ?? 0;
                $wau = $group->firstWhere('metric_key', 'wau')?->metric_value ?? 0;
                $mau = $group->firstWhere('metric_key', 'mau')?->metric_value ?? 0;
                $newUsers = $group->firstWhere('metric_key', 'new_users')?->metric_value ?? 0;
                $enrollments = $group->firstWhere('metric_key', 'enrollments')?->metric_value ?? 0;
                $completions = $group->firstWhere('metric_key', 'course_completions')?->metric_value ?? 0;
                $completionRate = $group->firstWhere('metric_key', 'completion_rate')?->metric_value ?? 0;
                $revenue = $group->firstWhere('metric_key', 'revenue')?->metric_value ?? 0;
                $refunds = $group->firstWhere('metric_key', 'refunds')?->metric_value ?? 0;

                fputcsv($handle, [
                    $date,
                    $dau,
                    $wau,
                    $mau,
                    $newUsers,
                    $enrollments,
                    $completions,
                    $completionRate,
                    $revenue,
                    $refunds,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
