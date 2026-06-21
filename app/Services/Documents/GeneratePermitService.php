<?php

namespace App\Services\Documents;

use App\Enums\PermitStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Permit;
use App\Models\User;
use App\Models\VisaApplication;
use App\Services\Workflow\WorkflowService;
use App\Support\Audit;
use App\Support\DocumentHashService;
use App\Support\MrzGenerator;
use App\Support\PermitNumberGenerator;
use App\Support\SecuritySealGenerator;
use App\Support\VerificationCodeGenerator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GeneratePermitService
{
    public function __construct(
        protected WorkflowService $workflowService,
        protected PermitNumberGenerator $permitNumberGenerator,
        protected VerificationCodeGenerator $verificationCodeGenerator,
        protected SecuritySealGenerator $securitySealGenerator,
        protected DocumentHashService $documentHashService,
        protected MrzGenerator $mrzGenerator
    ) {
    }

    public function handle(VisaApplication $application, User $issuer, ?User $checker = null): Permit
    {
        if (! $this->workflowService->permitCanBeIssued($application)) {
            throw new RuntimeException('Permit cannot be issued yet.');
        }

        if ($this->workflowService->requiresChecker($application) && ! $checker) {
            throw new RuntimeException('Checker approval is required.');
        }

        if ($application->permit) {
            return $application->permit;
        }

        return DB::transaction(function () use ($application, $issuer, $checker) {
            $payment = $application->latestInvoice?->payments()->where('status', 'successful')->latest()->first();
            $receipt = $payment?->receipt;
            $waiver = $application->latestWaiverApproval;

            $permit = Permit::query()->create([
                'permit_no' => $this->permitNumberGenerator->generate($application->airport),
                'visa_application_id' => $application->id,
                'payment_id' => $payment?->id,
                'receipt_id' => $receipt?->id,
                'waiver_approval_id' => $waiver?->id,
                'issued_by' => $issuer->id,
                'checker_user_id' => $checker?->id,
                'permit_type' => 'visa_on_arrival',
                'status' => PermitStatus::Issued,
                'issued_at' => now(),
                'valid_from' => $application->valid_from ?: now()->toDateString(),
                'valid_until' => $application->valid_until,
                'verification_code' => $this->verificationCodeGenerator->generate(),
                'seal_algorithm' => 'hmac-sha256',
                'seal_version' => 'v1',
                'is_virtual_available' => true,
            ]);

            $mrz = $this->mrzGenerator->generate($permit->fresh(['visaApplication.passenger']));

            $seal = $this->securitySealGenerator->generate([
                'permit_no' => $permit->permit_no,
                'passport_number' => $application->passenger->passport_number,
                'valid_until' => optional($permit->valid_until)->format('Y-m-d'),
                'verification_code' => $permit->verification_code,
            ]);

            $documentHash = $this->documentHashService->generate(
                json_encode([
                    'permit_no' => $permit->permit_no,
                    'mrz_line_1' => $mrz['line_1'],
                    'mrz_line_2' => $mrz['line_2'],
                    'seal' => $seal,
                ], JSON_UNESCAPED_SLASHES)
            );

            $permit->update([
                'mrz_type' => $mrz['type'],
                'mrz_line_1' => $mrz['line_1'],
                'mrz_line_2' => $mrz['line_2'],
                'security_seal' => $seal,
                'document_hash' => $documentHash,
                'virtual_payload_hash' => $documentHash,
            ]);

            $application->update([
                'status' => VisaApplicationStatus::PermitIssued,
                'approved_by' => $checker?->id ?? $issuer->id,
                'approved_at' => now(),
                'last_status_changed_at' => now(),
            ]);

            Audit::log(
                action: 'permit.generated',
                description: 'Permit generated.',
                auditable: $permit,
                metadata: [
                    'permit_no' => $permit->permit_no,
                    'checker_user_id' => $checker?->id,
                ]
            );

            return $permit->fresh([
                'visaApplication.passenger',
                'payment',
                'receipt',
                'waiverApproval',
            ]);
        });
    }
}
