<?php
// FILE: database/seeders/DemoVisaApplicationSeeder.php

namespace Database\Seeders;

use App\Enums\PermitStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Airport;
use App\Models\AuditLog;
use App\Models\Desk;
use App\Models\DeviceRegistration;
use App\Models\FraudFlag;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Permit;
use App\Models\PermitPrintLog;
use App\Models\Receipt;
use App\Models\User;
use App\Models\VisaApplication;
use App\Models\WaiverApproval;
use App\Support\DocumentHashService;
use App\Support\MrzGenerator;
use App\Support\PrintableSecurityValue;
use App\Support\SecuritySealGenerator;
use App\Support\VerificationCodeGenerator;
use App\Support\VisaIdGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoVisaApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $airport = Airport::query()->where('code', 'FNA')->firstOrFail();
        $desk = Desk::query()->where('airport_id', $airport->id)->where('code', 'VOA-01')->firstOrFail();

        $officer = User::query()->where('email', 'officer1.fna@immigration.gov.sl')->firstOrFail();
        $supervisor = User::query()->where('email', 'supervisor.fna@immigration.gov.sl')->firstOrFail();
        $paymentOfficer = User::query()->where('email', 'payment.fna@immigration.gov.sl')->firstOrFail();

        $device = DeviceRegistration::query()->updateOrCreate(
            ['device_identifier' => 'FNA-VOA-01-TERMINAL-01'],
            [
                'airport_id' => $airport->id,
                'desk_id' => $desk->id,
                'device_name' => 'FNA Terminal 01',
                'hostname' => 'fna-voa-01',
                'printer_name' => 'Desk Printer 01',
                'ip_address' => '10.0.0.21',
                'trusted' => true,
                'active' => true,
                'registered_by' => $supervisor->id,
                'registered_at' => now(),
                'last_seen_at' => now(),
            ]
        );

        $documentHashService = app(DocumentHashService::class);
        $securitySealGenerator = app(SecuritySealGenerator::class);
        $verificationCodeGenerator = app(VerificationCodeGenerator::class);
        $mrzGenerator = app(MrzGenerator::class);
        $visaIdGenerator = app(VisaIdGenerator::class);

        $records = [
            [
                'passenger' => [
                    'surname' => 'WIEGAND',
                    'given_names' => 'WAINO MITCHEL',
                    'full_name' => 'WIEGAND WAINO MITCHEL',
                    'nationality' => 'Kenyan',
                    'nationality_code' => 'KEN',
                    'passport_number' => 'U19940043',
                    'passport_expiry_date' => '2029-06-07',
                    'sex' => 'M',
                    'date_of_birth' => '1990-01-01',
                    'country_of_birth' => 'Kenya',
                    'country_of_residence' => 'Kenya',
                    'occupation' => 'CONSULTANT',
                    'email' => 'wiegand@example.com',
                    'phone' => '+254700000001',
                ],
                'application' => [
                    'purpose_of_visit' => 'TOURISM',
                    'point_of_entry' => 'FREETOWN INTERNATIONAL AIRPORT',
                    'period_of_stay_days' => 30,
                    'period_of_stay_text' => 'ONE (1) MONTH',
                    'arrival_date' => now()->toDateString(),
                    'valid_from' => now()->toDateString(),
                    'valid_until' => now()->addMonth()->toDateString(),
                    'flight_carrier' => 'BRUSSELS AIRLINES',
                    'flight_number' => 'SN241',
                    'flight_details' => 'BRUSSELS AIRLINES / SN241',
                    'host_name' => 'SIERRA VISIT LTD',
                    'host_address' => 'FREETOWN',
                    'host_phone' => '+232700000001',
                    'destination_address' => 'FREETOWN',
                    'remarks' => 'DEMO PAID WITH RECEIPT',
                ],
                'invoice' => ['amount' => 100.00, 'currency' => 'USD', 'status' => 'paid'],
                'payment' => ['status' => 'successful', 'channel' => 'card'],
                'receipt' => true,
                'permit' => false,
                'waiver' => false,
            ],
            [
                'passenger' => [
                    'surname' => 'KUHLMAN',
                    'given_names' => 'LEXI ARMAND',
                    'full_name' => 'KUHLMAN LEXI ARMAND',
                    'nationality' => 'Ghanaian',
                    'nationality_code' => 'GHA',
                    'passport_number' => 'U32993859',
                    'passport_expiry_date' => '2029-06-07',
                    'sex' => 'M',
                    'date_of_birth' => '1990-01-01',
                    'country_of_birth' => 'Ghana',
                    'country_of_residence' => 'Ghana',
                    'occupation' => 'ARTILLERY OFFICER',
                    'email' => 'kuhlman@example.com',
                    'phone' => '+233700000001',
                ],
                'application' => [
                    'purpose_of_visit' => 'VISIT',
                    'point_of_entry' => 'FREETOWN INTERNATIONAL AIRPORT',
                    'period_of_stay_days' => 30,
                    'period_of_stay_text' => 'ONE (1) MONTH',
                    'arrival_date' => now()->toDateString(),
                    'valid_from' => now()->toDateString(),
                    'valid_until' => now()->addMonth()->toDateString(),
                    'flight_carrier' => 'ASKY',
                    'flight_number' => '41KI',
                    'flight_details' => 'LANGWORTH-PARKER',
                    'host_name' => 'DICKINSON, SMITH AND MARKS',
                    'host_address' => '575 JENKINS FORGES EZEKIELFORT, KY 95402-1929',
                    'host_phone' => '+232700000002',
                    'destination_address' => 'FREETOWN',
                    'remarks' => 'DEMO PAID WITH RECEIPT AND PERMIT',
                ],
                'invoice' => ['amount' => 100.00, 'currency' => 'USD', 'status' => 'paid'],
                'payment' => ['status' => 'successful', 'channel' => 'bank'],
                'receipt' => true,
                'permit' => true,
                'waiver' => false,
            ],
            [
                'passenger' => [
                    'surname' => 'SPENCER',
                    'given_names' => 'SUMMER COOPER',
                    'full_name' => 'SPENCER SUMMER COOPER',
                    'nationality' => 'British',
                    'nationality_code' => 'GBR',
                    'passport_number' => 'U22538248',
                    'passport_expiry_date' => '2029-06-07',
                    'sex' => 'F',
                    'date_of_birth' => '1992-08-11',
                    'country_of_birth' => 'United Kingdom',
                    'country_of_residence' => 'United Kingdom',
                    'occupation' => 'DIPLOMAT',
                    'email' => 'spencer@example.com',
                    'phone' => '+447000000001',
                ],
                'application' => [
                    'purpose_of_visit' => 'OFFICIAL',
                    'point_of_entry' => 'FREETOWN INTERNATIONAL AIRPORT',
                    'period_of_stay_days' => 30,
                    'period_of_stay_text' => 'ONE (1) MONTH',
                    'arrival_date' => now()->toDateString(),
                    'valid_from' => now()->toDateString(),
                    'valid_until' => now()->addMonth()->toDateString(),
                    'flight_carrier' => 'TURKISH AIRLINES',
                    'flight_number' => 'TK531',
                    'flight_details' => 'TURKISH AIRLINES / TK531',
                    'host_name' => 'OFFICE OF THE FIRST LADY',
                    'host_address' => 'FREETOWN',
                    'host_phone' => '+232700000003',
                    'destination_address' => 'FREETOWN',
                    'remarks' => 'DEMO WAIVER WITH PERMIT',
                ],
                'invoice' => ['amount' => 0.00, 'currency' => 'USD', 'status' => 'waived'],
                'payment' => null,
                'receipt' => false,
                'permit' => true,
                'waiver' => true,
            ],
        ];

        foreach ($records as $index => $record) {
            $passenger = Passenger::query()->create($record['passenger']);

            $application = VisaApplication::query()->create([
                'application_no' => 'VOA-' . $airport->code . '-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'passenger_id' => $passenger->id,
                'airport_id' => $airport->id,
                'desk_id' => $desk->id,
                'created_by' => $officer->id,
                'submitted_by' => $officer->id,
                'approved_by' => $record['permit'] ? $supervisor->id : null,
                'reviewed_by' => $record['permit'] ? $supervisor->id : null,
                'visa_type' => 'visa_on_arrival',
                'status' => $record['permit'] ? VisaApplicationStatus::PermitIssued : VisaApplicationStatus::Paid,
                'purpose_of_visit' => $record['application']['purpose_of_visit'],
                'point_of_entry' => $record['application']['point_of_entry'],
                'period_of_stay_days' => $record['application']['period_of_stay_days'],
                'period_of_stay_text' => $record['application']['period_of_stay_text'],
                'arrival_date' => $record['application']['arrival_date'],
                'valid_from' => $record['application']['valid_from'],
                'valid_until' => $record['application']['valid_until'],
                'flight_carrier' => $record['application']['flight_carrier'],
                'flight_number' => $record['application']['flight_number'],
                'flight_details' => $record['application']['flight_details'],
                'host_name' => $record['application']['host_name'],
                'host_address' => $record['application']['host_address'],
                'host_phone' => $record['application']['host_phone'],
                'destination_address' => $record['application']['destination_address'],
                'is_fee_waived' => $record['waiver'],
                'requires_checker_approval' => $record['waiver'],
                'remarks' => $record['application']['remarks'],
                'submitted_at' => now(),
                'reviewed_at' => $record['permit'] ? now() : null,
                'approved_at' => $record['permit'] ? now() : null,
                'last_status_changed_at' => now(),
            ]);

            $waiver = null;
            if ($record['waiver']) {
                $waiver = WaiverApproval::query()->create([
                    'visa_application_id' => $application->id,
                    'requested_by' => $officer->id,
                    'approved_by' => $supervisor->id,
                    'reason_category' => 'official_exemption',
                    'reason' => 'Official exemption approved.',
                    'authority_reference' => 'WVR-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'approved' => true,
                    'requested_at' => now(),
                    'approved_at' => now(),
                ]);
            }

            $invoice = Invoice::query()->create([
                'invoice_no' => 'INV-' . $airport->code . '-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 5, '0', STR_PAD_LEFT),
                'visa_application_id' => $application->id,
                'created_by' => $officer->id,
                'amount' => $record['invoice']['amount'],
                'currency' => $record['invoice']['currency'],
                'payment_reference' => 'SVA-' . $airport->code . '-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'gateway' => 'wangov',
                'status' => $record['invoice']['status'],
                'issued_at' => now(),
                'expires_at' => now()->addDay(),
                'paid_at' => in_array($record['invoice']['status'], ['paid', 'waived'], true) ? now() : null,
            ]);

            $payment = null;
            if ($record['payment']) {
                $payment = Payment::query()->create([
                    'invoice_id' => $invoice->id,
                    'confirmed_by' => $paymentOfficer->id,
                    'gateway' => 'wangov',
                    'gateway_transaction_id' => 'TXN-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'gateway_reference' => $invoice->payment_reference,
                    'payment_channel' => $record['payment']['channel'],
                    'amount_due' => $invoice->amount,
                    'amount_paid' => $invoice->amount,
                    'currency' => $invoice->currency,
                    'status' => $record['payment']['status'],
                    'raw_payload' => ['status' => 'success'],
                    'verification_payload' => ['verified' => true],
                    'initiated_at' => now()->subMinutes(10),
                    'paid_at' => now()->subMinutes(5),
                    'verified_at' => now()->subMinutes(4),
                ]);
            }

            $receipt = null;
            if ($record['receipt'] && $payment) {
                $receipt = Receipt::query()->create([
                    'receipt_no' => 'RCP-' . $airport->code . '-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'payment_id' => $payment->id,
                    'issued_by' => $paymentOfficer->id,
                    'document_hash' => $documentHashService->generate('receipt:' . $payment->id . ':' . now()->toIso8601String()),
                    'issued_at' => now()->subMinutes(3),
                    'printed_at' => now()->subMinutes(2),
                ]);
            }

            if ($record['permit']) {
                $permit = Permit::query()->create([
                    'permit_no' => 'SLID-VOA-' . $airport->code . '-' . now()->format('Y') . '-' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'visa_id' => $visaIdGenerator->generate($airport),
                    'visa_application_id' => $application->id,
                    'payment_id' => $payment?->id,
                    'receipt_id' => $receipt?->id,
                    'waiver_approval_id' => $waiver?->id,
                    'issued_by' => $officer->id,
                    'checker_user_id' => $supervisor->id,
                    'permit_type' => 'visa_on_arrival',
                    'status' => PermitStatus::Issued,
                    'issued_at' => now()->subMinute(),
                    'valid_from' => $application->valid_from,
                    'valid_until' => $application->valid_until,
                    'verification_code' => $verificationCodeGenerator->generate(),
                    'seal_algorithm' => 'hmac-sha256',
                    'seal_version' => 'v1',
                    'is_virtual_available' => true,
                    'print_count' => 1,
                    'last_printed_at' => now()->subMinute(),
                ]);

                $mrz = $mrzGenerator->generate($permit->fresh(['visaApplication.passenger']));
                $seal = $securitySealGenerator->generate([
                    'permit_no' => $permit->permit_no,
                    'visa_id' => $permit->visa_id,
                    'passport_number' => $application->passenger->passport_number,
                    'valid_until' => $permit->valid_until,
                    'verification_code' => $permit->verification_code,
                ]);

                $permit->update([
                    'mrz_type' => $mrz['type'],
                    'mrz_line_1' => $mrz['line_1'],
                    'mrz_line_2' => $mrz['line_2'],
                    'security_seal' => $seal,
                    'document_hash' => $documentHashService->generate('permit:' . $permit->permit_no . ':' . $permit->visa_id),
                    'virtual_payload_hash' => $documentHashService->generate('virtual:' . $permit->permit_no . ':' . $permit->verification_code),
                ]);

                PermitPrintLog::query()->create([
                    'permit_id' => $permit->id,
                    'printed_by' => $officer->id,
                    'airport_id' => $airport->id,
                    'desk_id' => $desk->id,
                    'device_registration_id' => $device->id,
                    'terminal_name' => 'FNA Terminal 01',
                    'printer_name' => 'Desk Printer 01',
                    'is_reprint' => false,
                    'printed_at' => now()->subMinute(),
                ]);

                NotificationLog::query()->create([
                    'permit_id' => $permit->id,
                    'visa_application_id' => $application->id,
                    'channel' => 'email',
                    'recipient' => $passenger->email,
                    'subject' => 'Sierra Leone Immigration Department Visa on Arrival Permit',
                    'status' => 'sent',
                    'provider_message_id' => 'MSG-' . now()->format('Ymd') . '-' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'payload' => [
                        'permit_no' => $permit->permit_no,
                        'visa_id' => $permit->visa_id,
                        'verification_code' => $permit->verification_code,
                    ],
                    'sent_at' => now(),
                ]);

                if ($index === 1) {
                    FraudFlag::query()->create([
                        'visa_application_id' => $application->id,
                        'permit_id' => $permit->id,
                        'payment_id' => $payment?->id,
                        'flagged_by' => $supervisor->id,
                        'flag_type' => 'reprint_review',
                        'severity' => 'medium',
                        'description' => 'Review sample fraud flag for HQ dashboard.',
                        'resolved' => false,
                        'flagged_at' => now(),
                    ]);
                }
            }

            AuditLog::query()->create([
                'user_id' => $officer->id,
                'airport_id' => $airport->id,
                'desk_id' => $desk->id,
                'action' => 'application.seeded',
                'description' => 'Demo visa application seeded.',
                'auditable_type' => VisaApplication::class,
                'auditable_id' => $application->id,
                'metadata' => [
                    'application_no' => $application->application_no,
                    'invoice_no' => $invoice->invoice_no,
                    'receipt_no' => $receipt?->receipt_no,
                ],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Seeder',
            ]);
        }
    }
}
