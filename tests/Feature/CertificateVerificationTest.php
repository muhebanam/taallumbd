<?php

namespace Tests\Feature;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CertificateVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_verify_certificate_by_uuid(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        $certificate = Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'certificate_no' => 'CERT-2026-001',
        ]);

        $response = $this->get(route('certificates.verify', $certificate->uuid));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Certificate/Verify')
            ->where('isValid', true)
            ->where('certificate.id', $certificate->id)
            ->where('certificate.certificate_no', 'CERT-2026-001')
        );
    }

    public function test_can_verify_certificate_by_certificate_number(): void
    {
        $student = User::factory()->student()->create();
        $course = Course::factory()->published()->create();
        $certificate = Certificate::factory()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'certificate_no' => 'CERT-2026-999',
        ]);

        $response = $this->get(route('certificates.verify', 'CERT-2026-999'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Certificate/Verify')
            ->where('isValid', true)
            ->where('certificate.id', $certificate->id)
        );
    }

    public function test_invalid_identifier_returns_not_valid(): void
    {
        $response = $this->get(route('certificates.verify', 'INVALID-CERT-UUID-9999'));

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Certificate/Verify')
            ->where('isValid', false)
            ->where('certificate', null)
        );
    }

    public function test_certificate_model_generates_valid_urls(): void
    {
        $certificate = Certificate::factory()->create();

        $this->assertStringContainsString('/verify/'.$certificate->uuid, $certificate->verification_url);
        $this->assertStringContainsString('api.qrserver.com', $certificate->qr_code_url);
    }
}
