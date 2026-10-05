<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(5);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory()->article(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 9999),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(4, true),
            'thumbnail' => null,
            'status' => 'published',
            'published_at' => now(),
        ];
    }
}
