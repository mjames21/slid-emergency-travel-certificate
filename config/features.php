<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Product Scope Feature Flags
    |--------------------------------------------------------------------------
    |
    | Permit operations are the active product scope. The public emergency
    | travel certificate module is active; border management remains available
    | for future activation.
    |
    */

    'emergency_travel_certificate' => env('FEATURE_EMERGENCY_TRAVEL_CERTIFICATE_ENABLED', true),
    'border_management' => env('FEATURE_BORDER_MANAGEMENT_ENABLED', false),
];
