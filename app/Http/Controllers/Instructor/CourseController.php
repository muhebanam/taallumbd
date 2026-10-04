<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CourseController extends Controller
{
    private function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'short_description' => 'required|string|max:500',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'is_free' => 'boolean',
            'level' => 'nullable|string|max:100',
            'duration' => 'nullable|string|max:100',
            'status' => 'required|in:draft,pending,published,coming_soon',
            'enrollment_limit' => 'nullable|integer|min:1',
            'enrollment_start' => 'nullable|date',
            'enrollment_end' => 'nullable|date|after_or_equal:enrollment_start',
            'learn_points' => 'nullable|array',
            'requirements' => 'nullable|array',
            'completion_requirements' => 'nullable|array',
            'completion_requirements.lessons_required' => 'boolean',
            'completion_requirements.quizzes_required' => 'boolean',
            'completion_requirements.assignments_required' => 'boolean',
            'thumbnail' => 'nullable|image|max:4096',
        ];
    }

    public function create()
    {
        return Inertia::render('Instructor/Builder', [
            'categories' => Category::ofType('course')->get(['id', 'name']),
            'course' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());
        $data['instructor_id'] = $request->user()->id;
        $data['slug'] = Str::slug($data['title']).'-'.Str::random(5);
        $data['status'] = 'pending'; // instructor submissions require admin approval
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }
        $course = Course::create($data);

        return redirect()->route('instructor.courses.edit', $course)
            ->with('success', 'কোর্স তৈরি হয়েছে। কারিকুলাম যোগ করুন।');
    }

    public function edit(Request $request, Course $course)
    {
        abort_unless($request->user()->can('update', $course), 403);
        $course->load([
            'sections.curriculumItems.itemable',
        ]);
        $course->sections->each(function ($section) {
            $section->curriculumItems->loadMorph('itemable', [
                Quiz::class => ['questions.options'],
            ]);
        });

        return Inertia::render('Instructor/Builder', [
            'categories' => Category::ofType('course')->get(['id', 'name']),
            'course' => $course,
        ]);
    }

    public function update(Request $request, Course $course)
    {
        abort_unless($request->user()->can('update', $course), 403);
        $data = $request->validate($this->rules());
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('courses', 'public');
        }
        $course->update($data);

        return back()->with('success', 'কোর্স আপডেট হয়েছে।');
    }

    public function students(Request $request, Course $course)
    {
        abort_unless($request->user()->can('update', $course), 403);

        return Inertia::render('Instructor/CourseStudents', [
            'course' => $course->only('id', 'title', 'slug'),
            'enrollments' => $course->enrollments()->with('user:id,name,email')->latest()->paginate(20),
        ]);
    }
}
