<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\Course;
use App\Models\OrganizationMember;
use App\Services\EnterpriseLmsService;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrgCohortController extends Controller
{
    public function __construct(
        protected EnterpriseLmsService $lmsService
    ) {}

    /**
     * Display a listing of cohorts.
     */
    public function index(): Response
    {
        $cohorts = Cohort::with(['headTeacher'])
            ->withCount(['students', 'courses'])
            ->latest()
            ->paginate(15);

        $teachers = OrganizationMember::where('role', 'teacher')
            ->with('user')
            ->get()
            ->map(fn ($m) => ['id' => $m->user_id, 'name' => $m->user?->name]);

        return Inertia::render('Organization/Cohorts', [
            'cohorts' => $cohorts,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Store a new cohort.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year' => 'nullable|string|max:64',
            'head_teacher_id' => 'nullable|exists:users,id',
            'room_number' => 'nullable|string|max:64',
            'description' => 'nullable|string',
        ]);

        $tenant = TenantContext::getTenant();
        $this->lmsService->createCohort($tenant, $validated);

        return back()->with('success', 'নতুন শ্রেণি / হালাকা সফলভাবে খোলা হয়েছে।');
    }

    /**
     * Assign a student or teacher to a cohort.
     */
    public function assignMember(Request $request, mixed $organization, Cohort $cohort): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:student,teacher',
            'roll_number' => 'nullable|string|max:64',
        ]);

        $tenant = TenantContext::getTenant();
        if ($cohort->organization_id !== $tenant->id) {
            abort(403);
        }

        $this->lmsService->assignCohortMember(
            $cohort,
            (int) $validated['user_id'],
            $validated['role'],
            $validated['roll_number'] ?? null
        );

        return back()->with('success', 'সদস্যকে সফলভাবে শ্রেণিতে যুক্ত করা হয়েছে।');
    }

    /**
     * Map a course to the cohort.
     */
    public function assignCourse(Request $request, mixed $organization, Cohort $cohort): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'assigned_teacher_id' => 'nullable|exists:users,id',
            'is_mandatory' => 'boolean',
        ]);

        $tenant = TenantContext::getTenant();
        if ($cohort->organization_id !== $tenant->id) {
            abort(403);
        }

        $this->lmsService->assignCourseToCohort(
            $cohort,
            (int) $validated['course_id'],
            $validated['assigned_teacher_id'] ? (int) $validated['assigned_teacher_id'] : null,
            (bool) ($validated['is_mandatory'] ?? true)
        );

        return back()->with('success', 'কোর্স সফলভাবে শ্রেণির সাথে সংযুক্ত করা হয়েছে।');
    }
}
