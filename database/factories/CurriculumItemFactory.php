<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurriculumItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'section_id' => null,
            'itemable_type' => Lesson::class,
            'itemable_id' => Lesson::factory(),
            'item_type' => 'lesson',
            'title_snapshot' => fake()->sentence(3),
            'sort_order' => 1,
            'is_required' => true,
            'is_preview' => false,
            'drip_type' => null,
            'drip_value' => null,
        ];
    }

    public function forLesson(Lesson $lesson): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $lesson->course_id,
            'section_id' => $lesson->section_id,
            'itemable_type' => Lesson::class,
            'itemable_id' => $lesson->id,
            'item_type' => 'lesson',
            'title_snapshot' => $lesson->title,
            'is_preview' => $lesson->is_preview,
            'sort_order' => $lesson->sort_order,
        ]);
    }

    public function forQuiz(Quiz $quiz): static
    {
        return $this->state(fn (array $attributes) => [
            'course_id' => $quiz->course_id,
            'section_id' => null,
            'itemable_type' => Quiz::class,
            'itemable_id' => $quiz->id,
            'item_type' => 'quiz',
            'title_snapshot' => $quiz->title,
            'sort_order' => 2,
        ]);
    }
}
