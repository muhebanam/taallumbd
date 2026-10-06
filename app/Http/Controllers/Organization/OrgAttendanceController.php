<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Cohort;
use App\Services\EnterpriseLmsService;
use App\Tenancy\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrgAttendanceController extends Controller
{
    public function __construct(
        protected EnterpriseLmsService $lmsService
    ) {}

    /**
     * Display attendance sheet for a cohort and date.
     */
    public function index(Request $request): Response
    {
        $tenant = TenantContext::getTenant();

        $cohorts = Cohort::where('status', 'active')->get(['id', 'name']);
        $selectedCohortId = $request->query('cohort_id') ?: $cohorts->first()?->id;
        $date = $request->query('date') ?: now()->toDateString();
        $session = $request->query('session') ?: 'daily';

        $studentsWithAttendance = [];
        $selectedCohort = null;

        if ($selectedCohortId) {
            $selectedCohort = Cohort::with(['students'])->find($selectedCohortId);

            if ($selectedCohort) {
                // Fetch existing attendance logs for this cohort on this date & session
                $existingLogs = Attendance::where('cohort_id', $selectedCohort->id)
                    ->where('date', $date)
                    ->where('session_name', $session)
                    ->get()
                    ->keyBy('user_id');

                foreach ($selectedCohort->students as $student) {
                    $log = $existingLogs->get($student->id);
                    $studentsWithAttendance[] = [
                        'user_id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'roll_number' => $student->pivot?->roll_number,
                        'status' => $log ? $log->status : 'present',
                        'remarks' => $log ? $log->remarks : '',
                    ];
                }
            }
        }

        return Inertia::render('Organization/Attendance', [
            'cohorts' => $cohorts,
            'selected_cohort_id' => $selectedCohortId ? (int) $selectedCohortId : null,
            'selected_date' => $date,
            'selected_session' => $session,
            'students' => $studentsWithAttendance,
            'summary' => [
                'total' => count($studentsWithAttendance),
                'present' => count(array_filter($studentsWithAttendance, fn ($s) => $s['status'] === 'present')),
                'absent' => count(array_filter($studentsWithAttendance, fn ($s) => $s['status'] === 'absent')),
                'late' => count(array_filter($studentsWithAttendance, fn ($s) => $s['status'] === 'late')),
            ],
        ]);
    }

    /**
     * Save attendance batch for a cohort.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cohort_id' => 'required|exists:cohorts,id',
            'date' => 'required|date',
            'session_name' => 'required|string|max:64',
            'attendance' => 'required|array',
            'attendance.*.user_id' => 'required|exists:users,id',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        $tenant = TenantContext::getTenant();

        $count = $this->lmsService->recordAttendanceBatch(
            $tenant,
            (int) $validated['cohort_id'],
            $validated['date'],
            $validated['session_name'],
            $validated['attendance'],
            $request->user()->id
        );

        return back()->with('success', "{$count} জন শিক্ষার্থীর উপস্থিতি সফলভাবে সংরক্ষণ করা হয়েছে।");
    }
}
