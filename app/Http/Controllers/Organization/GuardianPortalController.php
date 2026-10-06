<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ExamSubmission;
use App\Models\GradeBook;
use App\Models\OrganizationMember;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuardianPortalController extends Controller
{
    /**
     * Display guardian portal showing wards' progress, attendance & grades.
     */
    public function index(Request $request): Response
    {
        $tenant = TenantContext::getTenant();
        $user = $request->user();

        // Find all students for whom the logged-in user is guardian in this tenant
        $wardsMemberships = OrganizationMember::with(['user'])
            ->where('organization_id', $tenant->id)
            ->where('guardian_user_id', $user->id)
            ->where('role', 'student')
            ->get();

        $selectedStudentId = $request->query('student_id') ?: $wardsMemberships->first()?->user_id;
        $selectedWard = $wardsMemberships->firstWhere('user_id', $selectedStudentId);

        $attendanceStats = null;
        $recentExams = [];
        $gradeBooks = [];

        if ($selectedWard) {
            $totalAttendance = Attendance::where('user_id', $selectedWard->user_id)->count();
            $presentAttendance = Attendance::where('user_id', $selectedWard->user_id)->where('status', 'present')->count();

            $attendanceStats = [
                'total' => $totalAttendance,
                'present' => $presentAttendance,
                'rate' => $totalAttendance > 0 ? round(($presentAttendance / $totalAttendance) * 100, 1) : 0,
            ];

            $recentExams = ExamSubmission::with(['exam'])
                ->where('user_id', $selectedWard->user_id)
                ->latest()
                ->take(5)
                ->get();

            $gradeBooks = GradeBook::with(['cohort'])
                ->where('user_id', $selectedWard->user_id)
                ->where('is_published', true)
                ->latest()
                ->get();
        }

        return Inertia::render('Organization/Guardian', [
            'wards' => $wardsMemberships->map(fn ($m) => [
                'id' => $m->user_id,
                'name' => $m->user?->name,
                'email' => $m->user?->email,
                'id_number' => $m->id_number,
            ]),
            'selected_student_id' => $selectedStudentId ? (int) $selectedStudentId : null,
            'selected_ward' => $selectedWard ? [
                'id' => $selectedWard->user_id,
                'name' => $selectedWard->user?->name,
                'id_number' => $selectedWard->id_number,
            ] : null,
            'attendance_stats' => $attendanceStats,
            'recent_exams' => $recentExams,
            'grade_books' => $gradeBooks,
        ]);
    }
}
