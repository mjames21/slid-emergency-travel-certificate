<?php
// FILE: app/Http/Controllers/Hq/ExportExpiryReportController.php

namespace App\Http\Controllers\Hq;

use App\Http\Controllers\Controller;
use App\Models\Permit;
use App\Support\PermitLifecycleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportExpiryReportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'scope' => ['nullable', 'in:expiring_soon,expired_active'],
            'days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'airport_id' => ['nullable', 'integer', 'exists:airports,id'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $scope = (string) ($validated['scope'] ?? 'expiring_soon');
        $days = max(1, min(30, (int) ($validated['days'] ?? 7)));
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $airportId = $validated['airport_id'] ?? null;
        $search = trim((string) ($validated['search'] ?? ''));

        [$filename, $query] = $this->buildExport(
            $scope,
            $days,
            $startDate,
            $endDate,
            $airportId,
            $search
        );

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'permit_no',
                'traveler_name',
                'passport_number',
                'airport',
                'valid_until',
                'lifecycle_status',
                'issuer',
                'is_extension',
                'parent_permit_id',
            ]);

            $query
                ->orderBy('valid_until')
                ->orderBy('permit_no')
                ->chunk(500, function ($permits) use ($handle) {
                    foreach ($permits as $permit) {
                        fputcsv($handle, [
                            $permit->permit_no,
                            $permit->visaApplication?->passenger?->full_name ?? '',
                            $permit->visaApplication?->passenger?->passport_number ?? '',
                            $permit->visaApplication?->airport?->name ?? '',
                            optional($permit->valid_until)->format('Y-m-d') ?: '',
                            PermitLifecycleStatus::value($permit),
                            $permit->issuer?->name ?? '',
                            $permit->is_extension ? 'yes' : 'no',
                            $permit->parent_permit_id ?: '',
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function buildExport(
        string $scope,
        int $days,
        ?string $startDate,
        ?string $endDate,
        ?int $airportId,
        string $search
    ): array {
        $query = $this->baseQuery();

        if ($airportId) {
            $query->whereHas('visaApplication', function (Builder $query) use ($airportId) {
                $query->where('airport_id', $airportId);
            });
        }

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('permit_no', 'like', "%{$search}%")
                    ->orWhereHas('visaApplication.passenger', function (Builder $passengerQuery) use ($search) {
                        $passengerQuery
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('passport_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($scope === 'expired_active') {
            $query->whereDate('valid_until', '<', today());

            if ($startDate !== null) {
                $query->whereDate('valid_until', '>=', $startDate);
            }

            if ($endDate !== null) {
                $query->whereDate('valid_until', '<=', $endDate);
            }

            return [
                'hq-expired-active-permits-' . now()->format('Ymd-His') . '.csv',
                $query,
            ];
        }

        if ($startDate !== null && $endDate !== null) {
            $query->whereDate('valid_until', '>=', $startDate)
                ->whereDate('valid_until', '<=', $endDate);

            return [
                'hq-expiring-range-permits-' . now()->format('Ymd-His') . '.csv',
                $query,
            ];
        }

        $query->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->copy()->addDays($days));

        return [
            'hq-expiring-soon-permits-' . now()->format('Ymd-His') . '.csv',
            $query,
        ];
    }

    protected function baseQuery(): Builder
    {
        return Permit::query()
            ->with([
                'visaApplication.airport',
                'visaApplication.passenger',
                'issuer',
            ])
            ->whereNotNull('valid_until')
            ->tap(fn (Builder $query) => PermitLifecycleStatus::constrainActive($query));
    }
}
