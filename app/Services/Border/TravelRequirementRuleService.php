<?php

namespace App\Services\Border;

use App\Models\TravelRequirementRule;
use App\Models\VisaApplication;

class TravelRequirementRuleService
{
    public function evaluate(VisaApplication $application): array
    {
        $application->loadMissing('passenger');

        $passenger = $application->passenger;
        $nationalityCode = strtoupper((string) $passenger?->nationality_code);
        $purpose = strtoupper((string) $application->purpose_of_visit);
        $carrierTokens = $this->carrierTokens((string) $application->flight_carrier);

        $rule = TravelRequirementRule::query()
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('effective_from')
                    ->orWhereDate('effective_from', '<=', today());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhereDate('effective_until', '>=', today());
            })
            ->where(function ($query) use ($nationalityCode) {
                $query->whereNull('nationality_code')
                    ->orWhere('nationality_code', $nationalityCode);
            })
            ->where(function ($query) use ($purpose) {
                $query->whereNull('purpose_of_visit')
                    ->orWhereRaw('UPPER(purpose_of_visit) = ?', [$purpose]);
            })
            ->where(function ($query) use ($carrierTokens) {
                $query->whereNull('carrier_code');

                foreach ($carrierTokens as $carrierToken) {
                    $query->orWhereRaw('UPPER(carrier_code) = ?', [$carrierToken]);
                }
            })
            ->orderByRaw('case when nationality_code is null then 1 else 0 end')
            ->orderByRaw('case when purpose_of_visit is null then 1 else 0 end')
            ->orderByRaw('case when carrier_code is null then 1 else 0 end')
            ->first();

        $failures = [];
        $warnings = [];

        if (! $rule) {
            return [
                'checked' => true,
                'matched_rule_id' => null,
                'status' => 'manual_review',
                'failures' => [],
                'warnings' => ['No local travel requirement rule matched this nationality, purpose, and carrier context.'],
            ];
        }

        if ($rule->max_stay_days && $application->period_of_stay_days > $rule->max_stay_days) {
            $failures[] = sprintf('Requested stay exceeds maximum stay of %d days.', $rule->max_stay_days);
        }

        if ($rule->host_address_required && blank($application->host_address) && blank($application->destination_address)) {
            $failures[] = 'Host or destination address is required by the travel rule.';
        }

        if ($rule->min_passport_validity_days > 0 && $passenger?->passport_expiry_date && $application->arrival_date) {
            $remainingDays = $application->arrival_date->diffInDays($passenger->passport_expiry_date, false);

            if ($remainingDays < $rule->min_passport_validity_days) {
                $failures[] = sprintf('Passport validity is below the required %d days after arrival.', $rule->min_passport_validity_days);
            }
        }

        if ($rule->return_ticket_required) {
            $warnings[] = 'Return/onward ticket must be verified manually until carrier data integration is active.';
        }

        return [
            'checked' => true,
            'matched_rule_id' => $rule->id,
            'status' => $failures === [] ? 'pass' : 'fail',
            'failures' => $failures,
            'warnings' => $warnings,
        ];
    }

    protected function carrierTokens(string $carrier): array
    {
        $normalized = strtoupper(trim($carrier));

        if ($normalized === '') {
            return [];
        }

        $tokens = [$normalized];

        if (preg_match('/\(([A-Z0-9]{2,3})\)/', $normalized, $matches)) {
            $tokens[] = $matches[1];
        }

        foreach (config('flight_carriers', []) as $flightCarrier) {
            $name = strtoupper((string) ($flightCarrier['name'] ?? ''));
            $carrierCode = strtoupper((string) ($flightCarrier['code'] ?? $flightCarrier['iata'] ?? ''));

            if ($name === '' && $carrierCode === '') {
                continue;
            }

            if ($normalized === $name || $normalized === $carrierCode || ($name !== '' && str_contains($normalized, $name))) {
                $tokens[] = $name;

                if ($carrierCode !== '') {
                    $tokens[] = $carrierCode;
                }
            }
        }

        return array_values(array_unique(array_filter($tokens)));
    }
}
