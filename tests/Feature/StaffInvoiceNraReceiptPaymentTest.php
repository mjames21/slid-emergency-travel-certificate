<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\VisaApplicationStatus;
use App\Livewire\Staff\Invoices\Show;
use App\Models\Airport;
use App\Models\Invoice;
use App\Models\StaffTitle;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffInvoiceNraReceiptPaymentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function payment_officer_only_sees_payment_routes_not_application_workflow(): void
    {
        [$user, $invoice] = $this->userAndInvoice('payment_officer', 'Payment Officer');

        $this->actingAs($user->fresh(['staffTitles']));

        $this->get(route('staff.payments.index'))->assertOk();
        $this->get(route('staff.invoices.show', $invoice))->assertOk();
        $this->get(route('staff.applications.index'))->assertForbidden();
        $this->get(route('staff.applications.show', $invoice->visaApplication))->assertForbidden();
        $this->get(route('staff.reports.permit-expiry'))->assertForbidden();
        $this->get(route('dashboard'))->assertRedirect(route('staff.payments.index'));
    }

    #[Test]
    public function payment_officer_can_upload_nra_receipt_and_mark_invoice_paid(): void
    {
        Storage::fake('local');

        [$user, $invoice] = $this->userAndInvoice('payment_officer', 'Payment Officer');
        $this->actingAs($user->fresh(['staffTitles']));

        Livewire::test(Show::class, ['invoice' => $invoice])
            ->set('manual_receipt_no', 'NRA-2026-0099')
            ->set('manual_receipt_image', UploadedFile::fake()->image('receipt.jpg'))
            ->call('recordNraReceiptPayment')
            ->assertHasNoErrors();

        $this->assertSame('paid', $invoice->fresh()->status->value);
        $this->assertSame('paid', $invoice->visaApplication->fresh()->status->value);
        $this->assertDatabaseHas('receipts', [
            'receipt_no' => 'NRA-2026-0099',
            'receipt_source' => 'nra_manual',
            'evidence_original_name' => 'receipt.jpg',
        ]);
    }

    #[Test]
    public function visa_processing_officer_can_upload_traveler_nra_receipt_and_mark_invoice_paid(): void
    {
        Storage::fake('local');

        [$user, $invoice] = $this->userAndInvoice('visa_processing_officer', 'Visa Processing Officer');
        $this->actingAs($user->fresh(['staffTitles']));

        Livewire::test(Show::class, ['invoice' => $invoice])
            ->set('manual_receipt_no', 'NRA-2026-TRAVELER-01')
            ->set('manual_receipt_image', UploadedFile::fake()->image('traveler-receipt.jpg'))
            ->call('recordNraReceiptPayment')
            ->assertHasNoErrors();

        $this->assertSame('paid', $invoice->fresh()->status->value);
        $this->assertSame('paid', $invoice->visaApplication->fresh()->status->value);
        $this->assertDatabaseHas('receipts', [
            'receipt_no' => 'NRA-2026-TRAVELER-01',
            'receipt_source' => 'nra_manual',
            'evidence_original_name' => 'traveler-receipt.jpg',
        ]);
    }

    private function userAndInvoice(string $titleCode, string $titleName): array
    {
        $airport = Airport::factory()->create(['code' => 'FNA']);
        $staffTitle = StaffTitle::query()->create([
            'name' => $titleName,
            'code' => $titleCode,
            'description' => "{$titleName} UAT test role",
            'allowed_statuses' => ['awaiting_payment', 'payment_pending', 'paid'],
            'active' => true,
        ]);

        $user = User::factory()->create([
            'primary_airport_id' => $airport->id,
        ]);
        $user->staffTitles()->attach($staffTitle->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        $application = VisaApplication::factory()
            ->for($airport)
            ->create([
                'status' => VisaApplicationStatus::AwaitingPayment,
            ]);

        $invoice = Invoice::factory()
            ->for($application, 'visaApplication')
            ->create([
                'status' => InvoiceStatus::Pending,
                'paid_at' => null,
            ]);

        return [$user, $invoice];
    }
}
