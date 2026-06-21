<?php

namespace App\Services\Border;

use App\Models\AdmissibilityScreening;
use App\Models\Permit;
use App\Models\User;
use App\Services\Audit\WriteAuditLogService;
use App\Services\Mrz\VisaMrzValidator;
use App\Services\Passenger\BuildPassengerHistoryService;
use App\Support\PermitLifecycleStatus;
use Illuminate\Support\Str;

class AdmissibilityScreeningService
{
    public function __construct(
        protected BuildPassengerHistoryService $passengerHistoryService,
        protected WriteAuditLogService $writeAuditLogService,
        protected VisaMrzValidator $visaMrzValidator,
        protected WatchlistScreeningService $watchlistScreeningService,
        protected TravelRequirementRuleService $travelRequirementRuleService
    ) {
    }

    public function screenPermit(Permit $permit, User $officer, string $movementType = 'entry', ?string $notes = null): AdmissibilityScreening
    {
        $permit->loadMissing([
            'visaApplication.airport',
            'visaApplication.passenger',
            'fraudFlags',
            'visaApplication.fraudFlags',
        ]);

        $application = $permit->visaApplication;
        $passenger = $application?->passenger;

        if (! $application || ! $passenger) {
            throw new \RuntimeException('Permit is missing traveler or application context.');
        }

        $history = $this->passengerHistoryService->handle($passenger->passport_number);
        $watchlist = $this->watchlistScreeningService->screen($passenger);
        $travelRule = $this->travelRequirementRuleService->evaluate($application);
        $reasons = [];
        $recommendations = [];

        $passportValid = $passenger->passport_expiry_date
            && $passenger->passport_expiry_date->endOfDay()->greaterThanOrEqualTo(today());

        $permitStatus = strtolower(PermitLifecycleStatus::value($permit));
        $permitValid = in_array($permitStatus, ['', 'active'], true)
            && $permit->valid_until
            && $permit->valid_until->endOfDay()->greaterThanOrEqualTo(today());

        $passportMrzChecks = $passenger->passport_mrz_data['checks'] ?? [];
        $passportMrzVerified = $passportMrzChecks !== [] && collect($passportMrzChecks)->every(fn ($value) => $value === true);
        $visaMrz = $this->visaMrzValidator->validate($permit->mrz_line_1, $permit->mrz_line_2);
        $mrzVerified = $passportMrzVerified && ($visaMrz['ok'] ?? false);

        $fraudFlagCount = (int) ($permit->fraudFlags?->where('resolved', false)->count() ?? 0)
            + (int) ($application->fraudFlags?->where('resolved', false)->count() ?? 0);

        if (! $passportValid) {
            $reasons[] = 'Passport is expired or missing an expiry date.';
            $recommendations[] = 'Hold traveler for document review before any admission decision.';
        }

        if ($movementType === 'entry' && ! $permitValid) {
            $reasons[] = 'Visa-on-arrival permit is expired, revoked, extended, replaced, or missing validity.';
            $recommendations[] = 'Do not admit on this permit without supervisor review.';
        }

        if (! $mrzVerified) {
            $reasons[] = 'MRZ check digits are not fully verified.';
            $recommendations[] = 'Perform manual passport inspection and record exception notes.';
        }

        if (! ($visaMrz['ok'] ?? false)) {
            $reasons[] = 'Visa permit MRZ failed ICAO format or check-digit validation.';
            $recommendations[] = 'Regenerate permit document or escalate to document control.';
        }

        if (($history['has_fraud_history'] ?? false) || $fraudFlagCount > 0) {
            $reasons[] = 'Traveler or permit has prior fraud/security flags.';
            $recommendations[] = 'Refer to supervisor or secondary inspection.';
        }

        if (($history['has_expired_permit_history'] ?? false) && $movementType === 'entry') {
            $reasons[] = 'Traveler has prior expired permit history.';
            $recommendations[] = 'Review overstay and compliance history before admission.';
        }

        if ($watchlist['has_match'] ?? false) {
            $reasons[] = 'Traveler or travel document matched a watchlist or lost/stolen document alert.';
            $recommendations[] = 'Refer to secondary inspection and follow alert instructions before final decision.';

            foreach ($watchlist['watchlist_matches'] ?? [] as $match) {
                $reasons[] = sprintf(
                    'Watchlist match: %s / %s / %s.',
                    strtoupper((string) ($match['source'] ?? 'internal')),
                    strtoupper((string) ($match['category'] ?? 'alert')),
                    strtoupper((string) ($match['severity'] ?? 'medium'))
                );
            }

            foreach ($watchlist['document_alerts'] ?? [] as $alert) {
                $reasons[] = sprintf(
                    'Travel document alert: %s / %s.',
                    strtoupper((string) ($alert['source'] ?? 'internal')),
                    strtoupper((string) ($alert['document_status'] ?? 'alert'))
                );
            }
        }

        if (($travelRule['status'] ?? null) === 'fail') {
            foreach ($travelRule['failures'] ?? [] as $failure) {
                $reasons[] = $failure;
            }

            $recommendations[] = 'Resolve travel requirement failures or escalate for supervisor override.';
        }

        if (($travelRule['status'] ?? null) === 'manual_review') {
            foreach ($travelRule['warnings'] ?? [] as $warning) {
                $reasons[] = $warning;
            }

            $recommendations[] = 'Verify entry requirements using official government/IATA source before final decision.';
        }

        $status = $this->resolveStatus($passportValid, $permitValid, $mrzVerified, $fraudFlagCount, $history, $movementType, $watchlist, $travelRule);
        $riskLevel = $this->resolveRiskLevel($status, $fraudFlagCount, $mrzVerified, $watchlist);

        $screening = AdmissibilityScreening::create([
            'visa_application_id' => $application->id,
            'permit_id' => $permit->id,
            'passenger_id' => $passenger->id,
            'airport_id' => $application->airport_id,
            'screened_by' => $officer->id,
            'screening_reference' => $this->reference('SCR'),
            'movement_type' => $movementType,
            'status' => $status,
            'risk_level' => $riskLevel,
            'passport_valid' => $passportValid,
            'permit_valid' => $movementType === 'exit' ? true : $permitValid,
            'mrz_verified' => $mrzVerified,
            'traveler_history_reviewed' => true,
            'watchlist_checked' => true,
            'carrier_document_check' => filled($application->flight_carrier) || filled($application->flight_number),
            'protection_referral_required' => str_contains(strtolower((string) $notes), 'asylum')
                || str_contains(strtolower((string) $notes), 'traffick')
                || str_contains(strtolower((string) $notes), 'victim'),
            'reasons' => $reasons,
            'recommendations' => $recommendations,
            'officer_notes' => $notes,
            'screened_at' => now(),
        ]);

        $this->writeAuditLogService->handle(
            $officer,
            'border.admissibility_screened',
            $screening,
            [
                'permit_no' => $permit->permit_no,
                'passport_number' => $passenger->passport_number,
                'status' => $status,
                'risk_level' => $riskLevel,
            ],
            'Admissibility screening completed'
        );

        return $screening;
    }

