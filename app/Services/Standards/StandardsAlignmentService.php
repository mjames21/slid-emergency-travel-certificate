<?php

namespace App\Services\Standards;

use App\Models\VisaApplication;
use Carbon\Carbon;

class StandardsAlignmentService
{
    public function forForm(array $data, array $passengerHistory = []): array
    {
        return $this->evaluate($data, $passengerHistory);
    }

    public function forApplication(VisaApplication $application, array $passengerHistory = []): array
    {
        $passenger = $application->passenger;

        return $this->evaluate([
            'passport_biodata_available' => filled($passenger?->passport_biodata_image_path),
            'passport_mrz_available' => filled($passenger?->passport_mrz_image_path),
            'passport_mrz_raw_text' => $passenger?->passport_mrz_raw,
            'passport_mrz_result' => $passenger?->passport_mrz_data ?? [],
            'nationality_code' => $passenger?->nationality_code,
            'passport_expiry_date' => $passenger?->passport_expiry_date?->toDateString(),
            'date_of_birth' => $passenger?->date_of_birth?->toDateString(),
            'passport_number' => $passenger?->passport_number,
            'arrival_date' => $application->arrival_date?->toDateString(),
            'valid_from' => $application->valid_from?->toDateString(),
            'period_of_stay_days' => $application->period_of_stay_days,
            'point_of_entry' => $application->pointOfEntry?->name ?: $application->point_of_entry,
            'flight_carrier' => $application->flight_carrier,
            'flight_number' => $application->flight_number,
            'country_of_birth' => $passenger?->country_of_birth,
            'country_of_residence' => $passenger?->country_of_residence,
            'email' => $passenger?->email,
            'phone' => $passenger?->phone,
            'host_name' => $application->host_name,
            'host_address' => $application->host_address,
            'destination_address' => $application->destination_address,
            'remarks' => $application->remarks,
        ], $passengerHistory);
    }

    protected function evaluate(array $data, array $passengerHistory): array
    {
        $sections = [
            [
                'code' => 'icao',
                'label' => 'ICAO',
                'description' => 'Travel document identity, MRZ quality, and border-control readiness.',
                'items' => [
                    $this->check(
                        filled($data['passport_biodata_available'] ?? null),
                        'Passport biodata evidence captured',
                        'Capture or upload the passport biodata page before issuance.'
                    ),
                    $this->check(
                        filled($data['passport_mrz_raw_text'] ?? null),
                        'MRZ read completed',
                        'Run the MRZ reader or record a manual exception.'
                    ),
                    $this->check(
                        $this->mrzChecksPassed($data['passport_mrz_result'] ?? []),
                        'MRZ check digits passed',
                        'Review passport number, birth date, expiry date, optional data, and final MRZ check digits.',
                        filled($data['passport_mrz_raw_text'] ?? null) ? 'warn' : 'warn'
                    ),
                    $this->check(
                        $this->isAlpha3($data['nationality_code'] ?? null),
                        'Nationality code is ICAO-style alpha-3',
                        'Use the three-letter nationality code from the travel document.'
                    ),
                    $this->check(
                        $this->dateIsFuture($data['passport_expiry_date'] ?? null, $data['arrival_date'] ?? null),
                        'Passport valid on arrival',
                        'Passport expiry must be after the intended arrival date.'
                    ),
                    $this->check(
                        blank($data['date_of_birth'] ?? null) || $this->dateIsPast($data['date_of_birth']),
                        'Date of birth is plausible',
                        'Date of birth should be before today.'
                    ),
                ],
            ],
            [
                'code' => 'iata',
                'label' => 'IATA',
                'description' => 'Airline movement, itinerary, and travel-document requirement readiness.',
                'items' => [
                    $this->check(
                        filled($data['point_of_entry'] ?? null),
                        'Point of entry captured',
                        'Select the operational airport or border point.'
                    ),
                    $this->check(
                        filled($data['arrival_date'] ?? null) && filled($data['valid_from'] ?? null) && filled($data['period_of_stay_days'] ?? null),
                        'Travel dates and stay duration captured',
                        'Arrival date, validity start, and stay duration are required for operational checks.'
                    ),
                    $this->check(
                        filled($data['flight_carrier'] ?? null),
                        'Carrier or airline captured',
                        'Record the airline/carrier name or designator from the passenger movement.'
                    ),
                    $this->check(
                        filled($data['flight_number'] ?? null) && $this->flightNumberLooksOperational($data['flight_number']),
                        'Flight number captured',
                        'Use the airline movement number, for example KQ510 or 510.'
                    ),
                    $this->check(
                        false,
                        'Travel requirement source verified',
                        'Manual Timatic or government-source entry requirement verification is still required until an external rules feed is integrated.',
                        'warn'
                    ),
                ],
            ],
            [
                'code' => 'iom',
                'label' => 'IOM',
                'description' => 'Migration management, continuity, contactability, and assistance cues.',
                'items' => [
                    $this->check(
                        filled($data['passport_number'] ?? null),
                        'Traveler history lookup available',
                        ($passengerHistory['found'] ?? false)
                            ? 'Prior traveler history is attached to this passport number.'
                            : 'No prior traveler history found for this passport number.'
                    ),
                    $this->check(
                        filled($data['country_of_birth'] ?? null) && filled($data['country_of_residence'] ?? null),
                        'Country of birth and residence captured',
                        'Capture origin and residence context for migration-management continuity.'
                    ),
                    $this->check(
                        filled($data['email'] ?? null) || filled($data['phone'] ?? null),
                        'Traveler contact captured',
                        'Record at least one traveler contact method where available.',
                        'warn'
                    ),
                    $this->check(
                        filled($data['host_name'] ?? null) || filled($data['host_address'] ?? null) || filled($data['destination_address'] ?? null),
                        'Host or destination captured',
                        'Record destination, host, or in-country address.'
                    ),
                    $this->check(
                        filled($data['remarks'] ?? null),
                        'Migration or assistance observations recorded',
                        'Use remarks for assistance needs, referral notes, or manual migration-management observations.',
                        'warn'
                    ),
                ],
            ],
        ];

        return [
            'sections' => array_map(fn (array $section): array => $this->summarizeSection($section), $sections),
            'summary' => $this->summary($sections),
        ];
    }

