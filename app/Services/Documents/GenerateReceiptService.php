<?php

namespace App\Services\Documents;

use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Support\Audit;
use App\Support\DocumentHashService;
use App\Support\ReceiptNumberGenerator;
use Illuminate\Support\Facades\DB;

class GenerateReceiptService
{
    public function __construct(
        protected ReceiptNumberGenerator $receiptNumberGenerator,
        protected DocumentHashService $documentHashService
    ) {
    }

    public function handle(Payment $payment, User $issuer): Receipt
    {
        $existing = $payment->receipt;

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($payment, $issuer) {
            $content = sprintf(
                'receipt:%s:%s:%s',
                $payment->id,
                $payment->invoice->invoice_no,
                now()->toIso8601String()
            );

            $receipt = Receipt::query()->create([
                'receipt_no' => $this->receiptNumberGenerator->generate($payment->invoice->visaApplication->airport),
                'payment_id' => $payment->id,
                'issued_by' => $issuer->id,
                'document_hash' => $this->documentHashService->generate($content),
                'issued_at' => now(),
            ]);

            Audit::log(
                action: 'receipt.generated',
                description: 'Receipt generated.',
                auditable: $receipt,
                metadata: ['receipt_no' => $receipt->receipt_no]
            );

            return $receipt;
        });
    }
}
