<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('COUPON##??')),
            'discount_type' => 'percent',
            'discount_amount' => 15.00,
            'min_order_amount' => 100.00,
            'expires_at' => now()->addDays(30),
            'usage_limit' => 100,
            'used_count' => 0,
            'status' => 'active',
        ];
    }

    public function fixed(float $amount = 100.00): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed',
            'discount_amount' => $amount,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subDay(),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function maxedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_limit' => 5,
            'used_count' => 5,
        ]);
    }
}
