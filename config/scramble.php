<?php

return [
    /*
     * The path where your OpenAPI specification will be exported.
     */
    'api_path' => 'api/v1',

    'api_domain' => null,

    'theme' => 'light',

    /*
     * The URL where the documentation will be available.
     */
    'doc_route' => 'docs/api',

    /*
     * The description of your API.
     */
    'info' => [
        'version' => '1.0.0',
        'title' => "তা'ল্লুম বিডি (Taallum BD) REST API v1",
        'description' => 'Taallum BD ইসলামিক লার্নিং অ্যান্ড স্কলার এডটেক প্ল্যাটফর্মের মোবাইল অ্যাপ ও থার্ড-পার্টি ইন্টিগ্রেশনের জন্য অফিসিয়াল RESTful API স্পেসিফিকেশন।',
    ],

    /*
     * Customize Stoplight Elements UI.
     */
    'ui' => [
        'title' => 'Taallum BD API Documentation',
        'hide_try_it' => false,
    ],

    /*
     * The list of middleware of the documentation routes.
     */
    'middleware' => [
        'web',
    ],

    'extensions' => [],
];