    protected function check(bool $passes, string $label, string $detail, string $missingStatus = 'fail'): array
    {
        return [
            'status' => $passes ? 'pass' : $missingStatus,
            'label' => $label,
            'detail' => $detail,
        ];
    }

    protected function summarizeSection(array $section): array
    {
        $items = collect($section['items']);

        $section['counts'] = [
            'pass' => $items->where('status', 'pass')->count(),
            'warn' => $items->where('status', 'warn')->count(),
            'fail' => $items->where('status', 'fail')->count(),
            'total' => $items->count(),
        ];

        return $section;
    }

    protected function summary(array $sections): array
    {
        $items = collect($sections)->flatMap(fn (array $section) => $section['items']);
        $total = max($items->count(), 1);
        $passes = $items->where('status', 'pass')->count();

        return [
            'score' => (int) round(($passes / $total) * 100),
            'pass' => $passes,
            'warn' => $items->where('status', 'warn')->count(),
            'fail' => $items->where('status', 'fail')->count(),
            'total' => $total,
        ];
    }

    protected function mrzChecksPassed(array $mrzResult): bool
    {
        $checks = $mrzResult['checks'] ?? [];

        return $checks !== [] && collect($checks)->every(fn ($value) => $value === true);
    }

    protected function isAlpha3(?string $value): bool
    {
        return is_string($value) && preg_match('/^[A-Z]{3}$/', strtoupper(trim($value))) === 1;
    }

    protected function dateIsFuture(?string $date, ?string $relativeDate = null): bool
    {
        if (blank($date)) {
            return false;
        }

        $threshold = filled($relativeDate) ? Carbon::parse($relativeDate) : today();

        return Carbon::parse($date)->greaterThanOrEqualTo($threshold);
    }

    protected function dateIsPast(?string $date): bool
    {
        if (blank($date)) {
            return false;
        }

        return Carbon::parse($date)->isPast();
    }

    protected function flightNumberLooksOperational(?string $value): bool
    {
        if (blank($value)) {
            return false;
        }

        return preg_match('/^[A-Z0-9]{1,3}\s?\d{1,4}[A-Z]?$/i', trim($value)) === 1
            || preg_match('/^\d{1,4}[A-Z]?$/i', trim($value)) === 1;
    }
}
