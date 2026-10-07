<?php

namespace Tests\Feature;

use App\AI\Services\PromptGuard;
use Tests\TestCase;

class SecurityHeadersAndHardeningTest extends TestCase
{
    public function test_security_headers_are_present_in_responses(): void
    {
        $response = $this->get('/health');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
    }

    public function test_prompt_guard_scrubs_sensitive_pii(): void
    {
        $guard = app(PromptGuard::class);

        $input = 'আমার নাম রাফি, আমার ইমেইল rafi@example.com এবং ফোন নম্বর 01712345678। কার্ড নম্বর 1234-5678-9012-3456।';
        $scrubbed = $guard->scrubPii($input);

        $this->assertStringNotContainsString('rafi@example.com', $scrubbed);
        $this->assertStringNotContainsString('01712345678', $scrubbed);
        $this->assertStringNotContainsString('1234-5678-9012-3456', $scrubbed);
        $this->assertStringContainsString('[EMAIL_REDACTED]', $scrubbed);
        $this->assertStringContainsString('[PHONE_REDACTED]', $scrubbed);
        $this->assertStringContainsString('[CARD_REDACTED]', $scrubbed);
    }
}
