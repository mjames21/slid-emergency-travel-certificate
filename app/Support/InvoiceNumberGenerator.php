<?php

namespace App\Support;

use App\Models\Airport;
use App\Models\Invoice;

class InvoiceNumberGenerator
{
    public function generate(Airport $airport): string
    {
        do {
            $number = sprintf(
                'SVI-%s-%s-%05d',
                $airport->code,
                now()->format('Ymd'),
                random_int(1, 99999)
            );
        } while (Invoice::query()->where('invoice_no', $number)->exists());

        return $number;
    }
}
