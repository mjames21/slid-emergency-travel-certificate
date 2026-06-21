<?php

namespace App\Support;

use App\Models\Airport;
use App\Models\VisaApplication;

class ApplicationNumberGenerator
{
    public function generate(Airport $airport): string
    {
        do {
            $number = sprintf(
                'VOA-%s-%s-%05d',
                $airport->code,
                now()->format('Ymd'),
                random_int(1, 99999)
            );
        } while (VisaApplication::query()->where('application_no', $number)->exists());

        return $number;
    }
}
