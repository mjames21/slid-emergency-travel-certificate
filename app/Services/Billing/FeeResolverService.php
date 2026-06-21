<?php

namespace App\Services\Billing;

use App\Models\VisaApplication;

class FeeResolverService
{
    public function resolve(VisaApplication $application): array
    {
        if ($application->is_fee_waived) {
            return [
                'amount' => 0.00,
                'currency' => 'USD',
            ];
        }

        return [
            'amount' => 100.00,
            'currency' => 'USD',
        ];
    }
}
