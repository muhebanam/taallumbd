<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Revenue Share Percentages
    |--------------------------------------------------------------------------
    | Default instructor and platform split when no course or teacher override exists.
    */
    'default_instructor_percentage' => (float) env('WALLET_DEFAULT_INSTRUCTOR_SHARE', 70.00),
    'default_platform_percentage' => (float) env('WALLET_DEFAULT_PLATFORM_SHARE', 30.00),

    /*
    |--------------------------------------------------------------------------
    | Escrow / Refund Hold Window
    |--------------------------------------------------------------------------
    | Number of days before pending earnings mature into available balance.
    */
    'hold_days' => (int) env('WALLET_HOLD_DAYS', 7),

    /*
    |--------------------------------------------------------------------------
    | Minimum Payout Threshold (in Poisha)
    |--------------------------------------------------------------------------
    | 50,000 poisha = 500 BDT.
    */
    'min_payout_amount_poisha' => (int) env('WALLET_MIN_PAYOUT_POISHA', 50000),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('WALLET_DEFAULT_CURRENCY', 'BDT'),
];
