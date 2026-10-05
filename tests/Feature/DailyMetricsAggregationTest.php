<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\DailyMetric;
use App\Models\Enrollment;
use App\Models\LearningEvent;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DailyMetricsAggregationTest extends TestCase
{
    use RefreshDatabase;

    public function test_aggregates_daily_metrics_accurately(): void
    {
        $testDate = '2026-10-04';
        $dayCarbon = Carbon::parse($testDate);

        // 1. Seed users created on testDate
        $user1 = User::factory()->create();
        User::where('id', $user1->id)->update(['created_at' => $dayCarbon->copy()->setHour(10)]);
        $user2 = User::factory()->create();
        User::where('id', $user2->id)->update(['created_at' => $dayCarbon->copy()->setHour(14)]);
        $oldUser = User::factory()->create();
        User::where('id', $oldUser->id)->update(['created_at' => $dayCarbon->copy()->subDays(10)]);

        // 2. Seed learning events on testDate
        LearningEvent::create([
            'user_id' => $user1->id,
            'event_type' => 'lesson_started',
            'course_id' => 1,
            'occurred_at' => $dayCarbon->copy()->setHour(11),
        ]);
        LearningEvent::create([
            'user_id' => $user1->id,
            'event_type' => 'lesson_completed',
            'course_id' => 1,
            'occurred_at' => $dayCarbon->copy()->setHour(12),
        ]);
        LearningEvent::create([
            'user_id' => $oldUser->id,
            'event_type' => 'quran_read',
            'occurred_at' => $dayCarbon->copy()->setHour(15),
        ]);
        // Funnel events
        LearningEvent::create([
            'user_id' => $user2->id,
            'event_type' => 'search_performed',
            'occurred_at' => $dayCarbon->copy()->setHour(9),
        ]);
        LearningEvent::create([
            'user_id' => $user2->id,
            'event_type' => 'checkout_started',
            'course_id' => 1,
            'occurred_at' => $dayCarbon->copy()->setHour(9)->setMinute(30),
        ]);
        LearningEvent::create([
            'user_id' => $user2->id,
            'event_type' => 'checkout_completed',
            'course_id' => 1,
            'occurred_at' => $dayCarbon->copy()->setHour(9)->setMinute(45),
        ]);

        // 3. Seed orders on testDate
        $course = Course::factory()->create(['price' => 1200]);
        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'user_id' => $user2->id,
            'course_id' => $course->id,
            'amount' => 1200.0,
            'status' => 'paid',
        ]);
        Order::where('id', $order->id)->update(['created_at' => $dayCarbon->copy()->setHour(10)]);

        $refundTrx = PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'bkash',
            'type' => 'refund',
            'amount' => 500.0,
            'currency' => 'BDT',
            'status' => 'refunded',
        ]);
        PaymentTransaction::where('id', $refundTrx->id)->update(['created_at' => $dayCarbon->copy()->setHour(16)]);

        // 4. Seed enrollment & completion
        $enrollment = Enrollment::create([
            'user_id' => $user2->id,
            'course_id' => $course->id,
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => $dayCarbon->copy()->setHour(18),
        ]);
        Enrollment::where('id', $enrollment->id)->update(['created_at' => $dayCarbon->copy()->setHour(10)]);

        // Run aggregation command for testDate
        $exitCode = Artisan::call('analytics:aggregate', ['--date' => $testDate]);
        $this->assertEquals(0, $exitCode);

        // Verify DAU (user1, oldUser, user2 were active on this date = 3)
        $dau = DailyMetric::where('date', $testDate)->where('metric_key', 'dau')->first();
        $this->assertNotNull($dau);
        $this->assertEquals(3, (int) $dau->metric_value);

        // Verify New Users (user1, user2 = 2)
        $newUsers = DailyMetric::where('date', $testDate)->where('metric_key', 'new_users')->first();
        $this->assertNotNull($newUsers);
        $this->assertEquals(2, (int) $newUsers->metric_value);

        // Verify Revenue (1200)
        $revenue = DailyMetric::where('date', $testDate)->where('metric_key', 'revenue')->first();
        $this->assertNotNull($revenue);
        $this->assertEquals(1200.0, (float) $revenue->metric_value);

        // Verify Refunds (500)
        $refunds = DailyMetric::where('date', $testDate)->where('metric_key', 'refunds')->first();
        $this->assertNotNull($refunds);
        $this->assertEquals(500.0, (float) $refunds->metric_value);

        // Verify Conversion Funnel JSON breakdown
        $funnel = DailyMetric::where('date', $testDate)->where('metric_key', 'conversion_funnel')->first();
        $this->assertNotNull($funnel);
        $this->assertIsArray($funnel->breakdown);
        $this->assertEquals(1, $funnel->breakdown['searches']);
        $this->assertEquals(1, $funnel->breakdown['checkout_started']);
        $this->assertEquals(1, $funnel->breakdown['checkout_completed']);
    }

    public function test_aggregation_is_idempotent(): void
    {
        $testDate = '2026-10-04';
        Artisan::call('analytics:aggregate', ['--date' => $testDate]);
        $firstCount = DailyMetric::where('date', $testDate)->count();

        // Run second time
        Artisan::call('analytics:aggregate', ['--date' => $testDate]);
        $secondCount = DailyMetric::where('date', $testDate)->count();

        $this->assertEquals($firstCount, $secondCount);
    }

    public function test_can_prune_old_learning_events_according_to_retention_policy(): void
    {
        $user = User::factory()->create();

        // Old event (100 days ago)
        LearningEvent::create([
            'user_id' => $user->id,
            'event_type' => 'lesson_started',
            'occurred_at' => now()->subDays(100),
        ]);

        // Recent event (10 days ago)
        LearningEvent::create([
            'user_id' => $user->id,
            'event_type' => 'lesson_completed',
            'occurred_at' => now()->subDays(10),
        ]);

        $this->assertEquals(2, LearningEvent::count());

        // Prune older than 90 days
        $exitCode = Artisan::call('analytics:prune-events', ['--days' => 90, '--force' => true]);
        $this->assertEquals(0, $exitCode);

        // Old event deleted, recent event retained
        $this->assertEquals(1, LearningEvent::count());
        $this->assertEquals('lesson_completed', LearningEvent::first()->event_type);
    }
}
