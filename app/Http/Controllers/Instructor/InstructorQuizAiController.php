<?php

namespace App\Http\Controllers\Instructor;

use App\AI\Services\AiQuizGeneratorService;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorQuizAiController extends Controller
{
    public function __construct(
        protected AiQuizGeneratorService $quizGenerator
    ) {}

    protected function authorizeInstructor(Course $course, Request $request): void
    {
        $user = $request->user();
        if ($course->instructor_id !== $user->id && ! $user->isAdmin()) {
            abort(403, 'এই কোর্সে কুইজ প্রস্তুত করার অনুমতি আপনার নেই।');
        }
    }

    /**
     * Generate draft MCQs from lesson content (Draft only - not published yet!).
     */
    public function generate(Request $request, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorizeInstructor($course, $request);

        $questionCount = (int) $request->input('count', 3);
        $result = $this->quizGenerator->generateDraftQuiz(
            lesson: $lesson,
            instructor: $request->user(),
            questionCount: max(1, min(10, $questionCount))
        );

        return response()->json($result);
    }

    /**
     * Instructor explicitly approves, edits, and saves the draft MCQs into the database.
     */
    public function saveQuiz(Request $request, Course $course, Lesson $lesson): JsonResponse
    {
        $this->authorizeInstructor($course, $request);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|max:500',
            'questions.*.marks' => 'nullable|integer|min:1',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.text' => 'required|string|max:255',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
            'title' => $validated['title'],
            'description' => 'এআই সহায়তায় প্রস্তুতকৃত ও ইনস্ট্রাক্টর কর্তৃক অনুমোদিত কুইজ',
            'total_marks' => count($validated['questions']),
            'pass_marks' => (int) ceil(count($validated['questions']) * 0.7),
        ]);

        foreach ($validated['questions'] as $qData) {
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $qData['question'],
                'type' => 'single_choice',
                'marks' => $qData['marks'] ?? 1,
            ]);

            foreach ($qData['options'] as $optData) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optData['text'],
                    'is_correct' => (bool) $optData['is_correct'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'মাশাআল্লাহ! কুইজটি সফলভাবে নিরীক্ষা ও সংরক্ষণ করা হয়েছে।',
            'quiz_id' => $quiz->id,
        ]);
    }
}
