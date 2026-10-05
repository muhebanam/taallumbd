<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\EventTracker;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuizController extends Controller
{
    public function show(Request $request, Quiz $quiz)
    {
        abort_unless($request->user()->isEnrolled($quiz->course), 403);

        return Inertia::render('Student/QuizView', [
            'quiz' => $quiz->load(['questions.options:id,question_id,option_text']), // correct answers never sent to client
            'lastAttempt' => $quiz->attempts()->where('user_id', $request->user()->id)->latest()->first(),
        ]);
    }

    public function submit(Request $request, Quiz $quiz)
    {
        abort_unless($request->user()->isEnrolled($quiz->course), 403);
        $answers = $request->validate(['answers' => 'required|array'])['answers']; // {question_id: [option_ids]}

        $score = 0;
        foreach ($quiz->questions()->with('options')->get() as $question) {
            $correct = $question->options->where('is_correct', true)->pluck('id')->sort()->values();
            $given = collect($answers[$question->id] ?? [])->map(fn ($v) => (int) $v)->sort()->values();
            if ($correct->all() === $given->all() && $correct->isNotEmpty()) {
                $score += $question->marks;
            }
        }

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $request->user()->id,
            'score' => $score,
            'status' => $score >= $quiz->pass_marks ? 'passed' : 'failed',
            'submitted_at' => now(),
        ]);

        try {
            $tracker = app(EventTracker::class);
            $tracker->trackQuizAttempted($request->user(), $quiz->id, $quiz->course_id, ['score' => $score]);
            if ($attempt->status === 'passed') {
                $tracker->trackQuizPassed($request->user(), $quiz->id, $quiz->course_id, (float) $score);
            }
        } catch (\Throwable $e) {
        }

        return back()->with('success', $attempt->status === 'passed'
            ? "মাবরূক! আপনি কুইজে পাস করেছেন। স্কোর: {$score}/{$quiz->total_marks}"
            : "স্কোর: {$score}/{$quiz->total_marks}। আবার চেষ্টা করুন।");
    }
}
