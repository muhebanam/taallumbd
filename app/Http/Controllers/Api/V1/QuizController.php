<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\QuizAttemptResource;
use App\Http\Resources\Api\V1\QuizResource;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\EventTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Get quiz detail with questions and options (without revealing correct answers).
     */
    public function show(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $request->user();
        $course = $quiz->course ?? $quiz->lesson?->section?->course;

        if ($course && (! $user || ! $user->isEnrolledIn($course->id)) && (! $quiz->lesson || ! $quiz->lesson->is_free)) {
            return response()->json([
                'success' => false,
                'message' => 'কুইজে অংশগ্রহণের জন্য কোর্সে এনরোল করুন।',
            ], 403);
        }

        $quiz->load(['questions.options']);

        return response()->json([
            'success' => true,
            'data' => new QuizResource($quiz),
            'quiz' => new QuizResource($quiz),
        ]);
    }

    /**
     * Submit quiz answers and compute score.
     */
    public function submit(Request $request, Quiz $quiz): JsonResponse
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable',
        ]);

        $user = $request->user();
        $quiz->load(['questions.options']);

        $totalMarks = 0;
        $obtainedMarks = 0;
        $review = [];

        foreach ($quiz->questions as $question) {
            $qMarks = (int) ($question->marks ?: 1);
            $totalMarks += $qMarks;

            $selectedRaw = $request->input("answers.{$question->id}");
            $selectedOptionId = is_array($selectedRaw) ? ($selectedRaw[0] ?? null) : $selectedRaw;
            $correctOption = $question->options->firstWhere('is_correct', true);
            $isCorrect = false;

            if ($selectedOptionId) {
                $selectedOption = $question->options->firstWhere('id', (int) $selectedOptionId);
                if ($selectedOption && $selectedOption->is_correct) {
                    $isCorrect = true;
                    $obtainedMarks += $qMarks;
                }
            }

            $review[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'selected_option_id' => $selectedOptionId ? (int) $selectedOptionId : null,
                'correct_option_id' => $correctOption?->id,
                'is_correct' => $isCorrect,
                'marks_awarded' => $isCorrect ? $qMarks : 0,
            ];
        }

        $passMarks = (int) ($quiz->pass_marks ?: round($totalMarks * 0.7));
        $isPassed = $obtainedMarks >= $passMarks;
        $status = $isPassed ? 'passed' : 'failed';

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $obtainedMarks,
            'status' => $status,
            'submitted_at' => now(),
        ]);

        try {
            app(EventTracker::class)->trackQuizSubmitted($user, $quiz->id, $obtainedMarks, $totalMarks, $status);
        } catch (\Throwable $e) {
            // Non-blocking
        }

        return response()->json([
            'success' => true,
            'message' => $isPassed ? 'অভিনন্দন! আপনি কুইজে উত্তীর্ণ হয়েছেন।' : 'দুঃখিত, আপনি প্রয়োজনীয় পাস মার্ক পাননি। আবার চেষ্টা করুন।',
            'data' => [
                'score' => $obtainedMarks,
                'total_marks' => $totalMarks,
                'pass_marks' => $passMarks,
                'status' => $status,
                'passed' => $isPassed,
                'is_passed' => $isPassed,
                'attempt_id' => $attempt->id,
                'review' => $review,
            ],
            'score' => $obtainedMarks,
            'total_marks' => $totalMarks,
            'pass_marks' => $passMarks,
            'status' => $status,
            'is_passed' => $isPassed,
            'attempt_id' => $attempt->id,
            'review' => $review,
        ]);
    }

    /**
     * Get user's past quiz attempts.
     */
    public function attempts(Request $request, Quiz $quiz): JsonResponse
    {
        $user = $request->user();

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->get();

        $bestScore = $attempts->max('score') ?? 0;
        $hasPassed = $attempts->contains('status', 'passed');

        return response()->json([
            'success' => true,
            'quiz_id' => $quiz->id,
            'has_passed' => $hasPassed,
            'best_score' => $bestScore,
            'total_attempts' => $attempts->count(),
            'data' => QuizAttemptResource::collection($attempts),
            'attempts' => QuizAttemptResource::collection($attempts),
        ]);
    }
}
