<?php

namespace App\Support;

use App\Models\Airport;
use App\Models\Receipt;

class ReceiptNumberGenerator
{
    public function generate(Airport $airport): string
    {
        do {
            $number = sprintf(
                'SVR-%s-%s-%06d',
                $airport->code,
                now()->format('Ymd'),
                random_int(1, 999999)
            );
        } while (Receipt::query()->where('receipt_no', $number)->exists());

        return $number;
    }
}
