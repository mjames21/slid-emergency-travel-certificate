<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Product Scope Feature Flags
    |--------------------------------------------------------------------------
    |
    | Emergency Travel Certificate is the active product scope.
    |
    */

    'emergency_travel_certificate' => env('FEATURE_EMERGENCY_TRAVEL_CERTIFICATE_ENABLED', true),
];
