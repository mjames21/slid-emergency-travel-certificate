<?php
// FILE: app/Livewire/Staff/Applications/Create.php

namespace App\Livewire\Staff\Applications;

use App\Models\Airport;
use App\Models\Desk;
use App\Models\Nationality;
use App\Models\Passenger;
use App\Models\PointOfEntry;
use App\Models\PurposeOfVisit;
use App\Models\SexOption;
use App\Models\VisaApplication;
use App\Services\Billing\FeeResolverService;
use App\Services\Billing\GenerateInvoiceService;
use App\Services\Mrz\ExtractPassportMrzService;
use App\Services\Passenger\BuildPassengerHistoryService;
use App\Services\Standards\StandardsAlignmentService;
use App\Support\ApplicationNumberGenerator;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public int $step = 1;
    public bool $showMrzReviewModal = false;
    public ?string $stepSuccessMessage = null;

    public ?int $airport_id = null;
    public ?int $desk_id = null;

    public string $surname = '';
    public string $given_names = '';
    public string $nationality = '';
    public string $nationality_code = '';
    public string $passport_number = '';

    public string $passport_expiry_year = '';
    public string $passport_expiry_month = '';
    public string $passport_expiry_day = '';

    public string $sex = '';
    public string $date_of_birth_year = '';
    public string $date_of_birth_month = '';
    public string $date_of_birth_day = '';

    public string $country_of_birth = '';
    public string $country_of_residence = '';
    public string $occupation = '';
    public string $email = '';
    public string $phone = '';

    public $passport_biodata_image = null;
    public $passport_mrz_crop_image = null;

    public ?string $passport_biodata_path = null;
    public ?string $passport_mrz_crop_path = null;
    public ?string $passport_biodata_preview_url = null;
    public ?string $passport_mrz_crop_preview_url = null;
    public ?string $passport_mrz_raw_text = null;
    public array $passport_mrz_result = [];

    public string $purpose_of_visit = '';
    public ?int $point_of_entry_id = null;
    public string $period_of_stay_days = '';
    public string $period_of_stay_text = '';
    public string $arrival_date = '';
    public string $valid_from = '';
    public string $valid_until = '';
    public string $flight_carrier = '';
    public string $flight_number = '';
    public string $flight_details = '';

    public string $host_name = '';
    public string $host_address = '';
    public string $destination_address = '';
    public string $destination_phone = '';
    public string $remarks = '';

    public array $passengerHistory = [];

    public function mount(BuildPassengerHistoryService $historyService): void
    {
        $user = auth()->user();

        $this->airport_id = $user?->primaryAirport?->id;
        $this->desk_id = $user?->primaryDesk?->id;
        $this->point_of_entry_id = $this->resolveDefaultPointOfEntryId();

        $this->arrival_date = now()->toDateString();
        $this->valid_from = now()->toDateString();
        $this->period_of_stay_days = '30';

        $this->syncPeriodOfStayText();
        $this->syncValidUntil();

        $this->passengerHistory = $historyService->handle($this->passport_number);
    }

    public function updatedAirportId(): void
    {
        $this->point_of_entry_id = $this->resolveDefaultPointOfEntryId();
    }

    public function updatedNationalityCode(): void
    {
        $this->nationality_code = strtoupper(trim($this->nationality_code));
        $this->syncNationalityFromCode();
    }

    public function updatedNationality(): void
    {
        $this->nationality = trim($this->nationality);
        $this->syncNationalityFromName();
    }

    public function updatedPassportNumber(BuildPassengerHistoryService $historyService): void
    {
        $this->passport_number = strtoupper(trim($this->passport_number));
        $this->passengerHistory = $historyService->handle($this->passport_number);
    }

    public function updatedPassportBiodataImage(): void
    {
        $this->validateOnly('passport_biodata_image');

        if (! $this->passport_biodata_image) {
            return;
        }

        if ($this->passport_biodata_path) {
            Storage::disk('local')->delete($this->passport_biodata_path);
        }

        $path = $this->passport_biodata_image->store('applications/passports', 'local');

        $this->passport_biodata_path = $path;
        $this->passport_biodata_preview_url = $this->passport_biodata_image->temporaryUrl();
    }

    public function updatedPassportMrzCropImage(): void
    {
        $this->validateOnly('passport_mrz_crop_image');

        if (! $this->passport_mrz_crop_image) {
            return;
        }

        if ($this->passport_mrz_crop_path) {
            Storage::disk('local')->delete($this->passport_mrz_crop_path);
        }

        $path = $this->passport_mrz_crop_image->store('applications/passports/mrz', 'local');

        $this->passport_mrz_crop_path = $path;
        $this->passport_mrz_crop_preview_url = $this->passport_mrz_crop_image->temporaryUrl();
    }

    public function updatedPeriodOfStayDays(): void
    {
        $this->syncPeriodOfStayText();
        $this->syncValidUntil();
    }

    public function updatedValidFrom(): void
    {
        $this->syncValidUntil();
    }

    public function updatedArrivalDate(): void
    {
        if ($this->valid_from === '') {
            $this->valid_from = $this->arrival_date;
        }

        $this->syncValidUntil();
    }

    public function readPassportMrz(
        ExtractPassportMrzService $extractService,
        BuildPassengerHistoryService $historyService
    ): void {
        if (! $this->passport_biodata_path && ! $this->passport_mrz_crop_path) {
            $this->addError('passport_biodata_path', 'Capture or upload a passport image first.');
            return;
        }

        $relativePath = $this->passport_mrz_crop_path ?: $this->passport_biodata_path;
        $absolutePath = Storage::disk('local')->path($relativePath);

        $result = $extractService->handle($absolutePath);

        $this->passport_mrz_result = $result['parsed'] ?? [];
        $this->passport_mrz_raw_text = $result['ocr_text'] ?? null;

        $this->applyMrzResult($this->passport_mrz_result);
        $this->refreshPassengerHistory($historyService);

        $this->showMrzReviewModal = true;
    }

    public function closeMrzReviewModal(): void
    {
        $this->showMrzReviewModal = false;
    }

    public function confirmMrzReviewAndContinue(): void
    {
        $this->showMrzReviewModal = false;
        $this->step = 2;
        $this->stepSuccessMessage = 'MRZ confirmed. Continue with Traveler Details.';
    }

    public function clearStepSuccessMessage(): void
    {
        $this->stepSuccessMessage = null;
    }

    public function removePassportBiodata(): void
    {
        if ($this->passport_biodata_path) {
            Storage::disk('local')->delete($this->passport_biodata_path);
        }

        if ($this->passport_mrz_crop_path) {
            Storage::disk('local')->delete($this->passport_mrz_crop_path);
        }

        $this->passport_biodata_image = null;
        $this->passport_mrz_crop_image = null;
        $this->passport_biodata_path = null;
        $this->passport_mrz_crop_path = null;
        $this->passport_biodata_preview_url = null;
        $this->passport_mrz_crop_preview_url = null;
        $this->passport_mrz_raw_text = null;
        $this->passport_mrz_result = [];
        $this->showMrzReviewModal = false;
    }

    public function nextStep(): void
    {
        $this->stepSuccessMessage = null;

        if ($this->step === 1) {
            if (! $this->passport_biodata_path && ! $this->passport_mrz_crop_path) {
                $this->addError('passport_biodata_path', 'Capture or upload a passport image before continuing.');
                return;
            }
        }

        $rules = $this->rulesForStep($this->step);

        if ($rules !== []) {
            $this->validate($rules);
        }

        $this->step = min($this->step + 1, 5);
    }

    public function previousStep(): void
    {
        $this->stepSuccessMessage = null;
        $this->step = max($this->step - 1, 1);
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules());
        $validated['passport_number'] = strtoupper(trim($validated['passport_number']));
        $validated['nationality'] = trim($validated['nationality']);
        $validated['nationality_code'] = strtoupper(trim($validated['nationality_code']));

        $airport = Airport::query()->findOrFail($validated['airport_id']);
        $applicationNo = app(ApplicationNumberGenerator::class)->generate($airport);

        $passengerPayload = $this->filterExistingColumns('passengers', $this->buildPassengerPayload($validated));
        $passenger = $this->savePassengerIdentity($passengerPayload);

        $application = new VisaApplication();
        $application->forceFill($this->filterExistingColumns('visa_applications', $this->buildApplicationPayload($validated, $passenger->id, $applicationNo)));
        $application->save();

        $fee = app(FeeResolverService::class)->resolve($application->fresh(['airport', 'passenger']));
        $invoice = app(GenerateInvoiceService::class)->handle(
            auth()->user(),
            $application->fresh(['airport', 'passenger']),
            (float) $fee['amount'],
            (string) $fee['currency']
        );

        session()->flash('success', 'Application created successfully. Continue to traveler payment checkout.');

        $this->redirectRoute('staff.invoices.show', $invoice);
    }

    protected function savePassengerIdentity(array $payload): Passenger
    {
        try {
            $passenger = Passenger::query()->firstOrNew([
                'passport_number' => $payload['passport_number'],
                'nationality' => $payload['nationality'],
            ]);

            $passenger->forceFill($this->payloadForPassengerSave($passenger, $payload));
            $passenger->save();

            return $passenger;
        } catch (UniqueConstraintViolationException $exception) {
            $passenger = Passenger::query()
                ->where('passport_number', $payload['passport_number'])
                ->where('nationality', $payload['nationality'])
                ->first();

            if (! $passenger) {
                throw $exception;
            }

            $passenger->forceFill($this->payloadForPassengerSave($passenger, $payload));
            $passenger->save();

            return $passenger;
        }
    }

    protected function payloadForPassengerSave(Passenger $passenger, array $payload): array
    {
        if (! $passenger->exists) {
            return $payload;
        }

        $evidenceFields = [
            'passport_biodata_image_path',
            'passport_biodata_captured_at',
            'passport_biodata_captured_by',
            'passport_biodata_capture_device',
            'passport_mrz_image_path',
            'passport_mrz_raw',
            'passport_mrz_data',
            'passport_mrz_confidence',
            'passport_mrz_extracted_at',
            'passport_mrz_extracted_by',
        ];

        foreach ($evidenceFields as $field) {
            if (array_key_exists($field, $payload) && $payload[$field] === null) {
                unset($payload[$field]);
            }
        }

        return $payload;
    }

    public function render(StandardsAlignmentService $standardsService): View
    {
        $pointsOfEntry = $this->pointOfEntryTableExists()
            ? PointOfEntry::query()
                ->when($this->airport_id, fn ($query) => $query->where('airport_id', $this->airport_id))
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.staff.applications.create', [
            'airports' => Airport::query()->orderBy('name')->get(),
            'desks' => Desk::query()
                ->when($this->airport_id, fn ($query) => $query->where('airport_id', $this->airport_id))
                ->orderBy('name')
                ->get(),
            'nationalities' => Nationality::query()->orderBy('name')->get(),
            'sexOptions' => SexOption::query()->orderBy('sort_order')->get(),
            'purposesOfVisit' => PurposeOfVisit::query()->orderBy('name')->get(),
            'pointsOfEntry' => $pointsOfEntry,
            'flightCarriers' => config('flight_carriers'),
            'standardsReadiness' => $standardsService->forForm($this->standardsReadinessPayload(), $this->passengerHistory),
        ]);
    }

    protected function refreshPassengerHistory(BuildPassengerHistoryService $historyService): void
    {
        $this->passengerHistory = $historyService->handle($this->passport_number);
    }

    protected function resolveDefaultPointOfEntryId(): ?int
    {
        if (! $this->pointOfEntryTableExists()) {
            return null;
        }

        if (! $this->airport_id) {
            return null;
        }

        return PointOfEntry::query()
            ->where('airport_id', $this->airport_id)
            ->value('id');
    }

    protected function pointOfEntryTableExists(): bool
    {
        return Schema::hasTable('points_of_entry');
    }

    protected function syncNationalityFromCode(): void
    {
        if ($this->nationality_code === '') {
            return;
        }

        $match = Nationality::query()
            ->where('code', $this->nationality_code)
            ->first();

        if (! $match) {
            return;
        }

        $this->nationality = $match->name;
        $this->applyCountryDefaultsFromNationality($match->name);
    }

    protected function syncNationalityFromName(): void
    {
        if ($this->nationality === '') {
            return;
        }

        $match = Nationality::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower($this->nationality)])
            ->first();

        if ($match) {
            $this->nationality = $match->name;
            $this->nationality_code = $match->code;
            $this->applyCountryDefaultsFromNationality($match->name);
            return;
        }

        $this->applyCountryDefaultsFromNationality($this->nationality);
    }

    protected function applyCountryDefaultsFromNationality(?string $countryName): void
    {
        $countryName = trim((string) $countryName);

        if ($countryName === '') {
            return;
        }

        if ($this->country_of_birth === '') {
            $this->country_of_birth = $countryName;
        }

        if ($this->country_of_residence === '') {
            $this->country_of_residence = $countryName;
        }
    }

    protected function applyMrzResult(array $result): void
    {
        $this->surname = (string) ($result['surname'] ?? $this->surname);
        $this->given_names = (string) ($result['given_names'] ?? $this->given_names);
        $this->passport_number = strtoupper((string) ($result['passport_number'] ?? $this->passport_number));
        $this->nationality_code = strtoupper((string) ($result['nationality_code'] ?? $this->nationality_code));
        $this->sex = (string) ($result['sex'] ?? $this->sex);

        if (! empty($result['passport_expiry_date'])) {
            [$year, $month, $day] = explode('-', $result['passport_expiry_date']);
            $this->passport_expiry_year = $year;
            $this->passport_expiry_month = $month;
            $this->passport_expiry_day = $day;
        }

        if (! empty($result['date_of_birth'])) {
            [$year, $month, $day] = explode('-', $result['date_of_birth']);
            $this->date_of_birth_year = $year;
            $this->date_of_birth_month = $month;
            $this->date_of_birth_day = $day;
        }

        $this->syncNationalityFromCode();
    }

    protected function syncPeriodOfStayText(): void
    {
        $days = (int) $this->period_of_stay_days;

        if ($days <= 0) {
            $this->period_of_stay_text = '';
            return;
        }

        if ($days === 30) {
            $this->period_of_stay_text = 'ONE (1) MONTH';
            return;
        }

        if ($days === 60) {
            $this->period_of_stay_text = 'TWO (2) MONTHS';
            return;
        }

        if ($days === 90) {
            $this->period_of_stay_text = 'THREE (3) MONTHS';
            return;
        }

        $this->period_of_stay_text = $days . ' DAYS';
    }

    protected function syncValidUntil(): void
    {
        if ($this->valid_from === '' || $this->period_of_stay_days === '') {
            $this->valid_until = '';
            return;
        }

        $days = max((int) $this->period_of_stay_days, 0);

        $this->valid_until = Carbon::parse($this->valid_from)
            ->addDays($days)
            ->toDateString();
    }

    protected function passportExpiryDate(): ?string
    {
        if (
            $this->passport_expiry_year === '' ||
            $this->passport_expiry_month === '' ||
            $this->passport_expiry_day === ''
        ) {
            return null;
        }

        return sprintf(
            '%04d-%02d-%02d',
            (int) $this->passport_expiry_year,
            (int) $this->passport_expiry_month,
            (int) $this->passport_expiry_day
        );
    }

    protected function dateOfBirth(): ?string
    {
        if (
            $this->date_of_birth_year === '' ||
            $this->date_of_birth_month === '' ||
            $this->date_of_birth_day === ''
        ) {
            return null;
        }

        return sprintf(
            '%04d-%02d-%02d',
            (int) $this->date_of_birth_year,
            (int) $this->date_of_birth_month,
            (int) $this->date_of_birth_day
        );
    }

    protected function buildPassengerPayload(array $validated): array
    {
        $fullName = trim(preg_replace('/\s+/', ' ', $this->surname . ' ' . $this->given_names));

        return array_merge([
            'surname' => $validated['surname'],
            'given_names' => $validated['given_names'],
            'full_name' => $fullName !== '' ? $fullName : null,
            'nationality' => $validated['nationality'],
            'nationality_code' => strtoupper($validated['nationality_code']),
            'passport_number' => strtoupper($validated['passport_number']),
            'passport_expiry_date' => $this->passportExpiryDate(),
            'sex' => $validated['sex'] !== '' ? $validated['sex'] : null,
            'date_of_birth' => $this->dateOfBirth(),
            'country_of_birth' => $validated['country_of_birth'] !== '' ? $validated['country_of_birth'] : null,
            'country_of_residence' => $validated['country_of_residence'] !== '' ? $validated['country_of_residence'] : null,
            'occupation' => $validated['occupation'] !== '' ? $validated['occupation'] : null,
            'email' => $validated['email'] !== '' ? $validated['email'] : null,
            'phone' => $validated['phone'] !== '' ? $validated['phone'] : null,
        ], $this->passportEvidencePayload());
    }

    protected function buildApplicationPayload(array $validated, int $passengerId, string $applicationNo): array
    {
        $pointOfEntryName = $this->pointOfEntryTableExists()
            ? PointOfEntry::query()->whereKey($validated['point_of_entry_id'])->value('name')
            : null;

        return [
            'application_no' => $applicationNo,
            'airport_id' => $validated['airport_id'],
            'desk_id' => $validated['desk_id'] ?: null,
            'passenger_id' => $passengerId,
            'created_by' => auth()->id(),
            'point_of_entry_id' => $this->pointOfEntryTableExists() ? ($validated['point_of_entry_id'] ?: null) : null,
            'point_of_entry' => $pointOfEntryName ?: 'Pending point of entry',
            'purpose_of_visit' => $validated['purpose_of_visit'],
            'period_of_stay_days' => (int) $validated['period_of_stay_days'],
            'period_of_stay_text' => $this->period_of_stay_text !== '' ? $this->period_of_stay_text : null,
            'arrival_date' => $validated['arrival_date'],
            'valid_from' => $validated['valid_from'],
            'valid_until' => $validated['valid_until'],
            'flight_carrier' => $validated['flight_carrier'] !== '' ? $validated['flight_carrier'] : null,
            'flight_number' => $validated['flight_number'] !== '' ? $validated['flight_number'] : null,
            'flight_details' => $validated['flight_details'] !== '' ? $validated['flight_details'] : null,
            'host_name' => $validated['host_name'] !== '' ? $validated['host_name'] : null,
            'host_address' => $validated['host_address'] !== '' ? $validated['host_address'] : null,
            'destination_address' => $validated['destination_address'] !== '' ? $validated['destination_address'] : null,
            'destination_phone' => $validated['destination_phone'] !== '' ? $validated['destination_phone'] : null,
            'reference' => Str::uuid()->toString(),
            'remarks' => $validated['remarks'] !== '' ? $validated['remarks'] : null,
        ];
    }

    protected function passportEvidencePayload(): array
    {
        return [
            'passport_biodata_image_path' => $this->passport_biodata_path,
            'passport_biodata_captured_at' => $this->passport_biodata_path ? now() : null,
            'passport_biodata_captured_by' => $this->passport_biodata_path ? auth()->id() : null,
            'passport_biodata_capture_device' => $this->passport_biodata_path ? request()->userAgent() : null,
            'passport_mrz_image_path' => $this->passport_mrz_crop_path,
            'passport_mrz_raw' => $this->passport_mrz_raw_text,
            'passport_mrz_data' => $this->passport_mrz_result !== [] ? $this->passport_mrz_result : null,
            'passport_mrz_confidence' => $this->passport_mrz_result['confidence'] ?? null,
            'passport_mrz_extracted_at' => $this->passport_mrz_raw_text ? now() : null,
            'passport_mrz_extracted_by' => $this->passport_mrz_raw_text ? auth()->id() : null,
        ];
    }

    protected function standardsReadinessPayload(): array
    {
        return [
            'passport_biodata_available' => $this->passport_biodata_path || $this->passport_biodata_preview_url || $this->passport_biodata_image,
            'passport_mrz_available' => $this->passport_mrz_crop_path || $this->passport_mrz_crop_preview_url || $this->passport_mrz_crop_image,
            'passport_mrz_raw_text' => $this->passport_mrz_raw_text,
            'passport_mrz_result' => $this->passport_mrz_result,
            'nationality_code' => $this->nationality_code,
            'passport_expiry_date' => $this->passportExpiryDate(),
            'date_of_birth' => $this->dateOfBirth(),
            'passport_number' => $this->passport_number,
            'arrival_date' => $this->arrival_date,
            'valid_from' => $this->valid_from,
            'period_of_stay_days' => $this->period_of_stay_days,
            'point_of_entry' => $this->pointOfEntryTableExists()
                ? PointOfEntry::query()->whereKey($this->point_of_entry_id)->value('name')
                : null,
            'flight_carrier' => $this->flight_carrier,
            'flight_number' => $this->flight_number,
            'country_of_birth' => $this->country_of_birth,
            'country_of_residence' => $this->country_of_residence,
            'email' => $this->email,
            'phone' => $this->phone,
            'host_name' => $this->host_name,
            'host_address' => $this->host_address,
            'destination_address' => $this->destination_address,
            'remarks' => $this->remarks,
        ];
    }

    protected function filterExistingColumns(string $table, array $data): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $columns = Schema::getColumnListing($table);

        return array_filter(
            Arr::only($data, $columns),
            static fn ($value) => $value !== ''
        );
    }

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [],
            2 => Arr::only($this->rules(), [
                'airport_id',
                'desk_id',
                'surname',
                'given_names',
                'nationality',
                'nationality_code',
                'passport_number',
                'passport_expiry_year',
                'passport_expiry_month',
                'passport_expiry_day',
                'sex',
                'date_of_birth_year',
                'date_of_birth_month',
                'date_of_birth_day',
                'country_of_birth',
                'country_of_residence',
                'occupation',
                'email',
                'phone',
            ]),
            3 => Arr::only($this->rules(), [
                'purpose_of_visit',
                'point_of_entry_id',
                'period_of_stay_days',
                'arrival_date',
                'valid_from',
                'valid_until',
                'flight_carrier',
                'flight_number',
                'flight_details',
            ]),
            4 => Arr::only($this->rules(), [
                'host_name',
                'host_address',
                'destination_address',
                'destination_phone',
                'remarks',
            ]),
            5 => [],
            default => [],
        };
    }

    protected function rules(): array
    {
        return [
            'airport_id' => ['required', 'integer', Rule::exists('airports', 'id')],
            'desk_id' => ['nullable', 'integer', Rule::exists('desks', 'id')],

            'surname' => ['required', 'string', 'max:255'],
            'given_names' => ['required', 'string', 'max:255'],
            'nationality' => ['required', 'string', 'max:255'],
            'nationality_code' => ['required', 'string', 'size:3'],
            'passport_number' => ['required', 'string', 'max:100'],

            'passport_biodata_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'passport_mrz_crop_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],

            'passport_expiry_year' => ['required', 'digits:4'],
            'passport_expiry_month' => ['required', 'numeric', 'between:1,12'],
            'passport_expiry_day' => ['required', 'numeric', 'between:1,31'],

            'sex' => ['nullable', Rule::in(['M', 'F', 'X', 'Male', 'Female'])],

            'date_of_birth_year' => ['nullable', 'digits:4'],
            'date_of_birth_month' => ['nullable', 'numeric', 'between:1,12'],
            'date_of_birth_day' => ['nullable', 'numeric', 'between:1,31'],

            'country_of_birth' => ['nullable', 'string', 'max:255'],
            'country_of_residence' => ['nullable', 'string', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],

            'purpose_of_visit' => ['required', 'string', 'max:255'],
            'point_of_entry_id' => $this->pointOfEntryTableExists()
                ? ['nullable', 'integer', Rule::exists('points_of_entry', 'id')]
                : ['nullable'],
            'period_of_stay_days' => ['required', 'integer', 'min:1', 'max:365'],
            'period_of_stay_text' => ['nullable', 'string', 'max:255'],
            'arrival_date' => ['required', 'date'],
            'valid_from' => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after_or_equal:valid_from'],

            'flight_carrier' => ['nullable', 'string', 'max:255'],
            'flight_number' => ['nullable', 'string', 'max:100'],
            'flight_details' => ['nullable', 'string', 'max:2000'],

            'host_name' => ['nullable', 'string', 'max:255'],
            'host_address' => ['nullable', 'string', 'max:500'],
            'destination_address' => ['nullable', 'string', 'max:500'],
            'destination_phone' => ['nullable', 'string', 'max:50'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
