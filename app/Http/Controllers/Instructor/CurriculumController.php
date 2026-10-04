<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\CourseSection;
use App\Models\CurriculumItem;
use App\Models\Lesson;
use App\Models\LiveClass;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\Resource as CourseResource;
use App\Services\CurriculumItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CurriculumController extends Controller
{
    public function __construct(private CurriculumItemService $curriculumService) {}

    private function authorizeCourse(Request $request, Course $course): void
    {
        abort_unless($request->user()->can('update', $course), 403);
    }

    private function assertSectionBelongsToCourse(Course $course, CourseSection $section): void
    {
        abort_unless((int) $section->course_id === (int) $course->id, 403);
    }

    private function assertModelBelongsToCourse(Course $course, $model): void
    {
        abort_unless((int) $model->course_id === (int) $course->id, 403);
    }

    /* ---------------- Sections ---------------- */

    public function storeSection(Request $request, Course $course)
    {
        $this->authorizeCourse($request, $course);
        $data = $request->validate(['title' => 'required|string|max:255']);

        $course->sections()->create([
            'title' => $data['title'],
            'sort_order' => $course->sections()->count() + 1,
        ]);

        return back()->with('success', 'সেকশন যোগ হয়েছে।');
    }

    public function updateSection(Request $request, Course $course, CourseSection $section)
    {
        $this->authorizeCourse($request, $course);
        $this->assertSectionBelongsToCourse($course, $section);
        $data = $request->validate(['title' => 'required|string|max:255']);

        $section->update($data);

        return back()->with('success', 'সেকশন আপডেট হয়েছে।');
    }

    public function deleteSection(Request $request, Course $course, CourseSection $section)
    {
        $this->authorizeCourse($request, $course);
        $this->assertSectionBelongsToCourse($course, $section);

        // Delete all items inside this section
        foreach ($section->curriculumItems as $item) {
            if ($item->itemable) {
                $item->itemable->delete();
            }
            $item->delete();
        }

        $section->delete();

        return back()->with('success', 'সেকশন মুছে ফেলা হয়েছে।');
    }

    /* ---------------- Lessons ---------------- */

    public function storeLesson(Request $request, Course $course, CourseSection $section)
    {
        $this->authorizeCourse($request, $course);
        $this->assertSectionBelongsToCourse($course, $section);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'is_preview' => 'boolean',
            'lecture_sheet' => 'nullable|file|max:10240|mimes:pdf',
        ]);

        if ($request->hasFile('lecture_sheet')) {
            $data['lecture_sheet'] = $request->file('lecture_sheet')->store('lecture-sheets', 'public');
        }

        $lesson = $course->lessons()->create([
            'section_id' => $section->id,
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::random(4),
            'content' => $data['content'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'lecture_sheet' => $data['lecture_sheet'] ?? null,
            'is_preview' => (bool) ($data['is_preview'] ?? false),
            'sort_order' => CurriculumItem::where('course_id', $course->id)->where('section_id', $section->id)->count() + 1,
        ]);

        $this->curriculumService->createItemForModel($course, $section, $lesson, 'lesson', [
            'is_preview' => $lesson->is_preview,
            'sort_order' => $lesson->sort_order,
        ]);

        return back()->with('success', 'পাঠ যোগ হয়েছে।');
    }

    public function updateLesson(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $lesson);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'is_preview' => 'boolean',
            'lecture_sheet' => 'nullable|file|max:10240|mimes:pdf',
        ]);

        if ($request->hasFile('lecture_sheet')) {
            $data['lecture_sheet'] = $request->file('lecture_sheet')->store('lecture-sheets', 'public');
        }

        $lesson->update($data);

        $this->curriculumService->updateItemMeta($lesson, [
            'title' => $lesson->title,
            'is_preview' => $lesson->is_preview,
        ]);

        return back()->with('success', 'পাঠ আপডেট হয়েছে।');
    }

    public function deleteLesson(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $lesson);

        $this->curriculumService->deleteItemForModel($lesson);
        $lesson->delete();

        return back()->with('success', 'পাঠ মুছে ফেলা হয়েছে।');
    }

    /* ---------------- Quizzes ---------------- */

    public function storeQuiz(Request $request, Course $course, CourseSection $section)
    {
        $this->authorizeCourse($request, $course);
        $this->assertSectionBelongsToCourse($course, $section);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pass_marks' => 'required|integer|min:0',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:single_choice,multiple_choice,true_false',
            'questions.*.marks' => 'required|integer|min:1',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'boolean',
        ]);

        $quiz = Quiz::create([
            'course_id' => $course->id,
            'lesson_id' => null, // builder stores directly under course/section now
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'total_marks' => collect($data['questions'])->sum('marks'),
            'pass_marks' => $data['pass_marks'],
        ]);

        foreach ($data['questions'] as $q) {
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'marks' => $q['marks'],
            ]);
            foreach ($q['options'] as $opt) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['option_text'],
                    'is_correct' => (bool) ($opt['is_correct'] ?? false),
                ]);
            }
        }

        $this->curriculumService->createItemForModel($course, $section, $quiz, 'quiz', [
            'sort_order' => CurriculumItem::where('course_id', $course->id)->where('section_id', $section->id)->count() + 1,
        ]);

        return back()->with('success', 'কুইজ তৈরি হয়েছে।');
    }

    public function updateQuiz(Request $request, Course $course, Quiz $quiz)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $quiz);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pass_marks' => 'required|integer|min:0',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string',
            'questions.*.type' => 'required|in:single_choice,multiple_choice,true_false',
            'questions.*.marks' => 'required|integer|min:1',
            'questions.*.options' => 'required|array|min:2',
            'questions.*.options.*.option_text' => 'required|string',
            'questions.*.options.*.is_correct' => 'boolean',
        ]);

        $quiz->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'total_marks' => collect($data['questions'])->sum('marks'),
            'pass_marks' => $data['pass_marks'],
        ]);

        // Recreate questions and options
        $quiz->questions()->delete();

        foreach ($data['questions'] as $q) {
            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question' => $q['question'],
                'type' => $q['type'],
                'marks' => $q['marks'],
            ]);
            foreach ($q['options'] as $opt) {
                QuizOption::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['option_text'],
                    'is_correct' => (bool) ($opt['is_correct'] ?? false),
                ]);
            }
        }

        $this->curriculumService->updateItemMeta($quiz, [
            'title' => $quiz->title,
        ]);

        return back()->with('success', 'কুইজ আপডেট হয়েছে।');
    }

    public function deleteQuiz(Request $request, Course $course, Quiz $quiz)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $quiz);

        $this->curriculumService->deleteItemForModel($quiz);
        $quiz->delete();

        return back()->with('success', 'কুইজ মুছে ফেলা হয়েছে।');
    }

    /* ---------------- Assignments ---------------- */

    public function storeAssignment(Request $request, Course $course, CourseSection $section)
    {
        $this->authorizeCourse($request, $course);
        $this->assertSectionBelongsToCourse($course, $section);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date',
            'total_marks' => 'required|integer|min:0',
        ]);

        $assignment = $course->assignments()->create([
            'lesson_id' => null,
            'title' => $data['title'],
            'description' => $data['description'],
            'deadline' => $data['deadline'] ?? null,
            'total_marks' => $data['total_marks'],
        ]);

        $this->curriculumService->createItemForModel($course, $section, $assignment, 'assignment', [
            'sort_order' => CurriculumItem::where('course_id', $course->id)->where('section_id', $section->id)->count() + 1,
        ]);

        return back()->with('success', 'অ্যাসাইনমেন্ট তৈরি হয়েছে।');
    }

    public function updateAssignment(Request $request, Course $course, Assignment $assignment)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $assignment);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deadline' => 'nullable|date',
            'total_marks' => 'required|integer|min:0',
        ]);

        $assignment->update($data);

        $this->curriculumService->updateItemMeta($assignment, [
            'title' => $assignment->title,
        ]);

        return back()->with('success', 'অ্যাসাইনমেন্ট আপডেট হয়েছে।');
    }

    public function deleteAssignment(Request $request, Course $course, Assignment $assignment)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $assignment);

        $this->curriculumService->deleteItemForModel($assignment);
        $assignment->delete();

        return back()->with('success', 'অ্যাসাইনমেন্ট মুছে ফেলা হয়েছে।');
    }

    /* ---------------- Resources ---------------- */

    public function storeResource(Request $request, Course $course, CourseSection $section)
    {
        $this->authorizeCourse($request, $course);
        $this->assertSectionBelongsToCourse($course, $section);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|url|max:500',
            'file' => 'nullable|file|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('resources', 'public');
        }

        $resource = CourseResource::create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'file_path' => $data['file_path'] ?? null,
            'url' => $data['url'] ?? null,
        ]);

        $this->curriculumService->createItemForModel($course, $section, $resource, 'resource', [
            'sort_order' => CurriculumItem::where('course_id', $course->id)->where('section_id', $section->id)->count() + 1,
        ]);

        return back()->with('success', 'রিসোর্স যোগ হয়েছে।');
    }

    public function updateResource(Request $request, Course $course, CourseResource $resource)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $resource);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'nullable|url|max:500',
            'file' => 'nullable|file|max:10240',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('resources', 'public');
        }

        $resource->update($data);

        $this->curriculumService->updateItemMeta($resource, [
            'title' => $resource->title,
        ]);

        return back()->with('success', 'রিসোর্স আপডেট হয়েছে।');
    }

    public function deleteResource(Request $request, Course $course, CourseResource $resource)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $resource);

        $this->curriculumService->deleteItemForModel($resource);
        $resource->delete();

        return back()->with('success', 'রিসোর্স মুছে ফেলা হয়েছে।');
    }

    /* ---------------- Live Classes ---------------- */

    public function storeLiveClass(Request $request, Course $course, CourseSection $section)
    {
        $this->authorizeCourse($request, $course);
        $this->assertSectionBelongsToCourse($course, $section);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'meeting_url' => 'nullable|url|max:500',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1',
        ]);

        $liveClass = LiveClass::create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'meeting_url' => $data['meeting_url'] ?? null,
            'start_time' => $data['start_time'],
            'duration' => $data['duration'],
        ]);

        $this->curriculumService->createItemForModel($course, $section, $liveClass, 'live_class', [
            'sort_order' => CurriculumItem::where('course_id', $course->id)->where('section_id', $section->id)->count() + 1,
        ]);

        return back()->with('success', 'লাইভ ক্লাস যোগ হয়েছে।');
    }

    public function updateLiveClass(Request $request, Course $course, LiveClass $liveClass)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $liveClass);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'meeting_url' => 'nullable|url|max:500',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1',
        ]);

        $liveClass->update($data);

        $this->curriculumService->updateItemMeta($liveClass, [
            'title' => $liveClass->title,
        ]);

        return back()->with('success', 'লাইভ ক্লাস আপডেট হয়েছে।');
    }

    public function deleteLiveClass(Request $request, Course $course, LiveClass $liveClass)
    {
        $this->authorizeCourse($request, $course);
        $this->assertModelBelongsToCourse($course, $liveClass);

        $this->curriculumService->deleteItemForModel($liveClass);
        $liveClass->delete();

        return back()->with('success', 'লাইভ ক্লাস মুছে ফেলা হয়েছে।');
    }

    /* ---------------- Reorder ---------------- */

    public function reorder(Request $request, Course $course)
    {
        $this->authorizeCourse($request, $course);
        $data = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:curriculum_items,id',
            'items.*.section_id' => 'nullable|integer|exists:course_sections,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        $this->curriculumService->reorder($course, $data['items']);

        return back()->with('success', 'কারিকুলাম নতুন করে সাজানো হয়েছে।');
    }

    /* ---------------- Submissions ---------------- */

    public function reviewSubmission(Request $request, AssignmentSubmission $submission)
    {
        $this->authorizeCourse($request, $submission->assignment->course);
        $submission->update($request->validate([
            'marks' => 'required|integer|min:0',
            'feedback' => 'nullable|string|max:2000',
        ]) + ['status' => 'reviewed']);

        return back()->with('success', 'মূল্যায়ন সংরক্ষিত হয়েছে।');
    }
}
