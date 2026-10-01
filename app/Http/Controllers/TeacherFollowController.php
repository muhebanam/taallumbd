<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherFollowController extends Controller
{
    public function store(Teacher $teacher)
    {
        $user = auth()->user();

        if ($user->id === $teacher->user_id) {
            return back()->with('error', 'আপনি নিজেকে ফলো করতে পারবেন না।');
        }

        if (!$teacher->allow_follow) {
            return back()->with('error', 'এই শিক্ষকের ফলো করা বর্তমানে বন্ধ আছে।');
        }

        if (!$user->followedTeachers()->where('teacher_id', $teacher->id)->exists()) {
            $user->followedTeachers()->attach($teacher->id);
        }

        return back();
    }

    public function destroy(Teacher $teacher)
    {
        $user = auth()->user();
        $user->followedTeachers()->detach($teacher->id);
        return back();
    }
}
