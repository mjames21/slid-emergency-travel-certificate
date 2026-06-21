<?php
// FILE: app/Support/PrintableSecurityValue.php

namespace App\Support;

class PrintableSecurityValue
{
    public static function short(?string $value, int $length = 16): string
    {
        if (! $value) {
            return 'N/A';
        }

        return mb_substr($value, 0, $length) . '...';
    }
}