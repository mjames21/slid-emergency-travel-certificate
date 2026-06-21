<?php

namespace App\Services\Border;

use App\Models\Passenger;
use App\Models\TravelDocumentAlert;
use App\Models\WatchlistEntry;

class WatchlistScreeningService
{
    public function screen(Passenger $passenger): array
    {
        $passportNumber = strtoupper(trim((string) $passenger->passport_number));
        $nationalityCode = strtoupper(trim((string) $passenger->nationality_code));
        $surname = strtoupper(trim((string) $passenger->surname));
        $dateOfBirth = $passenger->date_of_birth?->toDateString();

        $watchlistMatches = WatchlistEntry::query()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($query) use ($passportNumber, $nationalityCode, $surname, $dateOfBirth) {
                $query->where('passport_number', $passportNumber)
                    ->orWhere(function ($query) use ($nationalityCode, $surname, $dateOfBirth) {
                        $query->whereRaw('UPPER(surname) = ?', [$surname])
                            ->when($nationalityCode !== '', fn ($query) => $query->where('nationality_code', $nationalityCode))
                            ->when($dateOfBirth !== null, fn ($query) => $query->whereDate('date_of_birth', $dateOfBirth));
                    });
            })
            ->orderByRaw("case severity when 'critical' then 1 when 'high' then 2 when 'medium' then 3 else 4 end")
            ->limit(10)
            ->get();

        $documentAlerts = TravelDocumentAlert::query()
            ->whereRaw('UPPER(document_number) = ?', [$passportNumber])
            ->when($nationalityCode !== '', fn ($query) => $query->where(function ($query) use ($nationalityCode) {
                $query->whereNull('issuing_state')
                    ->orWhere('issuing_state', $nationalityCode);
            }))
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->limit(10)
            ->get();

        $highestSeverity = $this->highestSeverity($watchlistMatches->pluck('severity')->all());

        return [
            'checked' => true,
            'has_match' => $watchlistMatches->isNotEmpty() || $documentAlerts->isNotEmpty(),
            'highest_severity' => $highestSeverity,
            'watchlist_matches' => $watchlistMatches->map(fn (WatchlistEntry $entry): array => [
                'id' => $entry->id,
                'source' => $entry->source,
                'category' => $entry->category,
                'severity' => $entry->severity,
                'reason' => $entry->reason,
                'instructions' => $entry->instructions,
            ])->values()->all(),
            'document_alerts' => $documentAlerts->map(fn (TravelDocumentAlert $alert): array => [
                'id' => $alert->id,
                'source' => $alert->source,
                'document_status' => $alert->document_status,
                'reason' => $alert->reason,
            ])->values()->all(),
        ];
    }

    protected function highestSeverity(array $severities): ?string
    {
        foreach (['critical', 'high', 'medium', 'low'] as $severity) {
            if (in_array($severity, $severities, true)) {
                return $severity;
            }
        }

        return null;
    }
}
