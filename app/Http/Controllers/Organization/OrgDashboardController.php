<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Cohort;
use App\Models\Exam;
use App\Models\OrganizationMember;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrgDashboardController extends Controller
{
    /**
     * Display the Organization Admin Dashboard.
     */
    public function index(Request $request): Response
    {
        $tenant = TenantContext::getTenant();

        // Calculate statistics within tenant scope
        $totalStudents = OrganizationMember::where('role', 'student')->where('status', 'active')->count();
        $totalTeachers = OrganizationMember::where('role', 'teacher')->where('status', 'active')->count();
        $totalGuardians = OrganizationMember::where('role', 'guardian')->where('status', 'active')->count();
        $activeCohorts = Cohort::where('status', 'active')->count();
        $recentExams = Exam::latest()->take(5)->get();

        // Today's attendance percentage
        $todayAttendanceTotal = Attendance::where('date', now()->toDateString())->count();
        $todayPresent = Attendance::where('date', now()->toDateString())->where('status', 'present')->count();
        $attendanceRate = $todayAttendanceTotal > 0 ? round(($todayPresent / $todayAttendanceTotal) * 100, 1) : 0;

        $cohortsList = Cohort::withCount(['students', 'courses'])->latest()->take(6)->get();

        return Inertia::render('Organization/Dashboard', [
            'organization' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'subdomain' => $tenant->subdomain,
                'type' => $tenant->type,
                'plan' => $tenant->plan,
                'seat_limit' => $tenant->seat_limit,
                'used_seats' => $tenant->used_seats,
                'branding' => $tenant->branding ?? [],
                'status' => $tenant->status,
            ],
            'stats' => [
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
                'total_guardians' => $totalGuardians,
                'active_cohorts' => $activeCohorts,
                'seat_limit' => $tenant->seat_limit,
                'used_seats' => $tenant->used_seats,
                'remaining_seats' => max(0, $tenant->seat_limit - $tenant->used_seats),
                'seat_usage_percentage' => $tenant->seat_limit > 0 ? round(($tenant->used_seats / $tenant->seat_limit) * 100, 1) : 0,
                'attendance_rate' => $attendanceRate,
                'today_attendance_marked' => $todayAttendanceTotal,
            ],
            'recent_cohorts' => $cohortsList,
            'recent_exams' => $recentExams,
        ]);
    }
}
