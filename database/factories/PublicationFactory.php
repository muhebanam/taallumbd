<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PublicationFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(100, 9999),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['book', 'ebook', 'audio', 'video']),
            'file_url' => null,
            'external_url' => null,
            'thumbnail' => null,
            'status' => 'published',
            'published_at' => now(),
        ];
    }
}
