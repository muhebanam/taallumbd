<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdminTeacherController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with('user')
            ->withCount(['followers', 'courses'])
            ->orderBy('sort_order')
            ->paginate(15);

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers
        ]);
    }

    public function create()
    {
        $users = User::whereIn('role', ['instructor', 'admin'])->get();
        return Inertia::render('Admin/Teachers/Create', [
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:teachers,user_id',
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
            'status' => 'required|in:pending,active,inactive,rejected',
            'featured' => 'boolean',
            'is_verified' => 'boolean',
            'allow_follow' => 'boolean',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $slug = Str::slug($request->name);
        // Ensure unique slug
        $count = Teacher::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug = "{$slug}-" . ($count + 1);
        }

        $data = $request->except(['avatar_file', 'cover_file']);
        $data['slug'] = $slug;

        if ($request->hasFile('avatar_file')) {
            $data['avatar'] = $request->file('avatar_file')->store('teachers/avatars', 'public');
        }

        if ($request->hasFile('cover_file')) {
            $data['cover_photo'] = $request->file('cover_file')->store('teachers/covers', 'public');
        }

        if ($request->boolean('is_verified')) {
            $data['verified_at'] = now();
        }

        Teacher::create($data);

        return redirect()->route('admin.teachers.index')->with('success', 'শিক্ষক প্রোফাইল সফলভাবে তৈরি হয়েছে।');
    }

    public function edit(Teacher $teacher)
    {
        $users = User::whereIn('role', ['instructor', 'admin'])->get();
        return Inertia::render('Admin/Teachers/Edit', [
            'teacher' => $teacher,
            'users' => $users
        ]);
    }

    public function update(Request $request, Teacher $teacher)
    {
        $request->validate([
            'user_id' => "nullable|exists:users,id|unique:teachers,user_id,{$teacher->id}",
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
            'status' => 'required|in:pending,active,inactive,rejected',
            'featured' => 'boolean',
            'is_verified' => 'boolean',
            'allow_follow' => 'boolean',
            'show_email' => 'boolean',
            'show_phone' => 'boolean',
            'sort_order' => 'integer',
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

        if ($request->boolean('is_verified') && !$teacher->is_verified) {
            $data['verified_at'] = now();
        } elseif (!$request->boolean('is_verified')) {
            $data['verified_at'] = null;
        }

        $teacher->update($data);

        return redirect()->route('admin.teachers.index')->with('success', 'শিক্ষক প্রোফাইল সফলভাবে আপডেট হয়েছে।');
    }

    public function destroy(Teacher $teacher)
    {
        if ($teacher->avatar) {
            Storage::disk('public')->delete($teacher->avatar);
        }
        if ($teacher->cover_photo) {
            Storage::disk('public')->delete($teacher->cover_photo);
        }
        $teacher->delete();

        return redirect()->route('admin.teachers.index')->with('success', 'শিক্ষক প্রোফাইল সফলভাবে ডিলিট হয়েছে।');
    }

    public function verify(Teacher $teacher)
    {
        $teacher->update([
            'is_verified' => !$teacher->is_verified,
            'verified_at' => !$teacher->is_verified ? now() : null
        ]);
        return back()->with('success', 'ভেরিফিকেশন স্ট্যাটাস আপডেট হয়েছে।');
    }

    public function feature(Teacher $teacher)
    {
        $teacher->update(['featured' => !$teacher->featured]);
        return back()->with('success', 'ফিচার্ড স্ট্যাটাস আপডেট হয়েছে।');
    }

    public function activate(Teacher $teacher)
    {
        $teacher->update(['status' => 'active']);
        return back()->with('success', 'শিক্ষক প্রোফাইল সক্রিয় করা হয়েছে।');
    }

    public function reject(Teacher $teacher)
    {
        $teacher->update(['status' => 'rejected']);
        return back()->with('success', 'শিক্ষক প্রোফাইল বাতিল করা হয়েছে।');
    }
}
