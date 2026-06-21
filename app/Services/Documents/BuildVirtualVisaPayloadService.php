<?php

namespace App\Services\Documents;

use App\Models\Permit;

class BuildVirtualVisaPayloadService
{
    public function handle(Permit $permit): array
    {
        $permit->loadMissing([
            'visaApplication.passenger',
            'visaApplication.airport',
            'receipt',
            'payment',
        ]);

        return [
            'permit_no' => $permit->permit_no,
            'status' => $permit->status->value,
            'traveler_name' => $permit->visaApplication->passenger->full_name,
            'passport_number' => $permit->visaApplication->passenger->passport_number,
            'nationality' => $permit->visaApplication->passenger->nationality,
            'point_of_entry' => $permit->visaApplication->point_of_entry,
            'issued_at' => optional($permit->issued_at)->format('Y-m-d H:i:s'),
            'valid_until' => optional($permit->valid_until)->format('Y-m-d'),
            'verification_code' => $permit->verification_code,
            'verification_url' => route('verify.permit', $permit->verification_code),
            'receipt_no' => $permit->receipt?->receipt_no,
            'payment_basis' => $permit->payment_id ? 'verified_payment' : ($permit->waiver_approval_id ? 'approved_waiver' : 'unknown'),
            'security_seal' => $permit->security_seal,
        ];
    }
}
