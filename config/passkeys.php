<?php

$configuredOrigins = env('PASSKEYS_ALLOWED_ORIGINS');

return [
    'relying_party_id' => env(
        'PASSKEYS_RELYING_PARTY_ID',
        parse_url(config('app.url'), PHP_URL_HOST)
    ),

    'allowed_origins' => $configuredOrigins
        ? array_values(array_filter(array_map('trim', explode(',', $configuredOrigins))))
        : [config('app.url')],

    'user_handle_secret' => env('PASSKEYS_USER_HANDLE_SECRET', config('app.key')),

    'timeout' => (int) env('PASSKEYS_TIMEOUT', 60000),

    'guard' => 'web',

    'middleware' => ['web'],

    'management_middleware' => ['password.confirm'],

    'throttle' => 'throttle:6,1',

    'redirect' => '/dashboard',
];
