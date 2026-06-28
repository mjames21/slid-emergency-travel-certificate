<?php

namespace App\Support;

use App\Models\VisaApplication;

class ApplicationNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = sprintf(
                'ETC-%s-%05d',
                now()->format('Ymd'),
                random_int(1, 99999)
            );
        } while (VisaApplication::query()->where('application_no', $number)->exists());

        return $number;
    }
}
