<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizSubmitTest extends TestCase
{
    use RefreshDatabase;

    private function createQuizWithQuestions(Course $course): array
    {
        $quiz = Quiz::factory()->create([
            'course_id' => $course->id,
            'total_marks' => 10,
            'pass_marks' => 6,
        ]);

        $q1 = QuizQuestion::factory()->create([
            'quiz_id' => $quiz->id,
            'marks' => 5,
        ]);
        $q1Correct = QuizOption::factory()->correct()->create(['question_id' => $q1->id]);
        $q1Wrong = QuizOption::factory()->incorrect()->create(['question_id' => $q1->id]);

        $q2 = QuizQuestion::factory()->create([
            'quiz_id' => $quiz->id,
            'marks' => 5,
        ]);
        $q2Correct = QuizOption::factory()->correct()->create(['question_id' => $q2->id]);
        $q2Wrong = QuizOption::factory()->incorrect()->create(['question_id' => $q2->id]);

        return [
            'quiz' => $quiz,
            'q1' => $q1,
            'q1Correct' => $q1Correct,
            'q1Wrong' => $q1Wrong,
            'q2' => $q2,
            'q2Correct' => $q2Correct,
            'q2Wrong' => $q2Wrong,
        ];
    }

    public function test_enrolled_student_can_view_quiz(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        Enrollment::factory()->create(['user_id' => $student->id, 'course_id' => $course->id]);

        $data = $this->createQuizWithQuestions($course);

        $response = $this->actingAs($student)->get(route('student.quizzes.show', $data['quiz']));
        $response->assertStatus(200);
    }

    public function test_non_enrolled_student_cannot_view_quiz(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        $data = $this->createQuizWithQuestions($course);

        $response = $this->actingAs($student)->get(route('student.quizzes.show', $data['quiz']));
        $response->assertStatus(403);
    }

    public function test_enrolled_student_submits_correct_answers_and_passes(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        Enrollment::factory()->create(['user_id' => $student->id, 'course_id' => $course->id]);

        $data = $this->createQuizWithQuestions($course);

        $response = $this->actingAs($student)->post(route('student.quizzes.submit', $data['quiz']), [
            'answers' => [
                $data['q1']->id => [$data['q1Correct']->id],
                $data['q2']->id => [$data['q2Correct']->id],
            ],
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $data['quiz']->id,
            'user_id' => $student->id,
            'score' => 10,
            'status' => 'passed',
        ]);
    }

    public function test_enrolled_student_submits_incorrect_answers_and_fails(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        Enrollment::factory()->create(['user_id' => $student->id, 'course_id' => $course->id]);

        $data = $this->createQuizWithQuestions($course);

        $response = $this->actingAs($student)->post(route('student.quizzes.submit', $data['quiz']), [
            'answers' => [
                $data['q1']->id => [$data['q1Wrong']->id],
                $data['q2']->id => [$data['q2Wrong']->id],
            ],
        ]);

        $response->assertStatus(302);

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $data['quiz']->id,
            'user_id' => $student->id,
            'score' => 0,
            'status' => 'failed',
        ]);
    }

    public function test_non_enrolled_user_cannot_submit_quiz(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        $data = $this->createQuizWithQuestions($course);

        $response = $this->actingAs($student)->post(route('student.quizzes.submit', $data['quiz']), [
            'answers' => [
                $data['q1']->id => [$data['q1Correct']->id],
            ],
        ]);

        $response->assertStatus(403);
        $this->assertEquals(0, QuizAttempt::count());
    }
}
