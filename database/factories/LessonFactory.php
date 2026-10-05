<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LessonFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'course_id' => Course::factory(),
            'section_id' => function (array $attributes) {
                return CourseSection::factory()->create(['course_id' => $attributes['course_id']])->id;
            },
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::random(6),
            'content' => fake()->paragraphs(3, true),
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'lecture_sheet' => null,
            'is_preview' => false,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function preview(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_preview' => true,
        ]);
    }
}
