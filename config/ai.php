<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Feature Master Toggle
    |--------------------------------------------------------------------------
    | If disabled, all AI features gracefully downgrade or inform user.
    */
    'enabled' => env('AI_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Default AI Provider
    |--------------------------------------------------------------------------
    | Supported: "gemini", "openai", "fake", "null"
    */
    'provider' => env('AI_PROVIDER', 'gemini'),

    /*
    |--------------------------------------------------------------------------
    | Google Gemini Settings (Free Tier Default)
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY', ''),
        'model' => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'text-embedding-004'),
        'temperature' => (float) env('GEMINI_TEMPERATURE', 0.3),
        'max_tokens' => (int) env('GEMINI_MAX_TOKENS', 1024),
        'timeout' => (int) env('GEMINI_TIMEOUT', 15),
        'max_retries' => (int) env('GEMINI_MAX_RETRIES', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Daily Quota & Cost Protection Guards
    |--------------------------------------------------------------------------
    */
    'quotas' => [
        'user_daily_limit' => (int) env('AI_USER_DAILY_LIMIT', 25),
        'global_daily_limit' => (int) env('AI_GLOBAL_DAILY_LIMIT', 500),
        'cache_ttl_hours' => (int) env('AI_CACHE_TTL_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Islamic & Legal Safety Disclaimers
    |--------------------------------------------------------------------------
    */
    'disclaimer' => 'সতর্কতা: এটি কৃত্রিম বুদ্ধিমত্তা (AI) দ্বারা সংকলিত প্রাথমিক তথ্য। এটি কোনো শরয়ী ফতোয়া নয়। চূড়ান্ত সিদ্ধান্তের জন্য নির্ভরযোগ্য আলেম ও ফতোয়া বোর্ডের পরামর্শ নিন।',

    'fatwa_redirect_message' => 'ফিকহী বিধান বা ফতোয়ার জন্য অনুগ্রহ করে আমাদের যোগ্য মুফতি বোর্ডের কাছে প্রশ্ন করুন।',

    'privacy_notice' => 'সতর্কবার্তা: AI ফিচারে কোনো ব্যক্তিগত সংবেদনশীল তথ্য (নাম, ফোন, ইমেইল, ঠিকানা) প্রদান করবেন না। ডেটা প্রক্রিয়াকরণের পূর্বে ব্যক্তিগত তথ্য স্বয়ংক্রিয়ভাবে ফিল্টার করা হয়।',
];
