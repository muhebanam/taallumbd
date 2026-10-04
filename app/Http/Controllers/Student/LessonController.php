<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function __construct(private ProgressService $progress) {}

    private function authorizeLesson(Request $request, Lesson $lesson): void
    {
        $user = $request->user();
        $course = $lesson->course;
        abort_unless(
            $lesson->is_preview || $user->isEnrolled($course) || $user->isAdmin() || $course->instructor_id === $user->id,
            403, 'এই পাঠটি দেখতে হলে কোর্সে ভর্তি হতে হবে।'
        );
    }

    public function show(Request $request, Lesson $lesson)
    {
        $this->authorizeLesson($request, $lesson);
        $lessons = Lesson::select('lessons.id', 'lessons.title')
            ->join('curriculum_items', 'lessons.id', '=', 'curriculum_items.itemable_id')
            ->where('curriculum_items.itemable_type', Lesson::class)
            ->where('curriculum_items.course_id', $lesson->course_id)
            ->orderBy('curriculum_items.sort_order')
            ->get();
        $index = $lessons->search(fn ($l) => $l->id === $lesson->id);

        return Inertia::render('Student/LessonView', [
            'lesson' => $lesson->load('course:id,title,slug', 'quizzes:id,lesson_id,title'),
            'isCompleted' => LessonProgress::where('user_id', $request->user()->id)
                ->where('lesson_id', $lesson->id)->where('is_completed', true)->exists(),
            'prevLesson' => $index > 0 ? $lessons[$index - 1] : null,
            'nextLesson' => $index !== false && $index < $lessons->count() - 1 ? $lessons[$index + 1] : null,
        ]);
    }

    public function complete(Request $request, Lesson $lesson)
    {
        $this->authorizeLesson($request, $lesson);
        $progress = $this->progress->completeLesson($request->user(), $lesson);

        return back()->with('success', $progress === 100 ? 'মাশাআল্লাহ! কোর্স সম্পন্ন হয়েছে।' : 'পাঠ সম্পন্ন হয়েছে।');
    }
}
