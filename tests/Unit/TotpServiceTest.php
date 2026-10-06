<?php

namespace Tests\Unit;

use App\Services\TotpService;
use PHPUnit\Framework\TestCase;

class TotpServiceTest extends TestCase
{
    public function test_generate_secret_returns_16_char_base32(): void
    {
        $secret = TotpService::generateSecret();
        $this->assertSame(16, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]{16}$/', $secret);
    }

    public function test_calculate_code_returns_six_digit_string(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $code = TotpService::calculateCode($secret, 1234567890);
        $this->assertSame(6, strlen($code));
        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
    }

    public function test_verify_code_succeeds_with_current_timestamp(): void
    {
        $secret = TotpService::generateSecret();
        $time = time();
        $code = TotpService::calculateCode($secret, $time);

        $this->assertTrue(TotpService::verifyCode($secret, $code, 1, $time));
        $this->assertFalse(TotpService::verifyCode($secret, '000000', 1, $time));
    }

    public function test_verify_code_succeeds_within_drift_window(): void
    {
        $secret = TotpService::generateSecret();
        $time = time();
        $previousCode = TotpService::calculateCode($secret, $time - 30);

        $this->assertTrue(TotpService::verifyCode($secret, $previousCode, 1, $time));
    }

    public function test_generate_recovery_codes_returns_eight_formatted_codes(): void
    {
        $codes = TotpService::generateRecoveryCodes(8);
        $this->assertCount(8, $codes);
        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^[A-F0-9]{4}-[A-F0-9]{4}$/', $code);
        }
    }
}
