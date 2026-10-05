<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'amount' => 500.00,
            'coupon_code' => null,
            'discount_amount' => 0.00,
            'status' => 'pending',
            'payment_method' => 'manual',
            'sender_phone' => '01712345678',
            'transaction_id' => strtoupper(fake()->bothify('TRX##??##?')),
            'screenshot_path' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function pendingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_verification',
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
