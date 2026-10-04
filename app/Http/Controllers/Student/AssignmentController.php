<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AssignmentController extends Controller
{
    public function show(Request $request, Assignment $assignment)
    {
        abort_unless($request->user()->isEnrolled($assignment->course), 403);

        return Inertia::render('Student/AssignmentView', [
            'assignment' => $assignment->load('course:id,title,slug'),
            'submission' => $assignment->submissions()->where('user_id', $request->user()->id)->latest()->first(),
        ]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        abort_unless($request->user()->isEnrolled($assignment->course), 403);
        $data = $request->validate([
            'answer_text' => 'nullable|string|max:20000',
            'file' => 'nullable|file|max:10240|mimes:pdf,doc,docx,zip,jpg,png',
        ]);
        abort_if(empty($data['answer_text']) && ! $request->hasFile('file'), 422, 'উত্তর বা ফাইল দিন।');

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'answer_text' => $data['answer_text'] ?? null,
            'file_path' => $request->hasFile('file') ? $request->file('file')->store('assignments', 'public') : null,
            'status' => 'submitted',
        ]);

        return back()->with('success', 'অ্যাসাইনমেন্ট জমা হয়েছে।');
    }
}
