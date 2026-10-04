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
            'instructor_id' => User::factory()->state(['role' => 'instructor']),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(4),
            'short_description' => fake()->sentence(10),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->randomElement([0, 300, 500, 800]),
            'is_free' => fake()->boolean(40),
            'level' => fake()->randomElement(['শুরু থেকে', 'মাধ্যমিক', 'উচ্চতর']),
            'duration' => fake()->numberBetween(4, 16).' সপ্তাহ',
            'status' => 'published',
        ];
    }
}
