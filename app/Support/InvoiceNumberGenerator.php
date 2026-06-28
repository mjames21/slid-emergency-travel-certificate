<?php

namespace App\Support;

use App\Models\Invoice;

class InvoiceNumberGenerator
{
    public function generate(): string
    {
        do {
            $number = sprintf(
                'ETC-INV-%s-%05d',
                now()->format('Ymd'),
                random_int(1, 99999)
            );
        } while (Invoice::query()->where('invoice_no', $number)->exists());

        return $number;
    }
}
