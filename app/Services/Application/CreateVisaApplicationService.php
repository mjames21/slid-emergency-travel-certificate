<?php

namespace App\Services\Application;

use App\Enums\VisaApplicationStatus;
use App\Models\Passenger;
use App\Models\User;
use App\Models\VisaApplication;
use App\Support\ApplicationNumberGenerator;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;

class CreateVisaApplicationService
{
    public function __construct(
        protected ApplicationNumberGenerator $applicationNumberGenerator
    ) {
    }

    public function handle(User $user, array $data): VisaApplication
    {
        return DB::transaction(function () use ($user, $data) {
            $fullName = trim($data['surname'] . ' ' . $data['given_names']);

            $passenger = Passenger::query()->updateOrCreate(
                [
                    'passport_number' => $data['passport_number'],
                    'nationality' => $data['nationality'],
                ],
                [
                    'surname' => $data['surname'],
                    'given_names' => $data['given_names'],
                    'full_name' => $fullName,
                    'nationality_code' => $data['nationality_code'] ?: null,
                    'passport_expiry_date' => $data['passport_expiry_date'],
                    'sex' => $data['sex'] ?: null,
                    'date_of_birth' => $data['date_of_birth'] ?: null,
                    'country_of_birth' => $data['country_of_birth'] ?: null,
                    'country_of_residence' => $data['country_of_residence'] ?: null,
                    'occupation' => $data['occupation'] ?: null,
                    'email' => $data['email'] ?: null,
                    'phone' => $data['phone'] ?: null,
                    'traveler_photo_path' => $data['traveler_photo_path'] ?? null,
                    'traveler_photo_captured_at' => isset($data['traveler_photo_path']) ? now() : null,
                    'traveler_photo_captured_by' => isset($data['traveler_photo_path']) ? $user->id : null,
                    'photo_capture_device' => $data['photo_capture_device'] ?? null,
                ]
            );

            $application = VisaApplication::query()->create([
                'application_no' => $this->applicationNumberGenerator->generate($user->primaryAirport),
                'passenger_id' => $passenger->id,
                'airport_id' => $user->primary_airport_id,
                'desk_id' => $user->primary_desk_id,
                'created_by' => $user->id,
                'submitted_by' => $user->id,
                'visa_type' => 'visa_on_arrival',
                'status' => VisaApplicationStatus::Submitted,
                'purpose_of_visit' => $data['purpose_of_visit'],
                'point_of_entry' => $data['point_of_entry'],
                'period_of_stay_days' => (int) $data['period_of_stay_days'],
                'period_of_stay_text' => $data['period_of_stay_text'] ?: null,
                'arrival_date' => $data['arrival_date'],
                'valid_from' => $data['valid_from'] ?: null,
                'valid_until' => $data['valid_until'] ?: null,
                'flight_carrier' => $data['flight_carrier'] ?: null,
                'flight_number' => $data['flight_number'] ?: null,
                'flight_details' => $data['flight_details'] ?: null,
                'host_name' => $data['host_name'] ?: null,
                'host_address' => $data['host_address'] ?: null,
                'host_phone' => $data['host_phone'] ?: null,
                'destination_address' => $data['destination_address'] ?: null,
                'is_fee_waived' => (bool) $data['is_fee_waived'],
                'requires_checker_approval' => (bool) $data['is_fee_waived'],
                'remarks' => $data['remarks'] ?: null,
                'submitted_at' => now(),
                'last_status_changed_at' => now(),
            ]);

            Audit::log(
                action: 'application.created',
                description: 'Visa application created.',
                auditable: $application,
                metadata: [
                    'application_no' => $application->application_no,
                    'passenger_id' => $passenger->id,
                ]
            );

            return $application;
        });
    }
}
