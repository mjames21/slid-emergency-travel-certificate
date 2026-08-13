<?php

namespace Tests\Feature;

use App\Livewire\Hq\EvisaApplications\Index as EtcApplicationsIndex;
use App\Models\Payment;
use App\Models\StaffTitle;
use App\Models\User;
use App\Models\VisaApplication;
use App\Services\Evisa\CreateOnlineEvisaApplicationService;
use App\Services\Evisa\RecordOnlineEvisaPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReceiptCertificateGenerationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function etc_issuer_can_record_payment_by_receipt_number(): void
    {
        User::factory()->create();
        $issuer = $this->staffUserWithTitle('etc_issuer', 'ETC Issuer');
        $application = $this->onlineApplication()->fresh(['latestInvoice']);

        $this->actingAs($issuer);

        Livewire::test(EtcApplicationsIndex::class)
            ->set('paymentLookup', $application->latestInvoice->payment_reference)
            ->set('paymentReceiptNumber', 'WAN-PAID-RECEIPT-9001')
            ->call('recordPaymentByReceipt')
            ->assertHasNoErrors()
            ->assertSet('error', null)
            ->assertSet('paymentLookup', '')
            ->assertSet('paymentReceiptNumber', '')
            ->assertSet('status', 'paid');

        $payment = Payment::query()->where('gateway_transaction_id', 'WAN-PAID-RECEIPT-9001')->firstOrFail();

        $this->assertSame($application->latestInvoice->id, $payment->invoice_id);
        $this->assertSame($issuer->id, $payment->confirmed_by);
        $this->assertSame('office_receipt', $payment->payment_channel);
        $this->assertSame('successful', $payment->status->value);
        $this->assertSame('paid', $application->fresh()->status->value);
        $this->assertSame('paid', $application->latestInvoice->fresh()->status->value);
    }

    #[Test]
    public function etc_issuer_can_generate_certificate_from_paid_receipt_number(): void
    {
        Mail::fake();

        User::factory()->create();
        $issuer = $this->staffUserWithTitle('etc_issuer', 'ETC Issuer');
        $application = $this->onlineApplication();

        $payment = app(RecordOnlineEvisaPaymentService::class)->handle(
            $application->fresh(['latestInvoice']),
            ['gateway_transaction_id' => 'WAN-ETC-RECEIPT-001']
        );

        $this->actingAs($issuer);

        Livewire::test(EtcApplicationsIndex::class)
            ->set('receiptNumber', ' WAN-ETC-RECEIPT-001 ')
            ->call('issueByReceipt')
            ->assertHasNoErrors()
            ->assertSet('error', null)
            ->assertSet('receiptNumber', '');

        $issuedApplication = $application->fresh(['permit']);
        $permit = $issuedApplication->permit;

        $this->assertNotNull($permit);
        $this->assertSame('permit_issued', $issuedApplication->status->value);
        $this->assertSame($payment->id, $permit->payment_id);
        $this->assertSame($issuer->id, $permit->issued_by);
        $this->assertNotEmpty($permit->mrz_line_1);
        $this->assertNotEmpty($permit->mrz_line_2);
    }

    #[Test]
    public function receipt_number_generation_still_requires_successful_payment(): void
    {
        Mail::fake();

        User::factory()->create();
        $issuer = $this->staffUserWithTitle('etc_issuer', 'ETC Issuer');
        $application = $this->onlineApplication();
        $receipt = $application->fresh(['latestInvoice'])->latestInvoice->payment_reference;

        $this->actingAs($issuer);

        Livewire::test(EtcApplicationsIndex::class)
            ->set('receiptNumber', $receipt)
            ->call('issueByReceipt')
            ->assertHasNoErrors(['receiptNumber'])
            ->assertSet('error', 'Emergency Travel Certificate application must be paid before approval and issue.');

        $this->assertNull($application->fresh(['permit'])->permit);
    }

    private function onlineApplication(): VisaApplication
    {
        return app(CreateOnlineEvisaApplicationService::class)->handle([
            'surname' => 'JAMES',
            'given_names' => 'MOHAMED',
            'nationality' => 'Sierra Leone',
            'nationality_code' => 'SLE',
            'passport_number' => 'SLR092377',
            'passport_expiry_date' => now()->addYears(3)->toDateString(),
            'sex' => 'M',
            'date_of_birth' => '1986-04-21',
            'country_of_birth' => 'Sierra Leone',
            'country_of_residence' => 'Sierra Leone',
            'occupation' => 'Consultant',
            'email' => 'traveler@example.test',
            'phone' => '+232700000000',
            'point_of_entry' => 'Emergency Travel Certificate Desk',
            'purpose_of_visit' => 'Business',
            'period_of_stay_days' => 30,
            'arrival_date' => now()->addWeek()->toDateString(),
            'flight_carrier' => 'Kenya Airways',
            'flight_number' => 'KQ510',
            'flight_details' => null,
            'host_name' => 'Host',
            'host_address' => 'Freetown',
            'host_phone' => '+232700000001',
            'destination_address' => 'Freetown',
            'remarks' => null,
        ]);
    }

    private function staffUserWithTitle(string $code, string $name): User
    {
        $title = StaffTitle::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => "{$name} test role",
                'active' => true,
            ]
        );

        $user = User::factory()->create(['active' => true]);

        $user->staffTitles()->attach($title->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        return $user->fresh(['staffTitles']);
    }
}
