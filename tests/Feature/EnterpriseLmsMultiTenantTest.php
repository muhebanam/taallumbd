<?php

namespace Tests\Feature;

use App\Models\Cohort;
use App\Models\Exam;
use App\Models\Organization;
use App\Models\User;
use App\Services\EnterpriseLmsService;
use App\Tenancy\TenantContext;
use Database\Seeders\EnterpriseOrganizationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EnterpriseLmsMultiTenantTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $org1;

    protected Organization $org2;

    protected User $admin1;

    protected User $teacher1;

    protected User $student1;

    protected User $student2;

    protected User $guardian1;

    protected User $admin2;

    protected User $outsider;

    protected EnterpriseLmsService $lmsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lmsService = app(EnterpriseLmsService::class);

        // Seed organizations
        $this->seed(EnterpriseOrganizationSeeder::class);

        $this->org1 = Organization::where('subdomain', 'darululoom')->firstOrFail();
        $this->org2 = Organization::where('subdomain', 'markaz')->firstOrFail();

        $this->admin1 = User::where('email', 'admin@darululoom.edu.bd')->firstOrFail();
        $this->teacher1 = User::where('email', 'teacher@darululoom.edu.bd')->firstOrFail();
        $this->student1 = User::where('email', 'student1@darululoom.edu.bd')->firstOrFail();
        $this->student2 = User::where('email', 'student2@darululoom.edu.bd')->firstOrFail();
        $this->guardian1 = User::where('email', 'guardian@darululoom.edu.bd')->firstOrFail();
        $this->admin2 = User::where('email', 'admin@markaz.edu.bd')->firstOrFail();

        $this->outsider = User::factory()->create([
            'email' => 'outsider@example.com',
            'role' => 'student',
        ]);
    }

    /**
     * Test tenant resolution via route parameter.
     */
    public function test_tenant_resolution_via_route_parameter(): void
    {
        $response = $this->actingAs($this->admin1)
            ->get("/org/{$this->org1->subdomain}/dashboard");

        $response->assertStatus(200);
        $this->assertEquals($this->org1->id, TenantContext::getTenantId());
    }

    /**
     * Test tenant resolution via custom header.
     */
    public function test_tenant_resolution_via_header(): void
    {
        $response = $this->actingAs($this->admin1)
            ->withHeader('X-Organization-Subdomain', 'darululoom')
            ->get("/org/{$this->org1->subdomain}/dashboard");

        $response->assertStatus(200);
        $this->assertEquals($this->org1->id, TenantContext::getTenantId());
    }

    /**
     * Test unknown tenant returns 404.
     */
    public function test_unknown_tenant_returns_404(): void
    {
        $response = $this->actingAs($this->admin1)
            ->get('/org/unknown-madrasah-xyz/dashboard');

        $response->assertStatus(404);
    }

    /**
     * Test membership authorization: non-member cannot access organization dashboard.
     */
    public function test_non_member_cannot_access_tenant(): void
    {
        $response = $this->actingAs($this->outsider)
            ->get("/org/{$this->org1->subdomain}/dashboard");

        $response->assertStatus(403);
    }

    /**
     * Test role enforcement: student cannot access staff/admin members list.
     */
    public function test_student_cannot_access_admin_sections(): void
    {
        $response = $this->actingAs($this->student1)
            ->get("/org/{$this->org1->subdomain}/members");

        $response->assertStatus(403);
    }

    /**
     * Test seat limit enforcement: cannot add member beyond seat limit.
     */
    public function test_seat_limit_prevents_unauthorized_member_addition(): void
    {
        // Set seat limit equal to current used seats
        $this->org1->update(['seat_limit' => $this->org1->used_seats]);

        $this->expectException(ValidationException::class);

        $this->lmsService->addMember($this->org1, [
            'name' => 'নতুন শিক্ষার্থী',
            'email' => 'newstudent@example.com',
        ], 'student');
    }

    /**
     * Test CSV bulk import validates and blocks when seats would be exceeded.
     */
    public function test_csv_bulk_import_enforces_seat_limit(): void
    {
        $this->org1->update(['seat_limit' => $this->org1->used_seats + 1]);

        $csv = "name,email,role\nStudent A,a@test.com,student\nStudent B,b@test.com,student\n";

        $this->expectException(ValidationException::class);
        $this->lmsService->bulkImportMembers($this->org1, $csv, 'student');
    }

    /**
     * Test CRITICAL cross-tenant isolation: Org 1 context never sees Org 2 cohorts.
     */
    public function test_strict_cross_tenant_isolation_for_cohorts(): void
    {
        // 1. Set context to Org 1
        TenantContext::setTenant($this->org1);
        $org1Cohorts = Cohort::all();

        $this->assertGreaterThan(0, $org1Cohorts->count());
        foreach ($org1Cohorts as $cohort) {
            $this->assertEquals($this->org1->id, $cohort->organization_id);
            $this->assertNotEquals($this->org2->id, $cohort->organization_id);
        }

        // 2. Switch context to Org 2
        TenantContext::setTenant($this->org2);
        $org2Cohorts = Cohort::all();

        $this->assertGreaterThan(0, $org2Cohorts->count());
        foreach ($org2Cohorts as $cohort) {
            $this->assertEquals($this->org2->id, $cohort->organization_id);
            $this->assertNotEquals($this->org1->id, $cohort->organization_id);
        }

        TenantContext::clear();
    }

    /**
     * Test cross-tenant tamper protection: Org 2 admin cannot grade Org 1 exam submission.
     */
    public function test_cross_tenant_tamper_protection(): void
    {
        $org1Exam = Exam::withoutGlobalScopes()->where('organization_id', $this->org1->id)->firstOrFail();
        $submission = $org1Exam->submissions()->firstOrFail();

        // Org 2 admin attempts to grade Org 1 exam via Org 2 URL route
        $response = $this->actingAs($this->admin2)
            ->post("/org/{$this->org2->subdomain}/exams/submissions/{$submission->id}/grade", [
                'manual_score' => 99,
                'feedback' => 'Hacked grade',
            ]);

        // Must be rejected with 403 Forbidden
        $response->assertStatus(403);
    }

    /**
     * Test automated MCQ evaluation and traditional Islamic grading scale.
     */
    public function test_exam_evaluation_and_islamic_grade_calculation(): void
    {
        TenantContext::setTenant($this->org1);

        $exam = Exam::create([
            'organization_id' => $this->org1->id,
            'created_by' => $this->teacher1->id,
            'title' => 'আকিদা ও ফিকহ মূল্যায়ন',
            'exam_type' => 'quiz_mcq',
            'duration_minutes' => 30,
            'total_marks' => 100,
            'pass_marks' => 40,
            'question_bank' => [
                ['id' => 'q1', 'question' => 'ইসলামের রুকন কয়টি?', 'correct_answer' => '৫', 'points' => 50],
                ['id' => 'q2', 'question' => 'ঈমানের রুকন কয়টি?', 'correct_answer' => '৬', 'points' => 50],
            ],
            'status' => 'published',
        ]);

        // Student submits correct answers (100% -> Mumtaz)
        $sub = $this->lmsService->evaluateSubmission($exam, $this->student1->id, [
            'q1' => '৫',
            'q2' => '৬',
        ]);

        $this->assertEquals(100.0, $sub->total_score);
        $this->assertEquals(100.0, $sub->percentage);
        $this->assertStringContainsString('মুমতায', $sub->grade);

        // Grade scale helper tests
        $this->assertStringContainsString('মুমতায', $this->lmsService->calculateIslamicGrade(92.0));
        $this->assertStringContainsString('জায়্যিদ জিদ্দান', $this->lmsService->calculateIslamicGrade(85.0));
        $this->assertStringContainsString('জায়্যিদ', $this->lmsService->calculateIslamicGrade(72.0));
        $this->assertStringContainsString('মাকবুল', $this->lmsService->calculateIslamicGrade(55.0));
        $this->assertStringContainsString('রাসিব', $this->lmsService->calculateIslamicGrade(35.0));

        TenantContext::clear();
    }

    /**
     * Test report card generation and class rank assignment.
     */
    public function test_cohort_report_card_generation_and_class_rank(): void
    {
        $cohort = Cohort::withoutGlobalScopes()->where('organization_id', $this->org1->id)->firstOrFail();

        $results = $this->lmsService->generateCohortReportCards($this->org1, $cohort->id, 'ষান্মাসিক পরীক্ষা ১৪৪৭');

        $this->assertCount(2, $results);

        $rank1 = $results->firstWhere('position_in_class', 1);
        $rank2 = $results->firstWhere('position_in_class', 2);

        $this->assertNotNull($rank1);
        $this->assertNotNull($rank2);
        $this->assertGreaterThanOrEqual($rank2->overall_percentage, $rank1->overall_percentage);
    }

    /**
     * Test Guardian portal: parent only sees their own child's progress.
     */
    public function test_guardian_portal_scoped_to_wards_only(): void
    {
        $response = $this->actingAs($this->guardian1)
            ->get("/org/{$this->org1->subdomain}/guardian");

        $response->assertStatus(200);

        // In Inertia page props, guardian should see student1 (their ward), but not student2
        $response->assertInertia(function ($page) {
            $page->component('Organization/Guardian')
                ->has('wards', 1)
                ->where('wards.0.id', $this->student1->id);
        });
    }
}
