<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Country;
use App\Models\Nationality;
use App\Models\PointOfEntry;
use App\Models\PurposeOfVisit;
use App\Models\VisaApplication;
use App\Services\Evisa\CreateOnlineEvisaApplicationService;
use App\Services\Evisa\InitiateOnlineEvisaPaymentService;
use App\Services\Mrz\ExtractPassportMrzService;
use App\Services\Mrz\MrzParser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class EvisaApplicationController extends Controller
{
    public function create(): View
    {
        return view('evisa.apply', [
            'airports' => Airport::query()->orderBy('name')->get(),
            'countries' => Country::query()
                ->where('active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'nationalities' => Nationality::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'pointsOfEntry' => PointOfEntry::query()->orderBy('name')->get(),
            'purposesOfVisit' => PurposeOfVisit::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'flightCarriers' => config('flight_carriers'),
        ]);
    }

    public function store(Request $request, CreateOnlineEvisaApplicationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'applicant_category' => ['required', Rule::in(['adult', 'child'])],
            'regional_category' => ['required', Rule::in(['ecowas', 'non_ecowas'])],
            'identity_document_type' => ['required', Rule::in(['passport', 'nin', 'other'])],
            'surname' => ['required', 'string', 'max:255'],
            'given_names' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:255'],
            'nationality_code' => ['required', 'string', 'size:3'],
            'passport_number' => ['required', 'string', 'max:50'],
            'passport_biodata_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'applicant_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'mrz_line_1' => ['nullable', 'string', 'max:64'],
            'mrz_line_2' => ['nullable', 'string', 'max:64'],
            'passport_expiry_year' => ['nullable', 'digits:4'],
            'passport_expiry_month' => ['nullable', 'regex:/^(0?[1-9]|1[0-2])$/'],
            'passport_expiry_day' => ['nullable', 'regex:/^(0?[1-9]|[12][0-9]|3[01])$/'],
            'sex' => ['nullable', 'string', 'max:10'],
            'date_of_birth_year' => ['required', 'digits:4'],
            'date_of_birth_month' => ['required', 'regex:/^(0?[1-9]|1[0-2])$/'],
            'date_of_birth_day' => ['required', 'regex:/^(0?[1-9]|[12][0-9]|3[01])$/'],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'country_of_birth' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('countries', 'name')->where('active', true),
            ],
            'country_of_residence' => [
                'nullable',
                'string',
                'max:255',
                Rule::exists('countries', 'name')->where('active', true),
            ],
            'applicant_address' => ['required', 'string', 'max:1000'],
            'occupation' => ['required', 'string', 'max:255'],
            'marital_status' => ['nullable', Rule::in(['single', 'married', 'divorced', 'widowed', 'separated', 'other'])],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'airport_id' => ['nullable', 'integer', 'exists:airports,id'],
            'point_of_entry_id' => ['nullable', 'integer', 'exists:points_of_entry,id'],
            'point_of_entry' => ['nullable', 'string', 'max:255'],
            'purpose_of_visit' => ['required', 'string', 'max:255'],
            'period_of_stay_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'arrival_date' => ['nullable', 'date'],
            'destination_country' => ['required', 'string', 'max:255'],
            'destination_address' => ['nullable', 'string', 'max:1000'],
            'flight_carrier' => ['nullable', 'string', 'max:255'],
            'flight_number' => ['nullable', 'string', 'max:255'],
            'flight_details' => ['nullable', 'string', 'max:1000'],
            'accommodation_type' => ['nullable', Rule::in(['hotel', 'host', 'family', 'company', 'other'])],
            'accommodation_name' => ['nullable', 'string', 'max:255'],
            'booking_reference' => ['nullable', 'string', 'max:255'],
            'booking_confirmation_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'host_name' => ['nullable', 'string', 'max:255'],
            'host_address' => ['nullable', 'string', 'max:1000'],
            'host_phone' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'employment_status' => ['nullable', Rule::in(['employed', 'self_employed', 'student', 'retired', 'unemployed', 'other'])],
            'employer_name' => ['nullable', 'string', 'max:255'],
            'employer_address' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_email' => ['nullable', 'email', 'max:255'],
            'guardian_name' => ['nullable', 'required_if:applicant_category,child', 'string', 'max:255'],
            'guardian_relationship' => ['nullable', 'required_if:applicant_category,child', 'string', 'max:255'],
            'guardian_address' => ['nullable', 'required_if:applicant_category,child', 'string', 'max:1000'],
            'guardian_phone' => ['nullable', 'required_if:applicant_category,child', 'string', 'max:50'],
            'guardian_sex' => ['nullable', 'string', 'max:20'],
            'travel_history_countries' => ['nullable', 'string', 'max:1000'],
            'previous_sierra_leone_visit' => ['nullable', Rule::in(['yes', 'no'])],
            'previous_sierra_leone_visit_details' => ['nullable', 'string', 'max:1000'],
            'previous_visa_refusal' => ['nullable', Rule::in(['yes', 'no'])],
            'previous_visa_refusal_details' => ['nullable', 'string', 'max:1000'],
            'previous_deportation' => ['nullable', Rule::in(['yes', 'no'])],
            'previous_deportation_details' => ['nullable', 'string', 'max:1000'],
            'criminal_conviction' => ['nullable', Rule::in(['yes', 'no'])],
            'criminal_conviction_details' => ['nullable', 'string', 'max:1000'],
            'security_or_watchlist_issue' => ['nullable', Rule::in(['yes', 'no'])],
            'security_or_watchlist_issue_details' => ['nullable', 'string', 'max:1000'],
            'infectious_disease_risk' => ['nullable', Rule::in(['yes', 'no'])],
            'infectious_disease_risk_details' => ['nullable', 'string', 'max:1000'],
            'applicant_certification' => ['accepted'],
        ]);

        $validated['passport_expiry_date'] = $this->dateFromParts(
            $validated,
            'passport_expiry',
            required: false,
            label: 'passport expiry date'
        ) ?: today()->addYear()->toDateString();

        $validated['date_of_birth'] = $this->dateFromParts(
            $validated,
            'date_of_birth',
            required: true,
            label: 'date of birth'
        );

        if (! empty($validated['passport_expiry_date']) && $validated['passport_expiry_date'] <= today()->toDateString()) {
            throw ValidationException::withMessages([
                'passport_expiry_year' => 'Passport expiry date must be after today.',
            ]);
        }

        if (! empty($validated['date_of_birth']) && $validated['date_of_birth'] >= today()->toDateString()) {
            throw ValidationException::withMessages([
                'date_of_birth_year' => 'Date of birth must be before today.',
            ]);
        }

        $validated['airport_id'] = $validated['airport_id'] ?? Airport::query()->orderBy('id')->value('id');
        $validated['period_of_stay_days'] = (int) ($validated['period_of_stay_days'] ?? 30);
        $validated['arrival_date'] = $validated['arrival_date'] ?? today()->toDateString();
        $validated['point_of_entry'] = ($validated['point_of_entry'] ?? null) ?: 'Emergency Travel Certificate Desk';
        $validated['identity_document_number'] = strtoupper($validated['passport_number']);
        $validated['country_of_residence'] = ($validated['country_of_residence'] ?? null) ?: $validated['nationality'];

        foreach ([
            'previous_sierra_leone_visit' => 'previous_sierra_leone_visit_details',
            'previous_visa_refusal' => 'previous_visa_refusal_details',
            'previous_deportation' => 'previous_deportation_details',
            'criminal_conviction' => 'criminal_conviction_details',
            'security_or_watchlist_issue' => 'security_or_watchlist_issue_details',
            'infectious_disease_risk' => 'infectious_disease_risk_details',
        ] as $answerField => $detailsField) {
            if (($validated[$answerField] ?? null) === 'yes' && blank($validated[$detailsField] ?? null)) {
                throw ValidationException::withMessages([
                    $detailsField => 'Provide details for any question answered yes.',
                ]);
            }
        }

        if (! empty($validated['point_of_entry_id'])) {
            $validated['point_of_entry'] = PointOfEntry::query()
                ->whereKey($validated['point_of_entry_id'])
                ->value('name') ?: $validated['point_of_entry'];
        }

        $validated['passport_biodata_image_path'] = $request
            ->file('passport_biodata_image')
            ->store('etc/passports', 'local');

        $validated['applicant_photo_path'] = $request
            ->file('applicant_photo')
            ->store('etc/applicant-photos', 'local');

        if ($request->hasFile('booking_confirmation_image')) {
            $validated['booking_confirmation_image_path'] = $request
                ->file('booking_confirmation_image')
                ->store('etc/bookings', 'local');
        }

        $validated['travel_history'] = [
            'applicant_category' => $validated['applicant_category'],
            'regional_category' => $validated['regional_category'],
            'destination_country' => $validated['destination_country'],
            'countries_visited_last_five_years' => $validated['travel_history_countries'] ?? null,
            'previous_sierra_leone_visit' => $validated['previous_sierra_leone_visit'] ?? null,
            'previous_sierra_leone_visit_details' => $validated['previous_sierra_leone_visit_details'] ?? null,
        ];

        $validated['immigration_history'] = [
            'previous_visa_refusal' => $validated['previous_visa_refusal'] ?? null,
            'previous_visa_refusal_details' => $validated['previous_visa_refusal_details'] ?? null,
            'previous_deportation' => $validated['previous_deportation'] ?? null,
            'previous_deportation_details' => $validated['previous_deportation_details'] ?? null,
        ];

        $validated['security_declarations'] = [
            'criminal_conviction' => $validated['criminal_conviction'] ?? null,
            'criminal_conviction_details' => $validated['criminal_conviction_details'] ?? null,
            'security_or_watchlist_issue' => $validated['security_or_watchlist_issue'] ?? null,
            'security_or_watchlist_issue_details' => $validated['security_or_watchlist_issue_details'] ?? null,
            'infectious_disease_risk' => $validated['infectious_disease_risk'] ?? null,
            'infectious_disease_risk_details' => $validated['infectious_disease_risk_details'] ?? null,
        ];

        $validated['applicant_certification_ip'] = $request->ip();

        $validated = array_merge($validated, $this->submittedMrzPayload($validated));

        $application = $service->handle($validated);

        return redirect()->route('etc.status', $application->public_access_token)
            ->with('success', 'Your Emergency Travel Certificate application has been submitted. Continue to online payment.');
    }

    public function readPassport(
        Request $request,
        ExtractPassportMrzService $extractService,
        MrzParser $parser
    ): JsonResponse {
        $validated = Validator::make($request->all(), [
            'passport_biodata_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'mrz_line_1' => ['nullable', 'string', 'max:64'],
            'mrz_line_2' => ['nullable', 'string', 'max:64'],
        ])->validate();

        try {
            $line1 = trim((string) ($validated['mrz_line_1'] ?? ''));
            $line2 = trim((string) ($validated['mrz_line_2'] ?? ''));

            if ($line1 !== '' && $line2 !== '') {
                $parsed = $parser->parsePassport($line1."\n".$line2);

                if (! ($parsed['ok'] ?? false)) {
                    return response()->json([
                        'ok' => false,
                        'message' => 'The typed MRZ lines could not be validated. Check the characters and edit the passport details manually if needed.',
                    ]);
                }

                return response()->json([
                    'ok' => true,
                    'message' => 'Typed MRZ was read. Review and edit the details if anything looks wrong.',
                    'parsed' => $this->publicParsedMrz($parsed),
                    'checks' => $parsed['checks'] ?? [],
                    'confidence' => $parsed['confidence'] ?? null,
                ]);
            }

            if (! $request->hasFile('passport_biodata_image')) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Upload a clear passport image or type the two MRZ lines to continue.',
                ], 422);
            }

            $result = $extractService->handle($validated['passport_biodata_image']->getRealPath());

            return response()->json([
                'ok' => true,
                'message' => 'Passport MRZ was read. Review and edit the details if anything looks wrong.',
                'parsed' => $result['parsed'] ?? [],
                'checks' => $result['checks'] ?? [],
                'confidence' => $result['confidence'] ?? null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'ok' => false,
                'message' => 'We could not read the MRZ clearly. You can still continue and enter the passport details manually.',
            ]);
        }
    }

    protected function submittedMrzPayload(array $data): array
    {
        $line1 = trim((string) ($data['mrz_line_1'] ?? ''));
        $line2 = trim((string) ($data['mrz_line_2'] ?? ''));

        if ($line1 === '' || $line2 === '') {
            return [];
        }

        $parsed = app(MrzParser::class)->parsePassport($line1."\n".$line2);

        return [
            'passport_mrz_raw' => $line1."\n".$line2,
            'passport_mrz_data' => $parsed,
            'passport_mrz_confidence' => $parsed['confidence'] ?? null,
        ];
    }

    protected function dateFromParts(array $data, string $prefix, bool $required, string $label): ?string
    {
        $year = $data[$prefix.'_year'] ?? null;
        $month = $data[$prefix.'_month'] ?? null;
        $day = $data[$prefix.'_day'] ?? null;
        $hasAny = filled($year) || filled($month) || filled($day);

        if (! $required && ! $hasAny) {
            return null;
        }

        if (! filled($year) || ! filled($month) || ! filled($day) || ! checkdate((int) $month, (int) $day, (int) $year)) {
            throw ValidationException::withMessages([
                $prefix.'_year' => 'Enter a valid '.$label.'.',
            ]);
        }

        return sprintf('%04d-%02d-%02d', (int) $year, (int) $month, (int) $day);
    }

    protected function publicParsedMrz(array $parsed): array
    {
        return [
            'document_type' => $parsed['document_type'] ?? null,
            'issuing_country' => $parsed['issuing_country'] ?? null,
            'surname' => $parsed['surname'] ?? null,
            'given_names' => $parsed['given_names'] ?? null,
            'passport_number' => $parsed['passport_number'] ?? null,
            'nationality_code' => $parsed['nationality_code'] ?? null,
            'date_of_birth' => $parsed['date_of_birth'] ?? null,
            'sex' => $parsed['sex'] ?? null,
            'passport_expiry_date' => $parsed['passport_expiry_date'] ?? null,
        ];
    }

    public function status(string $token): View
    {
        return view('evisa.status', [
            'application' => $this->publicApplication($token),
        ]);
    }

    public function pay(string $token, InitiateOnlineEvisaPaymentService $service): RedirectResponse
    {
        $application = $this->publicApplication($token);
        $result = $service->handle($application);

        if (! empty($result['checkout_url'])) {
            return redirect()->away($result['checkout_url']);
        }

        $message = match ($result['status'] ?? null) {
            'already_paid' => 'Payment is already confirmed. Your application is pending HQ review.',
            'sandbox_registered' => 'Payment request staged locally. WanGov credentials are not enabled in this environment.',
            default => 'Online payment request is ready. Complete payment using the GovPay checkout button.',
        };

        return redirect()->route('etc.status', $token)
            ->with('success', $message)
            ->with('auto_checkout_reference', $application->latestInvoice?->payment_reference);
    }

    protected function publicApplication(string $token): VisaApplication
    {
        return VisaApplication::query()
            ->with(['passenger', 'latestInvoice.payments', 'permit'])
            ->where('public_access_token', $token)
            ->where('application_channel', VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE)
            ->firstOrFail();
    }
}
