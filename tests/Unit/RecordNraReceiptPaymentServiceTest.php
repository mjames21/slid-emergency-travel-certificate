<?php

namespace Tests\Unit;

use App\Enums\InvoiceStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Models\VisaApplication;
use App\Services\Billing\RecordNraReceiptPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RecordNraReceiptPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_records_nra_receipt_payment_and_marks_invoice_paid(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $application = VisaApplication::factory()->create([
            'status' => VisaApplicationStatus::AwaitingPayment,
        ]);
        $invoice = Invoice::factory()
            ->for($application, 'visaApplication')
            ->create([
                'amount' => 80,
                'currency' => 'USD',
                'status' => InvoiceStatus::Pending,
                'paid_at' => null,
            ]);

        $receipt = app(RecordNraReceiptPaymentService::class)->handle($user, $invoice, [
            'receipt_no' => 'nra-2026-0001',
            'evidence_path' => 'receipts/nra/test-receipt.jpg',
            'evidence_original_name' => 'test-receipt.jpg',
            'evidence_mime_type' => 'image/jpeg',
            'evidence_size' => 1024,
            'evidence_hash' => hash('sha256', 'receipt-image'),
            'note' => 'UAT receipt upload',
        ]);

        $this->assertSame('NRA-2026-0001', $receipt->receipt_no);
        $this->assertSame('nra_manual', $receipt->receipt_source);
        $this->assertSame('paid', $invoice->fresh()->status->value);
        $this->assertSame('paid', $application->fresh()->status->value);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'confirmed_by' => $user->id,
            'gateway' => 'nra_manual',
            'payment_channel' => 'nra_receipt',
            'status' => 'successful',
        ]);
    }

    #[Test]
    public function it_rejects_recycled_nra_receipt_numbers(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $firstInvoice = Invoice::factory()
            ->for(VisaApplication::factory()->state(['status' => VisaApplicationStatus::AwaitingPayment]), 'visaApplication')
            ->create([
                'status' => InvoiceStatus::Pending,
                'paid_at' => null,
            ]);

        $secondInvoice = Invoice::factory()
            ->for(VisaApplication::factory()->state(['status' => VisaApplicationStatus::AwaitingPayment]), 'visaApplication')
            ->create([
                'status' => InvoiceStatus::Pending,
                'paid_at' => null,
            ]);

        $service = app(RecordNraReceiptPaymentService::class);
        $service->handle($user, $firstInvoice, [
            'receipt_no' => 'NRA-2026-0002',
            'evidence_path' => 'receipts/nra/first.jpg',
            'evidence_hash' => hash('sha256', 'first'),
        ]);

        $this->expectException(ValidationException::class);

        $service->handle($user, $secondInvoice, [
            'receipt_no' => 'NRA-2026-0002',
            'evidence_path' => 'receipts/nra/second.jpg',
            'evidence_hash' => hash('sha256', 'second'),
        ]);
    }
}
