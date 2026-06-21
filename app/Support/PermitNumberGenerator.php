<?php

namespace App\Support;

use App\Models\Airport;
use App\Models\Permit;

class PermitNumberGenerator
{
    public function generate(Airport $airport): string
    {
        do {
            $number = sprintf(
                'SVA-%s-%s-%06d',
                $airport->code,
                now()->format('Y'),
                random_int(1, 999999)
            );
        } while (Permit::query()->where('permit_no', $number)->exists());

        return $number;
    }
}
