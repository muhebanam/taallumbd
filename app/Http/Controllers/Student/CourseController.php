<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function show(Request $request, Course $course)
    {
        $user = $request->user();
        abort_unless($user->isEnrolled($course) || $user->isAdmin() || $course->instructor_id === $user->id, 403);

        return Inertia::render('Student/CourseView', [
            'course' => $course->load([
                'instructor:id,name',
                'sections.curriculumItems.itemable',
            ]),
            'completedLessonIds' => LessonProgress::where('user_id', $user->id)
                ->where('course_id', $course->id)->where('is_completed', true)->pluck('lesson_id'),
            'enrollment' => $user->enrollments()->where('course_id', $course->id)->first(),
        ]);
    }
}
