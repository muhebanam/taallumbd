<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ForumPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => null,
            'category_id' => null,
            'title' => fake()->sentence(5),
            'topic' => 'general',
            'body' => fake()->paragraphs(2, true),
            'views_count' => fake()->numberBetween(0, 50),
            'upvotes_count' => fake()->numberBetween(0, 10),
            'status' => 'published',
            'is_pinned' => false,
            'is_solved' => false,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_pinned' => true,
        ]);
    }

    public function solved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_solved' => true,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'hidden',
        ]);
    }
}
