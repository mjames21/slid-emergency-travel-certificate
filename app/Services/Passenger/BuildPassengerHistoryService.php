<?php
// FILE: app/Services/Passenger/BuildPassengerHistoryService.php

namespace App\Services\Passenger;

use App\Models\Passenger;
use App\Models\Permit;
use App\Models\PermitExtension;
use App\Models\VisaApplication;
use App\Support\PermitLifecycleStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BuildPassengerHistoryService
{
    public function handle(?string $passportNumber): array
    {
        $passportNumber = $this->normalizePassportNumber($passportNumber);

        if ($passportNumber === '') {
            return $this->emptyHistory();
        }

        $passengers = Passenger::query()
            ->whereRaw('UPPER(TRIM(passport_number)) = ?', [$passportNumber])
            ->with([
                'visaApplications.airport',
                'visaApplications.permit.issuer',
                'visaApplications.permit.fraudFlags',
            ])
            ->get();

        if ($passengers->isEmpty()) {
            return $this->emptyHistory($passportNumber);
        }

        $applications = $this->collectApplications($passengers);
        $permits = $this->collectPermits($applications);
        $extensions = $this->collectExtensions($permits);
        $fraudFlags = $this->collectFraudFlags($permits);

        $latestApplication = $applications
            ->sortByDesc(fn (VisaApplication $application) => $application->created_at?->timestamp ?? 0)
            ->first();

        $latestPermit = $permits
            ->sortByDesc(fn (Permit $permit) => $permit->created_at?->timestamp ?? 0)
            ->first();

        $activePermits = $permits->filter(function (Permit $permit): bool {
            $status = strtolower(PermitLifecycleStatus::value($permit));

            return in_array($status, ['', 'active'], true)
                && $permit->valid_until
                && Carbon::parse($permit->valid_until)->endOfDay()->isFuture();
        });

        $expiredPermits = $permits->filter(function (Permit $permit): bool {
            return $permit->valid_until
                && Carbon::parse($permit->valid_until)->endOfDay()->isPast();
        });

        $duplicatePassengerRecords = $passengers->count() > 1;
        $hasExtensionHistory = $extensions->isNotEmpty();
        $hasFraudHistory = $fraudFlags->isNotEmpty();
        $hasExpiredPermitHistory = $expiredPermits->isNotEmpty();

        return [
            'found' => true,
            'passport_number' => $passportNumber,
            'is_repeat_traveler' => $permits->count() > 0 || $applications->count() > 1,
            'has_duplicate_passenger_records' => $duplicatePassengerRecords,
            'has_extension_history' => $hasExtensionHistory,
            'has_fraud_history' => $hasFraudHistory,
            'has_expired_permit_history' => $hasExpiredPermitHistory,

            'counts' => [
                'passengers' => $passengers->count(),
                'applications' => $applications->count(),
                'permits' => $permits->count(),
                'active_permits' => $activePermits->count(),
                'expired_permits' => $expiredPermits->count(),
                'extensions' => $extensions->count(),
                'fraud_flags' => $fraudFlags->count(),
            ],

            'latest' => [
                'traveler_name' => $this->resolveLatestTravelerName($passengers),
                'application_id' => $latestApplication?->id,
                'application_date' => $latestApplication?->created_at?->toDateString(),
                'airport_name' => $latestApplication?->airport?->name,
                'permit_id' => $latestPermit?->id,
                'permit_no' => $latestPermit?->permit_no,
                'valid_until' => $latestPermit?->valid_until?->toDateString(),
                'lifecycle_status' => PermitLifecycleStatus::value($latestPermit),
            ],

            'alerts' => array_values(array_filter([
                $duplicatePassengerRecords ? 'Multiple passenger records use this passport number.' : null,
                $hasExtensionHistory ? 'Traveler has prior extension history.' : null,
                $hasExpiredPermitHistory ? 'Traveler has prior expired permit history.' : null,
                $hasFraudHistory ? 'Traveler has prior fraud flags.' : null,
            ])),

            'recent_permits' => $permits
                ->sortByDesc(fn (Permit $permit) => $permit->created_at?->timestamp ?? 0)
                ->take(5)
                ->map(function (Permit $permit): array {
                    return [
                        'id' => $permit->id,
                        'permit_no' => $permit->permit_no,
                        'valid_until' => $permit->valid_until?->toDateString(),
                        'lifecycle_status' => PermitLifecycleStatus::value($permit),
                        'issuer_name' => $permit->issuer?->name,
                    ];
                })
                ->values()
                ->all(),

            'recent_extensions' => $extensions
                ->sortByDesc(fn (PermitExtension $extension) => $extension->created_at?->timestamp ?? 0)
                ->take(5)
                ->map(function (PermitExtension $extension): array {
                    return [
                        'id' => $extension->id,
                        'extension_no' => $extension->extension_no,
                        'status' => $extension->status,
                        'requested_new_valid_until' => $extension->requested_new_valid_until?->toDateString(),
                    ];
                })
                ->values()
                ->all(),
        ];
    }

    protected function normalizePassportNumber(?string $passportNumber): string
    {
        return strtoupper(trim((string) $passportNumber));
    }

    protected function collectApplications(Collection $passengers): Collection
    {
        return $passengers
            ->flatMap(fn (Passenger $passenger) => $passenger->visaApplications ?? collect())
            ->unique('id')
            ->values();
    }

    protected function collectPermits(Collection $applications): Collection
    {
        return $applications
            ->map(fn (VisaApplication $application) => $application->permit)
            ->filter()
            ->unique('id')
            ->values();
    }

    protected function collectExtensions(Collection $permits): Collection
    {
        if ($permits->isEmpty()) {
            return collect();
        }

        $permitIds = $permits->pluck('id')->filter()->values();

        return PermitExtension::query()
            ->whereIn('original_permit_id', $permitIds)
            ->orWhereIn('new_permit_id', $permitIds)
            ->get()
            ->unique('id')
            ->values();
    }

    protected function collectFraudFlags(Collection $permits): Collection
    {
        return $permits
            ->flatMap(fn (Permit $permit) => $permit->fraudFlags ?? collect())
            ->unique('id')
            ->values();
    }

    protected function resolveLatestTravelerName(Collection $passengers): ?string
    {
        $latestPassenger = $passengers
            ->sortByDesc(fn (Passenger $passenger) => $passenger->updated_at?->timestamp ?? $passenger->created_at?->timestamp ?? 0)
            ->first();

        if (! $latestPassenger) {
            return null;
        }

        return $latestPassenger->full_name
            ?? trim(collect([
                $latestPassenger->surname ?? null,
                $latestPassenger->given_names ?? null,
            ])->filter()->implode(' '));
    }

    protected function emptyHistory(string $passportNumber = ''): array
    {
        return [
            'found' => false,
            'passport_number' => $passportNumber,
            'is_repeat_traveler' => false,
            'has_duplicate_passenger_records' => false,
            'has_extension_history' => false,
            'has_fraud_history' => false,
            'has_expired_permit_history' => false,

            'counts' => [
                'passengers' => 0,
                'applications' => 0,
                'permits' => 0,
                'active_permits' => 0,
                'expired_permits' => 0,
                'extensions' => 0,
                'fraud_flags' => 0,
            ],

            'latest' => [
                'traveler_name' => null,
                'application_id' => null,
                'application_date' => null,
                'airport_name' => null,
                'permit_id' => null,
                'permit_no' => null,
                'valid_until' => null,
                'lifecycle_status' => null,
            ],

            'alerts' => [],
            'recent_permits' => [],
            'recent_extensions' => [],
        ];
    }
}
