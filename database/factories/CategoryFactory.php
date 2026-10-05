<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::random(4),
            'type' => 'course',
            'parent_id' => null,
            'sort_order' => fake()->numberBetween(0, 10),
            'status' => 'active',
        ];
    }

    public function course(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'course',
        ]);
    }

    public function fatwa(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fatwa',
        ]);
    }

    public function article(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'article',
        ]);
    }

    public function publication(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'publication',
        ]);
    }
}
