<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class ProductionConfigTest extends TestCase
{
    public function test_session_cookies_are_http_only_and_configured_with_same_site(): void
    {
        $this->assertTrue(
            config('session.http_only'),
            'Session cookie must have http_only set to true to prevent XSS session hijacking.'
        );

        $sameSite = config('session.same_site');
        $this->assertContains(
            $sameSite,
            ['lax', 'strict', 'none'],
            'Session cookie same_site must be configured.'
        );
    }

    public function test_security_headers_are_applied_to_web_responses(): void
    {
        $response = $this->get('/health');
        $response->assertOk();

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_hsts_is_enforced_in_production_environment(): void
    {
        config(['app.env' => 'production']);

        $response = $this->get('/health');
        $response->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_debug_mode_must_be_disabled_in_production(): void
    {
        if (config('app.env') === 'production') {
            $this->assertFalse(
                config('app.debug'),
                'APP_DEBUG must be false in production environments to avoid leaking stack traces and environment credentials.'
            );
        } else {
            $this->assertTrue(true);
        }
    }
}
