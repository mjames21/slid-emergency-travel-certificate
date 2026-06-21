<?php

return [
    'headers' => [
        'enabled' => env('SECURITY_HEADERS_ENABLED', true),
        'hsts' => env('SECURITY_HSTS_ENABLED', env('APP_ENV') === 'production'),
        'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
        'hsts_preload' => env('SECURITY_HSTS_PRELOAD', false),
        'content_security_policy' => env('SECURITY_CSP_ENABLED', true),
        'upgrade_insecure_requests' => env('SECURITY_UPGRADE_INSECURE_REQUESTS', env('APP_ENV') === 'production'),
        'hide_powered_by' => env('SECURITY_HIDE_POWERED_BY_HEADER', true),
    ],

    'session_integrity' => [
        'enabled' => env('SECURITY_SESSION_INTEGRITY_ENABLED', true),
        'bind_user_agent' => env('SECURITY_SESSION_BIND_USER_AGENT', true),
        'bind_ip_prefix' => env('SECURITY_SESSION_BIND_IP_PREFIX', true),
        'ipv4_prefix_bits' => (int) env('SECURITY_SESSION_IPV4_PREFIX_BITS', 24),
        'ipv6_prefix_bits' => (int) env('SECURITY_SESSION_IPV6_PREFIX_BITS', 64),
    ],

    'staff_mfa' => [
        'required' => env('SECURITY_STAFF_MFA_REQUIRED', env('APP_ENV') === 'production'),
        'require_confirmed' => env('SECURITY_STAFF_MFA_REQUIRE_CONFIRMED', true),
    ],

    'staff_email_domains' => array_filter(array_map(
        fn (string $domain): string => strtolower(trim($domain)),
        explode(',', (string) env('SECURITY_STAFF_EMAIL_DOMAINS', 'immigration.gov.sl'))
    )),

    'secrets' => [
        'minimum_shared_secret_length' => (int) env('SECURITY_MIN_SHARED_SECRET_LENGTH', 32),
    ],
];
