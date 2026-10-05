<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TeacherFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'user_id' => User::factory()->instructor(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(6),
            'designation' => 'মুহাদ্দিস ও গবেষক',
            'headline' => fake()->sentence(),
            'short_bio' => fake()->paragraph(),
            'bio' => fake()->paragraphs(3, true),
            'avatar' => null,
            'cover_photo' => null,
            'location' => 'ঢাকা, বাংলাদেশ',
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'specialties' => ['ফিকহ', 'হাদিস'],
            'status' => 'active',
            'featured' => false,
            'is_verified' => true,
            'verified_at' => now(),
            'allow_follow' => true,
            'show_email' => false,
            'show_phone' => false,
            'sort_order' => 0,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
