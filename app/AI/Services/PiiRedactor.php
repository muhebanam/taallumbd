<?php

namespace App\AI\Services;

class PiiRedactor
{
    /**
     * Regex for Bangladeshi mobile numbers (e.g. +8801712345678, 01812345678, 88019...).
     */
    protected const PHONE_REGEX = '/(?:\+?88\s*[-.]?)?01[3-9]\d{2}\s*[-.]?\d{6}/';

    /**
     * Regex for email addresses.
     */
    protected const EMAIL_REGEX = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';

    /**
     * Regex for Bangladeshi NID (10, 13, or 17 digits).
     */
    protected const NID_REGEX = '/\b(?:\d{10}|\d{13}|\d{17})\b/';

    /**
     * Redact Personally Identifiable Information (PII) from user prompt.
     */
    public function redact(string $text): string
    {
        // 1. Redact phone numbers
        $clean = preg_replace(self::PHONE_REGEX, '[PHONE_REDACTED]', $text);

        // 2. Redact emails
        $clean = preg_replace(self::EMAIL_REGEX, '[EMAIL_REDACTED]', $clean);

        // 3. Redact potential NID / bank account numbers (sequences of 10-17 digits)
        $clean = preg_replace(self::NID_REGEX, '[ID_REDACTED]', $clean);

        return trim((string) $clean);
    }

    /**
     * Check if text contains PII.
     */
    public function hasPii(string $text): bool
    {
        return preg_match(self::PHONE_REGEX, $text) === 1
            || preg_match(self::EMAIL_REGEX, $text) === 1
            || preg_match(self::NID_REGEX, $text) === 1;
    }
}
