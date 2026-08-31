<?php

namespace App\Services\Verification;

use App\Enums\PermitStatus;
use App\Enums\PermitVerificationResult;
use App\Models\Permit;
use App\Models\PermitVerification;

class VerifyPermitService
{
    public function handle(string $code, array $context = []): array
    {
        $permit = Permit::query()
            ->with(['visaApplication.passenger', 'payment'])
            ->where('verification_code', $code)
            ->first();

        $result = PermitVerificationResult::NotFound;

        if ($permit) {
            $result = match (true) {
                $permit->status === PermitStatus::Cancelled => PermitVerificationResult::Cancelled,
                $permit->status === PermitStatus::Revoked => PermitVerificationResult::Revoked,
                $permit->valid_until && $permit->valid_until->endOfDay()->isPast() => PermitVerificationResult::Expired,
                default => PermitVerificationResult::Valid,
            };
        }

        PermitVerification::query()->create([
            'permit_id' => $permit?->id,
            'verification_code' => $code,
            'result' => $result,
            'channel' => $context['channel'] ?? 'public_portal',
            'ip_address' => $context['ip_address'] ?? null,
            'user_agent' => $context['user_agent'] ?? null,
            'verified_at' => now(),
        ]);

        return [
            'permit' => $permit,
            'result' => $result,
        ];
    }
}
