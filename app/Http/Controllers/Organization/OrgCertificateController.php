<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use App\Models\OrganizationCertificate;
use App\Models\OrganizationMember;
use App\Services\EnterpriseLmsService;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrgCertificateController extends Controller
{
    public function __construct(
        protected EnterpriseLmsService $lmsService
    ) {}

    /**
     * List organization certificates.
     */
    public function index(): Response
    {
        $certificates = OrganizationCertificate::with(['student', 'cohort'])
            ->latest('issued_date')
            ->paginate(15);

        $cohorts = Cohort::where('status', 'active')->get(['id', 'name']);
        $students = OrganizationMember::where('role', 'student')
            ->with('user')
            ->get()
            ->map(fn ($m) => ['id' => $m->user_id, 'name' => $m->user?->name]);

        $tenant = TenantContext::getTenant();

        return Inertia::render('Organization/Certificates', [
            'certificates' => $certificates,
            'cohorts' => $cohorts,
            'students' => $students,
            'branding' => $tenant->branding ?? [],
        ]);
    }

    /**
     * Issue certificates (single student or bulk cohort).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'cohort_id' => 'nullable|exists:cohorts,id',
            'student_id' => 'nullable|exists:users,id',
            'signers' => 'nullable|string|max:255',
        ]);

        $tenant = TenantContext::getTenant();

        $metadata = [
            'signers' => $validated['signers'] ?? 'প্রিন্সিপাল ও বিভাগীয় প্রধান',
        ];

        // Bulk issue for entire cohort
        if (! empty($validated['cohort_id']) && empty($validated['student_id'])) {
            $cohort = Cohort::with('students')->findOrFail($validated['cohort_id']);
            $count = 0;
            foreach ($cohort->students as $student) {
                $this->lmsService->issueCertificate(
                    $tenant,
                    $student->id,
                    $validated['title'],
                    $cohort->id,
                    null,
                    $metadata
                );
                $count++;
            }

            return back()->with('success', "কোহর্টের {$count} জন শিক্ষার্থীর জন্য বাল্ক সার্টিফিকেট সফলভাবে ইস্যু করা হয়েছে।");
        }

        // Single student issue
        if (! empty($validated['student_id'])) {
            $this->lmsService->issueCertificate(
                $tenant,
                (int) $validated['student_id'],
                $validated['title'],
                $validated['cohort_id'] ? (int) $validated['cohort_id'] : null,
                null,
                $metadata
            );

            return back()->with('success', 'সার্টিফিকেট সফলভাবে প্রদান করা হয়েছে।');
        }

        return back()->withErrors(['student_id' => 'শিক্ষার্থী অথবা শ্রেণি নির্বাচন করুন।']);
    }
}
