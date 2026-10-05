<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuizFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'lesson_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'total_marks' => 10,
            'pass_marks' => 6,
        ];
    }

    public function withQuestions(int $count = 2): static
    {
        return $this->afterCreating(function (Quiz $quiz) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $question = QuizQuestion::factory()->create([
                    'quiz_id' => $quiz->id,
                    'marks' => 5,
                ]);

                QuizOption::factory()->correct()->create([
                    'question_id' => $question->id,
                    'option_text' => 'সঠিক উত্তর',
                ]);

                QuizOption::factory()->incorrect()->create([
                    'question_id' => $question->id,
                    'option_text' => 'ভুল উত্তর ১',
                ]);

                QuizOption::factory()->incorrect()->create([
                    'question_id' => $question->id,
                    'option_text' => 'ভুল উত্তর ২',
                ]);
            }
        });
    }
}
