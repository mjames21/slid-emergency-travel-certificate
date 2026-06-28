<?php

namespace App\Support;

use App\Models\Permit;

class PermitNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = sprintf(
                'ETC-CERT-%s-%06d',
                now()->format('Y'),
                random_int(1, 999999)
            );
        } while (Permit::query()->where('permit_no', $number)->exists());

        return $number;
    }
}
