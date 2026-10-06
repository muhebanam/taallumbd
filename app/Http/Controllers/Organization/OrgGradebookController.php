<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\GradeBook;
use App\Services\EnterpriseLmsService;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrgGradebookController extends Controller
{
    public function __construct(
        protected EnterpriseLmsService $lmsService
    ) {}

    /**
     * Display report cards and class ranking for a cohort and term.
     */
    public function index(Request $request): Response
    {
        $tenant = TenantContext::getTenant();

        $cohorts = Cohort::where('status', 'active')->get(['id', 'name']);
        $selectedCohortId = $request->query('cohort_id') ?: $cohorts->first()?->id;
        $term = $request->query('term') ?: 'ষান্মাসিক পরীক্ষা ১৪৪৭';

        $gradeBooks = [];
        if ($selectedCohortId) {
            $gradeBooks = GradeBook::with(['student'])
                ->where('cohort_id', $selectedCohortId)
                ->where('term', $term)
                ->orderBy('position_in_class')
                ->get();
        }

        return Inertia::render('Organization/Gradebook', [
            'cohorts' => $cohorts,
            'selected_cohort_id' => $selectedCohortId ? (int) $selectedCohortId : null,
            'selected_term' => $term,
            'grade_books' => $gradeBooks,
        ]);
    }

    /**
     * Generate report cards with Islamic grading and class ranking.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cohort_id' => 'required|exists:cohorts,id',
            'term' => 'required|string|max:120',
        ]);

        $tenant = TenantContext::getTenant();

        $results = $this->lmsService->generateCohortReportCards(
            $tenant,
            (int) $validated['cohort_id'],
            $validated['term']
        );

        return back()->with('success', "মোট {$results->count()} জন শিক্ষার্থীর জন্য ফলাফল ও গ্রেডবুক তৈরি হয়েছে।");
    }
}
