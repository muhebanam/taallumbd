<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fatwa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminTeacherQuestionController extends Controller
{
    public function index()
    {
        $questions = Fatwa::whereNotNull('teacher_id')
            ->with(['teacher', 'user'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/TeacherQuestions/Index', [
            'questions' => $questions
        ]);
    }

    public function answer(Request $request, Fatwa $question)
    {
        $request->validate([
            'answer_body' => 'required|string',
        ]);

        $question->update([
            'answer_body' => $request->answer_body,
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with('success', 'প্রশ্নের উত্তর সফলভাবে প্রকাশিত হয়েছে।');
    }

    public function reject(Fatwa $question)
    {
        $question->update(['status' => 'rejected']);
        return back()->with('success', 'প্রশ্নটি বাতিল করা হয়েছে।');
    }
}
