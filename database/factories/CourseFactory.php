<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'instructor_id' => User::factory()->instructor(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'short_description' => fake()->sentence(10),
            'description' => fake()->paragraphs(3, true),
            'price' => 500,
            'is_free' => false,
            'level' => fake()->randomElement(['শুরু থেকে', 'মাধ্যমিক', 'উচ্চতর']),
            'duration' => fake()->numberBetween(4, 16).' সপ্তাহ',
            'status' => 'published',
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
            'is_free' => true,
        ]);
    }

    public function paid(float $price = 500): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $price,
            'is_free' => false,
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }
}
