<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    */
    'default' => env('PAYMENT_DEFAULT_GATEWAY', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Mock Payment Enabled (Local / Testing only)
    |--------------------------------------------------------------------------
    */
    'mock_enabled' => env('PAYMENTS_MOCK_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateways Configuration
    |--------------------------------------------------------------------------
    */
    'gateways' => [
        'manual' => [
            'enabled' => true,
            'methods' => [
                'bkash' => [
                    'name' => 'bKash (বিকাশ)',
                    'number' => env('BKASH_MANUAL_NUMBER', '01700000000'),
                    'type' => env('BKASH_MANUAL_TYPE', 'personal'),
                    'instructions' => 'বিকাশ অ্যাপ বা *247# থেকে সেন্ড মানি (Personal) করুন। এরপর নিচের ফর্মে ট্রানজ্যাকশন আইডি ও আপনার প্রেরক নম্বর দিন।',
                ],
                'nagad' => [
                    'name' => 'Nagad (নগদ)',
                    'number' => env('NAGAD_MANUAL_NUMBER', '01700000000'),
                    'type' => env('NAGAD_MANUAL_TYPE', 'personal'),
                    'instructions' => 'নগদ অ্যাপ বা *167# থেকে সেন্ড মানি করুন। এরপর নিচের ফর্মে ট্রানজ্যাকশন আইডি ও আপনার প্রেরক নম্বর দিন।',
                ],
                'rocket' => [
                    'name' => 'Rocket (রকেট)',
                    'number' => env('ROCKET_MANUAL_NUMBER', '01700000000'),
                    'type' => env('ROCKET_MANUAL_TYPE', 'personal'),
                    'instructions' => 'রকেট অ্যাপ বা *322# থেকে সেন্ড মানি করুন। এরপর নিচের ফর্মে ট্রানজ্যাকশন আইডি ও আপনার প্রেরক নম্বর দিন।',
                ],
            ],
        ],

        'mock' => [
            'enabled' => env('PAYMENTS_MOCK_ENABLED', false),
        ],

        'sslcommerz' => [
            'enabled' => env('SSLCOMMERZ_ENABLED', false),
            'mode' => env('SSLCOMMERZ_MODE', 'sandbox'),
            'store_id' => env('SSLCOMMERZ_STORE_ID'),
            'store_password' => env('SSLCOMMERZ_STORE_PASSWORD'),
        ],

        'bkash' => [
            'enabled' => env('BKASH_ENABLED', false),
            'mode' => env('BKASH_MODE', 'sandbox'),
            'app_key' => env('BKASH_APP_KEY'),
            'app_secret' => env('BKASH_APP_SECRET'),
            'username' => env('BKASH_USERNAME'),
            'password' => env('BKASH_PASSWORD'),
        ],

        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', true),
            'mode' => env('STRIPE_MODE', 'test'),
            'key' => env('STRIPE_KEY'),
            'secret' => env('STRIPE_SECRET'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'currency' => env('STRIPE_CURRENCY', 'usd'),
        ],
    ],

    'exchange_rates' => [
        'usd_to_bdt' => env('EXCHANGE_RATE_USD_BDT', 120.00),
    ],
];
