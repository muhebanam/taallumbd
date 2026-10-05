<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FatwaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_user_id' => User::factory(),
            'answered_by' => User::factory()->instructor(),
            'category_id' => Category::factory()->fatwa(),
            'questioner_name' => fake()->name(),
            'questioner_email' => fake()->safeEmail(),
            'questioner_phone' => fake()->phoneNumber(),
            'question_title' => fake()->sentence(6),
            'question_body' => fake()->paragraphs(2, true),
            'answer_body' => fake()->paragraphs(3, true),
            'references' => fake()->sentence(),
            'is_private' => false,
            'status' => 'published',
            'published_at' => now(),
            'answered_at' => now(),
            'views_count' => fake()->numberBetween(0, 500),
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => 'published',
            'published_at' => now(),
            'answered_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'answer_body' => null,
            'answered_by' => null,
            'published_at' => null,
            'answered_at' => null,
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
        ]);
    }
}
