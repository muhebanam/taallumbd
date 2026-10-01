<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Fatwa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class InstructorTeacherProfileController extends Controller
{
    public function edit()
    {
        $teacher = Teacher::where('user_id', auth()->id())->first();
        return Inertia::render('Instructor/TeacherProfile/Edit', [
            'teacher' => $teacher
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'headline' => 'nullable|string|max:255',
            'short_bio' => 'nullable|string',
            'bio' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'telegram_url' => 'nullable|url|max:255',
            'avatar_file' => 'nullable|image|max:2048',
            'cover_file' => 'nullable|image|max:2048',
            'specialties' => 'nullable|array',
            'knowledge_path' => 'nullable|array',
            'expertise_map' => 'nullable|array',
            'qualifications' => 'nullable|array',
            'experiences' => 'nullable|array',
            'office_hours' => 'nullable|array',
            'consultation_enabled' => 'boolean',
            'consultation_note' => 'nullable|string',
            'allow_follow' => 'boolean',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
        ]);

        $exists = Teacher::where('user_id', auth()->id())->exists();
        if ($exists) {
            return back()->with('error', 'আপনার ইতিমধ্যে শিক্ষক প্রোফাইল রয়েছে।');
        }

        $slug = Str::slug($request->name);
        $count = Teacher::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug = "{$slug}-" . ($count + 1);
        }

        $data = $request->except(['avatar_file', 'cover_file']);
        $data['user_id'] = auth()->id();
        $data['slug'] = $slug;
        $data['status'] = 'pending'; // Instructor self profiles require admin approval
        $data['is_verified'] = false;
        $data['featured'] = false;

        if ($request->hasFile('avatar_file')) {
            $data['avatar'] = $request->file('avatar_file')->store('teachers/avatars', 'public');
        }

        if ($request->hasFile('cover_file')) {
            $data['cover_photo'] = $request->file('cover_file')->store('teachers/covers', 'public');
        }

        Teacher::create($data);

        return redirect()->route('instructor.profile.teacher.edit')->with('success', 'আপনার শিক্ষক প্রোফাইল সফলভাবে তৈরি হয়েছে এবং অনুমোদনের জন্য পাঠানো হয়েছে।');
    }

    public function update(Request $request)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'headline' => 'nullable|string|max:255',
            'short_bio' => 'nullable|string',
            'bio' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'youtube_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'telegram_url' => 'nullable|url|max:255',
            'avatar_file' => 'nullable|image|max:2048',
            'cover_file' => 'nullable|image|max:2048',
            'specialties' => 'nullable|array',
            'knowledge_path' => 'nullable|array',
            'expertise_map' => 'nullable|array',
            'qualifications' => 'nullable|array',
            'experiences' => 'nullable|array',
            'office_hours' => 'nullable|array',
            'consultation_enabled' => 'boolean',
            'consultation_note' => 'nullable|string',
            'allow_follow' => 'boolean',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
        ]);

        $data = $request->except(['avatar_file', 'cover_file']);

        if ($request->name !== $teacher->name) {
            $slug = Str::slug($request->name);
            $count = Teacher::where('slug', 'like', "{$slug}%")->where('id', '!=', $teacher->id)->count();
            if ($count > 0) {
                $slug = "{$slug}-" . ($count + 1);
            }
            $data['slug'] = $slug;
        }

        if ($request->hasFile('avatar_file')) {
            if ($teacher->avatar) {
                Storage::disk('public')->delete($teacher->avatar);
            }
            $data['avatar'] = $request->file('avatar_file')->store('teachers/avatars', 'public');
        }

        if ($request->hasFile('cover_file')) {
            if ($teacher->cover_photo) {
                Storage::disk('public')->delete($teacher->cover_photo);
            }
            $data['cover_photo'] = $request->file('cover_file')->store('teachers/covers', 'public');
        }

        $teacher->update($data);

        return redirect()->route('instructor.profile.teacher.edit')->with('success', 'আপনার শিক্ষক প্রোফাইল সফলভাবে আপডেট হয়েছে।');
    }

    public function questions()
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        
        $questions = Fatwa::where('teacher_id', $teacher->id)
            ->with('user')
            ->latest()
            ->paginate(15);

        return Inertia::render('Instructor/TeacherProfile/Questions', [
            'questions' => $questions
        ]);
    }

    public function answerQuestion(Request $request, $id)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $question = Fatwa::where('teacher_id', $teacher->id)->findOrFail($id);

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

    public function rejectQuestion($id)
    {
        $teacher = Teacher::where('user_id', auth()->id())->firstOrFail();
        $question = Fatwa::where('teacher_id', $teacher->id)->findOrFail($id);

        $question->update(['status' => 'rejected']);
        return back()->with('success', 'প্রশ্নটি বাতিল করা হয়েছে।');
    }
}