    protected function resolveStatus(
        bool $passportValid,
        bool $permitValid,
        bool $mrzVerified,
        int $fraudFlagCount,
        array $history,
        string $movementType,
        array $watchlist,
        array $travelRule
    ): string {
        if (($travelRule['status'] ?? null) === 'fail') {
            return 'hold';
        }

        if (($travelRule['status'] ?? null) === 'manual_review') {
            return 'hold';
        }

        if (($watchlist['has_match'] ?? false) && in_array($watchlist['highest_severity'] ?? null, ['critical', 'high'], true)) {
            return 'refer';
        }

        if ($fraudFlagCount > 0 || ($history['has_fraud_history'] ?? false)) {
            return 'refer';
        }

        if (! $passportValid || ($movementType === 'entry' && ! $permitValid)) {
            return 'hold';
        }

        if (! $mrzVerified) {
            return 'hold';
        }

        if (($history['has_expired_permit_history'] ?? false) && $movementType === 'entry') {
            return 'refer';
        }

        return 'clear';
    }

    protected function resolveRiskLevel(string $status, int $fraudFlagCount, bool $mrzVerified, array $watchlist): string
    {
        if ($fraudFlagCount > 0 || in_array($watchlist['highest_severity'] ?? null, ['critical', 'high'], true)) {
            return 'high';
        }

        if ($status === 'refer') {
            return 'medium';
        }

        if ($status === 'hold' || ! $mrzVerified) {
            return 'medium';
        }

        return 'low';
    }

    protected function reference(string $prefix): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));
    }
}
