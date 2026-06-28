<?php

namespace App\Services\Evisa;

use App\Enums\InvoiceStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Invoice;
use App\Models\Passenger;
use App\Models\User;
use App\Models\VisaApplication;
use App\Support\ApplicationNumberGenerator;
use App\Support\InvoiceNumberGenerator;
use App\Support\PaymentReferenceGenerator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOnlineEvisaApplicationService
{
    public function __construct(
        protected ApplicationNumberGenerator $applicationNumberGenerator,
        protected InvoiceNumberGenerator $invoiceNumberGenerator,
        protected PaymentReferenceGenerator $paymentReferenceGenerator
    ) {}

    public function handle(array $data): VisaApplication
    {
        return DB::transaction(function () use ($data) {
            $fullName = trim($data['surname'].' '.$data['given_names']);

            $passenger = Passenger::query()->updateOrCreate(
                [
                    'passport_number' => strtoupper($data['passport_number']),
                    'nationality' => $data['nationality'],
                ],
                array_filter([
                    'surname' => strtoupper($data['surname']),
                    'given_names' => strtoupper($data['given_names']),
                    'full_name' => strtoupper($fullName),
                    'nationality_code' => strtoupper($data['nationality_code']),
                    'passport_expiry_date' => $data['passport_expiry_date'],
                    'sex' => $data['sex'] ?: null,
                    'date_of_birth' => $data['date_of_birth'] ?: null,
                    'country_of_birth' => $data['country_of_birth'] ?: null,
                    'country_of_residence' => $data['country_of_residence'] ?: null,
                    'occupation' => $data['occupation'] ?: null,
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?: null,
                    'passport_biodata_image_path' => $data['passport_biodata_image_path'] ?? null,
                    'passport_biodata_captured_at' => ! empty($data['passport_biodata_image_path']) ? now() : null,
                    'passport_biodata_capture_device' => ! empty($data['passport_biodata_image_path'])
                        ? ($data['passport_biodata_capture_device'] ?? 'online-applicant-upload')
                        : null,
                    'passport_mrz_raw' => $data['passport_mrz_raw'] ?? null,
                    'passport_mrz_data' => $data['passport_mrz_data'] ?? null,
                    'passport_mrz_confidence' => $data['passport_mrz_confidence'] ?? null,
                    'passport_mrz_extracted_at' => ! empty($data['passport_mrz_raw']) ? now() : null,
                ], static fn ($value) => $value !== null)
            );

            $application = VisaApplication::query()->create([
                'application_no' => $this->applicationNumberGenerator->generate(),
                'public_tracking_code' => $this->trackingCode(),
                'public_access_token' => Str::random(48),
                'passenger_id' => $passenger->id,
                'created_by' => $data['created_by'] ?? User::query()->oldest('id')->value('id'),
                'submitted_by' => null,
                'visa_type' => VisaApplication::TYPE_EMERGENCY_TRAVEL_CERTIFICATE,
                'application_channel' => VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE,
                'applicant_category' => ($data['applicant_category'] ?? null) ?: null,
                'regional_category' => ($data['regional_category'] ?? null) ?: null,
                'identity_document_type' => ($data['identity_document_type'] ?? null) ?: null,
                'identity_document_number' => ($data['identity_document_number'] ?? null) ?: strtoupper($data['passport_number']),
                'place_of_birth' => ($data['place_of_birth'] ?? null) ?: null,
                'marital_status' => ($data['marital_status'] ?? null) ?: null,
                'applicant_address' => ($data['applicant_address'] ?? null) ?: null,
                'status' => VisaApplicationStatus::AwaitingPayment,
                'purpose_of_visit' => $data['purpose_of_visit'],
                'point_of_entry' => $data['point_of_entry'],
                'period_of_stay_days' => (int) $data['period_of_stay_days'],
                'period_of_stay_text' => $data['period_of_stay_days'].' DAYS',
                'arrival_date' => $data['arrival_date'],
                'valid_from' => $data['arrival_date'],
                'valid_until' => Carbon::parse($data['arrival_date'])->copy()->addDays((int) $data['period_of_stay_days'])->toDateString(),
                'flight_carrier' => ($data['flight_carrier'] ?? null) ?: null,
                'flight_number' => ($data['flight_number'] ?? null) ?: null,
                'flight_details' => ($data['flight_details'] ?? null) ?: null,
                'accommodation_type' => ($data['accommodation_type'] ?? null) ?: null,
                'accommodation_name' => ($data['accommodation_name'] ?? null) ?: null,
                'booking_reference' => ($data['booking_reference'] ?? null) ?: null,
                'booking_confirmation_image_path' => ($data['booking_confirmation_image_path'] ?? null) ?: null,
                'applicant_photo_path' => ($data['applicant_photo_path'] ?? null) ?: null,
                'host_name' => ($data['host_name'] ?? null) ?: null,
                'host_address' => ($data['host_address'] ?? null) ?: null,
                'host_phone' => ($data['host_phone'] ?? null) ?: null,
                'destination_address' => ($data['destination_address'] ?? null) ?: null,
                'destination_country' => ($data['destination_country'] ?? null) ?: null,
                'employment_status' => ($data['employment_status'] ?? null) ?: null,
                'employer_name' => ($data['employer_name'] ?? null) ?: null,
                'employer_address' => ($data['employer_address'] ?? null) ?: null,
                'emergency_contact_name' => ($data['emergency_contact_name'] ?? null) ?: null,
                'emergency_contact_relationship' => ($data['emergency_contact_relationship'] ?? null) ?: null,
                'emergency_contact_phone' => ($data['emergency_contact_phone'] ?? null) ?: null,
                'emergency_contact_email' => ($data['emergency_contact_email'] ?? null) ?: null,
                'guardian_name' => ($data['guardian_name'] ?? null) ?: null,
                'guardian_relationship' => ($data['guardian_relationship'] ?? null) ?: null,
                'guardian_address' => ($data['guardian_address'] ?? null) ?: null,
                'guardian_phone' => ($data['guardian_phone'] ?? null) ?: null,
                'guardian_sex' => ($data['guardian_sex'] ?? null) ?: null,
                'travel_history' => $data['travel_history'] ?? null,
                'immigration_history' => $data['immigration_history'] ?? null,
                'security_declarations' => $data['security_declarations'] ?? null,
                'remarks' => ($data['remarks'] ?? null) ?: null,
                'submitted_at' => now(),
                'applicant_submitted_at' => now(),
                'applicant_certified_at' => now(),
                'applicant_certification_ip' => ($data['applicant_certification_ip'] ?? null) ?: null,
                'last_status_changed_at' => now(),
            ]);

            Invoice::query()->create([
                'invoice_no' => $this->invoiceNumberGenerator->generate(),
                'visa_application_id' => $application->id,
                'created_by' => null,
                'amount' => $data['amount'] ?? 80.00,
                'currency' => $data['currency'] ?? 'USD',
                'payment_reference' => $this->paymentReferenceGenerator->generate(),
                'gateway' => 'wangov',
                'status' => InvoiceStatus::Pending,
                'issued_at' => now(),
                'expires_at' => now()->addDay(),
            ]);

            return $application->fresh(['passenger', 'latestInvoice']);
        });
    }

    protected function trackingCode(): string
    {
        do {
            $code = 'ETC-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (VisaApplication::query()->where('public_tracking_code', $code)->exists());

        return $code;
    }
}
