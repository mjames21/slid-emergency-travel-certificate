<?php


// FILE: app/Http/Controllers/Staff/ExportExpiryExcelReportController.php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Permit;
use App\Support\PermitLifecycleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportExpiryExcelReportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'scope' => ['nullable', 'in:expiring_soon,expired_active'],
            'days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $scope = (string) ($validated['scope'] ?? 'expiring_soon');
        $days = max(1, min(30, (int) ($validated['days'] ?? 7)));
        $startDate = $validated['start_date'] ?? null;
        $endDate = $validated['end_date'] ?? null;
        $search = trim((string) ($validated['search'] ?? ''));

        [$filename, $query] = $this->buildExport(
            $request,
            $scope,
            $days,
            $startDate,
            $endDate,
            $search
        );

        return response()->streamDownload(function () use ($query) {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ';
            echo 'xmlns:o="urn:schemas-microsoft-com:office:office" ';
            echo 'xmlns:x="urn:schemas-microsoft-com:office:excel" ';
            echo 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ';
            echo 'xmlns:html="http://www.w3.org/TR/REC-html40">';

            echo '<Styles>';
            echo '<Style ss:ID="Default" ss:Name="Normal">';
            echo '<Alignment ss:Vertical="Center"/><Borders/><Font ss:FontName="Calibri" ss:Size="11"/><Interior/><NumberFormat/><Protection/>';
            echo '</Style>';

            echo '<Style ss:ID="Header">';
            echo '<Alignment ss:Horizontal="Center" ss:Vertical="Center"/>';
            echo '<Borders>';
            echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>';
            echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>';
            echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>';
            echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>';
            echo '</Borders>';
            echo '<Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1"/>';
            echo '<Interior ss:Color="#D9E2F3" ss:Pattern="Solid"/>';
            echo '</Style>';

            echo '<Style ss:ID="TextCell">';
            echo '<Alignment ss:Vertical="Center"/>';
            echo '<Borders>';
            echo '<Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/>';
            echo '<Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/>';
            echo '<Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/>';
            echo '<Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/>';
            echo '</Borders>';
            echo '<Font ss:FontName="Calibri" ss:Size="11"/>';
            echo '</Style>';
            echo '</Styles>';

            echo '<Worksheet ss:Name="Staff Expiry Report"><Table>';
            echo '<Column ss:Width="120"/><Column ss:Width="160"/><Column ss:Width="110"/><Column ss:Width="140"/><Column ss:Width="90"/><Column ss:Width="100"/><Column ss:Width="140"/><Column ss:Width="80"/><Column ss:Width="90"/>';

            echo $this->xmlRow([
                'permit_no',
                'traveler_name',
                'passport_number',
                'airport',
                'valid_until',
                'lifecycle_status',
                'issuer',
                'is_extension',
                'parent_permit_id',
            ], 'Header');

            $query
                ->orderBy('valid_until')
                ->orderBy('permit_no')
                ->chunk(500, function ($permits) {
                    foreach ($permits as $permit) {
                        echo $this->xmlRow([
                            $permit->permit_no,
                            $permit->visaApplication?->passenger?->full_name ?? '',
                            $permit->visaApplication?->passenger?->passport_number ?? '',
                            $permit->visaApplication?->airport?->name ?? '',
                            optional($permit->valid_until)->format('Y-m-d') ?: '',
                            PermitLifecycleStatus::value($permit),
                            $permit->issuer?->name ?? '',
                            $permit->is_extension ? 'yes' : 'no',
                            $permit->parent_permit_id ?: '',
                        ], 'TextCell');
                    }

                    flush();
                });

            echo '</Table></Worksheet></Workbook>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    protected function buildExport(
        Request $request,
        string $scope,
        int $days,
        ?string $startDate,
        ?string $endDate,
        string $search
    ): array {
        $query = $this->baseQuery($request);

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
                'staff-expired-active-permits-' . now()->format('Ymd-His') . '.xls',
                $query,
            ];
        }

        if ($startDate !== null && $endDate !== null) {
            $query->whereDate('valid_until', '>=', $startDate)
                ->whereDate('valid_until', '<=', $endDate);

            return [
                'staff-expiring-range-permits-' . now()->format('Ymd-His') . '.xls',
                $query,
            ];
        }

        $query->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->copy()->addDays($days));

        return [
            'staff-expiring-soon-permits-' . now()->format('Ymd-His') . '.xls',
            $query,
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $user = $request->user();

        $query = Permit::query()
            ->with([
                'visaApplication.airport',
                'visaApplication.passenger',
                'issuer',
            ])
            ->whereNotNull('valid_until');

        PermitLifecycleStatus::constrainActive($query);

        if (
            $user &&
            ! $user->hasStaffTitle('system_administrator') &&
            $user->primaryAirport?->id
        ) {
            $query->whereHas('visaApplication', function (Builder $query) use ($user) {
                $query->where('airport_id', $user->primaryAirport->id);
            });
        }

        return $query;
    }

    protected function xmlRow(array $values, string $styleId = 'TextCell'): string
    {
        return '<Row>' . implode('', array_map(
            fn($value) => $this->xmlCell((string) $value, $styleId),
            $values
        )) . '</Row>';
    }

    protected function xmlCell(string $value, string $styleId): string
    {
        $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        return '<Cell ss:StyleID="' . $styleId . '"><Data ss:Type="String">' . $escaped . '</Data></Cell>';
    }
}
