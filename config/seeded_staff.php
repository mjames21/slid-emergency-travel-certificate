<?php

return [
    'allow_default_passwords' => env('SEED_ALLOW_DEFAULT_PASSWORDS', env('APP_ENV') !== 'production'),
    'default_password' => env('SEED_DEFAULT_PASSWORD', 'ChangeMe123!'),

    'users' => [
        'system_administrator' => [
            'title_code' => 'system_administrator',
            'name' => env('SEED_SYSTEM_ADMIN_NAME', 'System Administrator'),
            'email' => env('SEED_SYSTEM_ADMIN_EMAIL', 'admin@immigration.gov.sl'),
            'staff_number' => env('SEED_SYSTEM_ADMIN_STAFF_NUMBER', 'SLID-0001'),
            'job_title' => 'System Administrator',
            'phone' => env('SEED_SYSTEM_ADMIN_PHONE', '+232000000001'),
            'password' => env('SEED_SYSTEM_ADMIN_PASSWORD'),
            'password_env' => 'SEED_SYSTEM_ADMIN_PASSWORD',
        ],

        'etc_issuer' => [
            'title_code' => 'etc_issuer',
            'name' => env('SEED_ETC_ISSUER_NAME', 'ETC Issuer'),
            'email' => env('SEED_ETC_ISSUER_EMAIL', 'etc.issuer@immigration.gov.sl'),
            'staff_number' => env('SEED_ETC_ISSUER_STAFF_NUMBER', 'SLID-ETC-0001'),
            'job_title' => 'ETC Issuer',
            'phone' => env('SEED_ETC_ISSUER_PHONE', '+232000000002'),
            'password' => env('SEED_ETC_ISSUER_PASSWORD'),
            'password_env' => 'SEED_ETC_ISSUER_PASSWORD',
        ],

        'executive_observer' => [
            'title_code' => 'executive_observer',
            'name' => env('SEED_EXECUTIVE_NAME', 'Executive Observer'),
            'email' => env('SEED_EXECUTIVE_EMAIL', 'executive@immigration.gov.sl'),
            'staff_number' => env('SEED_EXECUTIVE_STAFF_NUMBER', 'SLID-EXE-0001'),
            'job_title' => 'Executive',
            'phone' => env('SEED_EXECUTIVE_PHONE', '+232000000003'),
            'password' => env('SEED_EXECUTIVE_PASSWORD'),
            'password_env' => 'SEED_EXECUTIVE_PASSWORD',
        ],
    ],
];
