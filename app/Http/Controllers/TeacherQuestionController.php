<?php

namespace App\Http\Controllers;

use App\Models\Fatwa;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeacherQuestionController extends Controller
{
    public function store(Request $request, Teacher $teacher)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'question_body' => 'required|string',
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('type', 'fatwa')->where('status', 'active')),
            ],
            'is_private' => 'boolean',
        ]);

        Fatwa::create([
            'question_user_id' => auth()->id(),
            'answered_by' => $teacher->user_id,
            'teacher_id' => $teacher->id,
            'category_id' => $request->integer('category_id'),
            'question_title' => $request->subject,
            'question_body' => $request->question_body,
            'is_private' => $request->boolean('is_private'),
            'status' => 'pending',
        ]);

        return back()->with('success', 'আপনার প্রশ্নটি সফলভাবে জমা দেওয়া হয়েছে। শিক্ষক উত্তর দিলে এটি প্রকাশিত হবে।');
    }
}
