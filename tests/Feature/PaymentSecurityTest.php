<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_another_users_invoice(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ১',
            'slug' => 'course-1',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 500,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $user1->id,
            'course_id' => $course->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user2)->get('/invoice/'.$order->id);
        $response->assertStatus(403);
    }

    public function test_user_can_view_own_invoice(): void
    {
        $user = User::factory()->create();
        $instructor = User::factory()->create(['role' => 'instructor']);
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'title' => 'কোর্স ১',
            'slug' => 'course-1',
            'short_description' => 'বর্ণনা',
            'description' => 'বিস্তারিত',
            'price' => 500,
            'is_free' => false,
            'status' => 'published',
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'amount' => 500,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/invoice/'.$order->id);
        $response->assertStatus(200);
    }
}
