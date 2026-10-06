<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_login_does_not_require_two_factor_setup(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $student->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($student);
    }

    public function test_admin_without_two_factor_is_redirected_to_setup(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('adminpass123'),
        ]);

        // Attempt accessing admin route
        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertRedirect(route('two-factor.setup'));
    }

    public function test_admin_can_view_two_factor_setup_page_and_generate_secret(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get(route('two-factor.setup'));

        $response->assertOk();
        $admin->refresh();
        $this->assertNotEmpty($admin->two_factor_secret);
        $this->assertNotEmpty($admin->two_factor_recovery_codes);
    }

    public function test_admin_can_confirm_two_factor_with_valid_totp_code(): void
    {
        $secret = TotpService::generateSecret();
        $admin = User::factory()->create([
            'role' => 'admin',
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
        ]);

        $validCode = TotpService::calculateCode($secret);

        $response = $this->actingAs($admin)->post(route('two-factor.confirm'), [
            'code' => $validCode,
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $admin->refresh();
        $this->assertNotNull($admin->two_factor_confirmed_at);
        $this->assertTrue(session('two_factor_verified'));
    }

    public function test_admin_with_active_two_factor_must_pass_challenge(): void
    {
        $secret = TotpService::generateSecret();
        $admin = User::factory()->create([
            'role' => 'admin',
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ]);

        // Without two_factor_verified session
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertRedirect(route('two-factor.challenge'));

        // Submit valid TOTP code
        $validCode = TotpService::calculateCode($secret);
        $verifyResponse = $this->actingAs($admin)->post(route('two-factor.verify'), [
            'code' => $validCode,
        ]);

        $verifyResponse->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('two_factor_verified'));

        // Now admin can access /admin/dashboard
        $adminAccessResponse = $this->actingAs($admin)->get('/admin/dashboard');
        $adminAccessResponse->assertOk();
    }

    public function test_admin_can_verify_using_recovery_code(): void
    {
        $secret = TotpService::generateSecret();
        $recoveryCodes = ['ABCD-1234', 'EFGH-5678'];
        $admin = User::factory()->create([
            'role' => 'admin',
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('two-factor.verify'), [
            'recovery_code' => 'ABCD-1234',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertTrue(session('two_factor_verified'));

        $admin->refresh();
        $this->assertNotContains('ABCD-1234', $admin->two_factor_recovery_codes);
        $this->assertContains('EFGH-5678', $admin->two_factor_recovery_codes);
    }
}
