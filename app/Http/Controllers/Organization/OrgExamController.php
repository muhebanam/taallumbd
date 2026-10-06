<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Services\EnterpriseLmsService;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrgExamController extends Controller
{
    public function __construct(
        protected EnterpriseLmsService $lmsService
    ) {}

    /**
     * List exams of the organization.
     */
    public function index(): Response
    {
        $exams = Exam::with(['cohort'])
            ->withCount('submissions')
            ->latest()
            ->paginate(15);

        $cohorts = Cohort::where('status', 'active')->get(['id', 'name']);

        return Inertia::render('Organization/Exams', [
            'exams' => $exams,
            'cohorts' => $cohorts,
        ]);
    }

    /**
     * Store a new exam with question bank.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cohort_id' => 'nullable|exists:cohorts,id',
            'course_id' => 'nullable|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'exam_type' => 'required|in:quiz_mcq,written,oral_hifz,hybrid',
            'duration_minutes' => 'required|integer|min:5|max:300',
            'total_marks' => 'required|numeric|min:1',
            'pass_marks' => 'required|numeric|min:1',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'question_bank' => 'nullable|array',
            'randomize_questions' => 'boolean',
        ]);

        $tenant = TenantContext::getTenant();

        $this->lmsService->createExam(
            $tenant,
            $validated,
            $request->user()->id
        );

        return back()->with('success', 'পরীক্ষা সফলভাবে তৈরি করা হয়েছে।');
    }

    /**
     * Student submits answers for an exam.
     */
    public function submit(Request $request, mixed $organization, Exam $exam): RedirectResponse
    {
        $tenant = TenantContext::getTenant();
        if ($exam->organization_id !== $tenant->id) {
            abort(403);
        }

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $this->lmsService->evaluateSubmission(
            $exam,
            $request->user()->id,
            $validated['answers']
        );

        return back()->with('success', 'আপনার উত্তরপত্র জমা হয়েছে।');
    }

    /**
     * Teacher / examiner grades a student submission (manual oral/written score).
     */
    public function grade(Request $request, mixed $organization, ExamSubmission $submission): RedirectResponse
    {
        $tenant = TenantContext::getTenant();
        $exam = $submission->exam;
        if (! $exam || $exam->organization_id !== $tenant->id) {
            abort(403, 'এই পরীক্ষার খাতা দেখার বা মূল্যায়ন করার অনুমতি নেই।');
        }

        $validated = $request->validate([
            'manual_score' => 'required|numeric|min:0',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $this->lmsService->evaluateSubmission(
            $exam,
            $submission->user_id,
            $submission->answers ?? [],
            (float) $validated['manual_score'],
            $request->user()->id,
            $validated['feedback'] ?? null
        );

        return back()->with('success', 'মূল্যায়ন সফলভাবে সম্পন্ন ও ফলাফল আপডেট হয়েছে।');
    }
}
