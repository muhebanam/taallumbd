<?php

if (! function_exists('bn_number')) {
    /** Convert 0-9 digits to Bengali numerals: 2026 -> ২০২৬ */
    function bn_number(int|float|string $value): string
    {
        return strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    }
}
