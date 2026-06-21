<?php

namespace App\Services\Reporting;

use App\Models\FraudFlag;
use App\Models\Permit;
use App\Models\PermitVerification;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Collection;

class FraudMonitoringService
{
    public function detectRepeatedReprints(): Collection
    {
        return Permit::query()
            ->with(['visaApplication.passenger', 'visaApplication.airport'])
            ->where('print_count', '>', 2)
            ->latest()
            ->get();
    }

    public function detectHighInvalidVerifications(): Collection
    {
        return PermitVerification::query()
            ->selectRaw('verification_code, COUNT(*) as invalid_attempts')
            ->whereIn('result', ['invalid', 'not_found'])
            ->groupBy('verification_code')
            ->havingRaw('COUNT(*) >= 3')
            ->get();
    }

    public function detectWaiverHeavyOfficers(): Collection
    {
        return User::query()
            ->selectRaw('users.id, users.name, COUNT(visa_applications.id) as waived_total')
            ->join('visa_applications', 'visa_applications.created_by', '=', 'users.id')
            ->where('visa_applications.is_fee_waived', true)
            ->groupBy('users.id', 'users.name')
            ->havingRaw('COUNT(visa_applications.id) >= 3')
            ->get();
    }

    public function generateFlags(): int
    {
        $created = 0;

        foreach ($this->detectRepeatedReprints() as $permit) {
            $exists = FraudFlag::query()
                ->where('permit_id', $permit->id)
                ->where('flag_type', 'repeated_reprints')
                ->where('resolved', false)
                ->exists();

            if (! $exists) {
                FraudFlag::query()->create([
                    'visa_application_id' => $permit->visa_application_id,
                    'permit_id' => $permit->id,
                    'payment_id' => $permit->payment_id,
                    'flag_type' => 'repeated_reprints',
                    'severity' => 'high',
                    'description' => 'Permit has been printed more than two times.',
                    'resolved' => false,
                    'flagged_at' => now(),
                ]);

                $created++;
            }
        }

        foreach ($this->detectWaiverHeavyOfficers() as $officer) {
            $exists = FraudFlag::query()
                ->where('flag_type', 'waiver_pattern')
                ->where('description', 'like', '%' . $officer->name . '%')
                ->where('resolved', false)
                ->exists();

            if (! $exists) {
                FraudFlag::query()->create([
                    'flag_type' => 'waiver_pattern',
                    'severity' => 'medium',
                    'description' => 'Officer ' . $officer->name . ' has multiple waived applications requiring review.',
                    'resolved' => false,
                    'flagged_at' => now(),
                ]);

                $created++;
            }
        }

        Audit::log(
            action: 'fraud.scan.completed',
            description: 'Automated fraud scan completed.',
            metadata: ['flags_created' => $created]
        );

        return $created;
    }
}
