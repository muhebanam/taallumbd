<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Page;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ScholarTrustAndGovernanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_scholar_verification_checklist(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Teacher::factory()->create(['is_verified' => false]);

        $this->actingAs($admin)
            ->post("/admin/teachers/{$teacher->slug}/verification/checklist", [
                'checklist' => [
                    'dawra_certificate' => true,
                    'ijazah_accreditation' => true,
                ],
                'notes' => 'সনদপত্র ও ইজাজাহ মূল কপি যাচাইকৃত।',
            ])
            ->assertRedirect();

        $teacher->refresh();
        $this->assertTrue($teacher->verification_checklist['dawra_certificate']);
        $this->assertEquals('সনদপত্র ও ইজাজাহ মূল কপি যাচাইকৃত।', $teacher->verification_notes);
    }

    public function test_admin_can_upload_private_verification_document(): void
    {
        Storage::fake('local');
        $admin = User::factory()->admin()->create();
        $teacher = Teacher::factory()->create();

        $file = UploadedFile::fake()->create('dawra_sanad.pdf', 500, 'application/pdf');

        $this->actingAs($admin)
            ->post("/admin/teachers/{$teacher->slug}/verification/document", [
                'title' => 'দাওরায়ে হাদীস মূল সনদ',
                'document' => $file,
            ])
            ->assertRedirect();

        $teacher->refresh();
        $this->assertCount(1, $teacher->verification_documents);
        $this->assertEquals('দাওরায়ে হাদীস মূল সনদ', $teacher->verification_documents[0]['title']);
    }

    public function test_admin_can_verify_and_unverify_scholar(): void
    {
        $admin = User::factory()->admin()->create();
        $teacher = Teacher::factory()->create(['is_verified' => false]);

        // Verify
        $this->actingAs($admin)
            ->post("/admin/teachers/{$teacher->slug}/verification/verify")
            ->assertRedirect();

        $teacher->refresh();
        $this->assertTrue($teacher->is_verified);
        $this->assertNotNull($teacher->verified_at);
        $this->assertEquals($admin->id, $teacher->verified_by);

        // Unverify
        $this->actingAs($admin)
            ->post("/admin/teachers/{$teacher->slug}/unverify")
            ->assertRedirect();

        $teacher->refresh();
        $this->assertFalse($teacher->is_verified);
        $this->assertNull($teacher->verified_at);
    }

    public function test_scholar_board_public_page_lists_verified_scholars(): void
    {
        $verifiedTeacher = Teacher::factory()->create([
            'is_verified' => true,
            'status' => 'active',
            'name' => 'মাওলানা আব্দুল্লাহ আল-কাফী',
        ]);

        $response = $this->get('/scholars/board');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('About/ScholarBoard')
            ->has('scholars')
        );
    }

    public function test_certificate_verification_logs_audit_attempt(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        $certificate = Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'certificate_no' => 'CERT-AUDIT-001',
        ]);

        $this->get("/verify/{$certificate->uuid}")
            ->assertStatus(200);

        $this->assertDatabaseHas('certificate_verifications', [
            'certificate_id' => $certificate->id,
            'identifier_searched' => $certificate->uuid,
            'status' => 'valid',
        ]);
    }

    public function test_admin_can_revoke_compromised_certificate(): void
    {
        $admin = User::factory()->admin()->create();
        $certificate = Certificate::factory()->create([
            'revoked_at' => null,
        ]);

        $this->actingAs($admin)
            ->post("/admin/certificates/{$certificate->id}/revoke", [
                'reason' => 'তথ্য জালিয়াতি ও পরীক্ষায় অসদুপায় অবলম্বন।',
            ])
            ->assertRedirect();

        $certificate->refresh();
        $this->assertTrue($certificate->isRevoked());
        $this->assertEquals('তথ্য জালিয়াতি ও পরীক্ষায় অসদুপায় অবলম্বন।', $certificate->revoked_reason);
        $this->assertEquals($admin->id, $certificate->revoked_by);
    }

    public function test_revoked_certificate_verification_returns_revoked_state(): void
    {
        $admin = User::factory()->admin()->create();
        $certificate = Certificate::factory()->create([
            'revoked_at' => now(),
            'revoked_reason' => 'নীতিমালা লঙ্ঘনের দায়ে বাতিলকৃত',
            'revoked_by' => $admin->id,
        ]);

        $response = $this->get("/verify/{$certificate->uuid}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('certificate_verifications', [
            'certificate_id' => $certificate->id,
            'status' => 'revoked',
        ]);
    }

    public function test_policy_pages_are_accessible_publicly(): void
    {
        Page::create([
            'title' => 'ব্যবহারের শর্তাবলি',
            'slug' => 'terms',
            'body' => 'আত-তাআল্লুম প্ল্যাটফর্মের বিস্তারিত ব্যবহারের শর্তাবলি...',
            'is_published' => true,
        ]);

        $response = $this->get('/policy/terms');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Policy/Show')
            ->where('page.slug', 'terms')
            ->where('page.title', 'ব্যবহারের শর্তাবলি')
        );
    }

    public function test_admin_can_edit_policy_page(): void
    {
        $admin = User::factory()->admin()->create();
        $page = Page::create([
            'title' => 'রিফান্ড পলিসি',
            'slug' => 'refund',
            'body' => 'প্রাথমিক রিফান্ড নীতিমালা।',
            'is_published' => true,
        ]);

        $this->actingAs($admin)
            ->put("/admin/pages/{$page->slug}", [
                'title' => 'হালনাগাদ রিফান্ড ও প্রত্যাহার নীতিমালা',
                'body' => 'ভর্তির ৩ দিনের মধ্যে ১০০% রিফান্ড প্রযোজ্য।',
                'meta_title' => 'রিফান্ড নীতিমালা — আত-তাআল্লুম',
                'meta_description' => 'আত-তাআল্লুম প্ল্যাটফর্মে রিফান্ড সংক্রান্ত বিস্তারিত গাইডলাইন।',
                'is_published' => true,
            ])
            ->assertRedirect('/admin/pages');

        $page->refresh();
        $this->assertEquals('হালনাগাদ রিফান্ড ও প্রত্যাহার নীতিমালা', $page->title);
        $this->assertEquals($admin->id, $page->last_updated_by);
    }
}
