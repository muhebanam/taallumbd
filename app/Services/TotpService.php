<?php

namespace App\Services;

class TotpService
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate a cryptographically secure random Base32 secret key.
     */
    public static function generateSecret(int $length = 16): string
    {
        $secret = '';
        $alphabet = self::BASE32_CHARS;
        $maxIndex = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, $maxIndex)];
        }

        return $secret;
    }

    /**
     * Calculate TOTP code for a secret at a given timestamp.
     */
    public static function calculateCode(string $secret, ?int $timestamp = null, int $timeStep = 30): string
    {
        $timestamp = $timestamp ?? time();
        $binarySecret = self::base32Decode($secret);
        $timeSlice = (int) floor($timestamp / $timeStep);

        $binaryTime = pack('N*', 0).pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $binaryTime, $binarySecret, true);

        $offset = ord(substr($hash, -1)) & 0x0F;
        $hashPart = substr($hash, $offset, 4);

        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        $modulo = 10 ** 6;

        return str_pad((string) ($value % $modulo), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verify a 6-digit TOTP code with time window tolerance.
     */
    public static function verifyCode(string $secret, string $code, int $discrepancy = 1, ?int $timestamp = null): bool
    {
        $timestamp = $timestamp ?? time();
        $code = trim($code);

        if (strlen($code) !== 6 || ! ctype_digit($code)) {
            return false;
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculated = self::calculateCode($secret, $timestamp + ($i * 30));
            if (hash_equals($calculated, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate an otpauth:// URI for QR code generation.
     */
    public static function getQrCodeUri(string $companyName, string $accountName, string $secret): string
    {
        $issuer = rawurlencode($companyName);
        $account = rawurlencode($accountName);

        return "otpauth://totp/{$issuer}:{$account}?secret={$secret}&issuer={$issuer}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Generate random recovery codes.
     */
    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $part1 = strtoupper(bin2hex(random_bytes(2)));
            $part2 = strtoupper(bin2hex(random_bytes(2)));
            $codes[] = "{$part1}-{$part2}";
        }

        return $codes;
    }

    /**
     * Decode a Base32 string to binary.
     */
    private static function base32Decode(string $b32): string
    {
        $b32 = strtoupper(trim($b32));
        $alphabet = self::BASE32_CHARS;
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            if ($char === '=') {
                break;
            }

            $val = strpos($alphabet, $char);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
