<?php

namespace App\Support;

use App\Models\Permit;

class VerificationCodeGenerator
{
    public function generate(): string
    {
        do {
            $code = 'SVV-' . strtoupper(bin2hex(random_bytes(10)));
        } while (Permit::query()->where('verification_code', $code)->exists());

        return $code;
    }
}
