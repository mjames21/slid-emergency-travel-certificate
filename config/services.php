<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'tesseract' => [
        'binary' => env('TESSERACT_BINARY', 'tesseract'),
    ],

    'wangov' => [
        'enabled' => env('WANGOV_ENABLED', false),
        'timeout' => (int) env('WANGOV_TIMEOUT', 15),
        'allowed_methods' => env('WANGOV_ALLOWED_METHODS', ''),
        'fallback_applicant_nin' => env('WANGOV_FALLBACK_APPLICANT_NIN', 'SLIDETC'),
        'checkout_allowed_hosts' => array_filter(array_map('trim', explode(',', (string) env('WANGOV_CHECKOUT_ALLOWED_HOSTS', '')))),

        'external' => [
            'base_url' => env('WANGOV_BASE_URL', ''),
            'endpoint' => env('WANGOV_ENDPOINT', '/external-service'),
            'bearer_token' => env('WANGOV_BEARER_TOKEN'),
            'service_key' => env('WANGOV_SERVICE_KEY', ''),
            'service_code' => env('WANGOV_SERVICE_CODE', 'slid003'),
            'service_slug' => env('WANGOV_SERVICE_SLUG', 'sierra-leone-emergency-travel-certificate'),
            'service_display' => env('WANGOV_SERVICE_DISPLAY', 'Sierra Leone Emergency Travel Certificate'),
            'origin' => env('WANGOV_ORIGIN', env('APP_URL')),
        ],

        'webhook' => [
            'ips' => array_filter(array_map('trim', explode(',', (string) env('WANGOV_WEBHOOK_IPS', '')))),
            'max_payload_bytes' => (int) env('WANGOV_WEBHOOK_MAX_PAYLOAD_BYTES', 20000),
            'vendor_secret' => env('WANGOV_WEBHOOK_SECRET', ''),
        ],
    ],

];
