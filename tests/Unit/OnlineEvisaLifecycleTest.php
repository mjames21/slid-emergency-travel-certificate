<?php

namespace Tests\Unit;

use App\Mail\PermitIssuedMail;
use App\Models\Airport;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use App\Models\Permit;
use App\Models\StaffTitle;
use App\Models\User;
use App\Models\VisaApplication;
use App\Services\Billing\GenerateInvoiceService;
use App\Services\Evisa\ApproveOnlineEvisaApplicationService;
use App\Services\Evisa\CreateOnlineEvisaApplicationService;
use App\Services\Evisa\InitiateOnlineEvisaPaymentService;
use App\Services\Evisa\ProcessWangovPaymentWebhookService;
use App\Services\Evisa\RecordOnlineEvisaPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class OnlineEvisaLifecycleTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function applicant_applies_pays_then_hq_approves_and_emails_permit(): void
    {
        Mail::fake();

        User::factory()->create();
        $hqOfficer = $this->staffUserWithTitle('etc_issuer', 'ETC Issuer');
        $airport = Airport::factory()->create([
            'name' => 'Freetown International Airport',
            'code' => 'FNA',
        ]);

        $application = app(CreateOnlineEvisaApplicationService::class)->handle([
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
            'airport_id' => $airport->id,
            'point_of_entry' => 'Freetown International Airport',
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

        $this->assertSame(VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE, $application->application_channel);
        $this->assertSame('awaiting_payment', $application->status->value);
        $this->assertNotNull($application->public_access_token);

        app(RecordOnlineEvisaPaymentService::class)->handle($application->fresh(['latestInvoice']));

        $this->assertSame('paid', $application->fresh()->status->value);

        $permit = app(ApproveOnlineEvisaApplicationService::class)
            ->handle($application->fresh(['latestInvoice.payments', 'passenger']), $hqOfficer);

        $this->assertSame('permit_issued', $application->fresh()->status->value);
        $this->assertSame($application->id, $permit->visa_application_id);
        $this->assertTrue(Gate::forUser($hqOfficer)->allows('print', $permit));

        Mail::assertSent(PermitIssuedMail::class, function (PermitIssuedMail $mail) {
            return $mail->hasTo('traveler@example.test')
                && $mail->permit->permit_no !== null;
        });
    }

    #[Test]
    public function paid_etc_application_cannot_be_issued_by_unauthorized_user(): void
    {
        User::factory()->create();
        $airport = Airport::factory()->create([
            'name' => 'Freetown International Airport',
            'code' => 'FNA',
        ]);
        $application = $this->onlineApplication($airport->id);

        app(RecordOnlineEvisaPaymentService::class)->handle($application->fresh(['latestInvoice']));

        try {
            app(ApproveOnlineEvisaApplicationService::class)
                ->handle($application->fresh(['latestInvoice.payments', 'passenger']), User::factory()->create());

            $this->fail('Unauthorized user issued an Emergency Travel Certificate.');
        } catch (RuntimeException $exception) {
            $this->assertSame(
                'Only ETC issuers, executives, and system administrators can issue Emergency Travel Certificates.',
                $exception->getMessage()
            );
        }

        $application = $application->fresh(['permit']);

        $this->assertSame('paid', $application->status->value);
        $this->assertNull($application->permit);
    }

    #[Test]
    public function unpaid_etc_certificate_cannot_be_printed_even_by_issuer(): void
    {
        User::factory()->create();
        $issuer = $this->staffUserWithTitle('etc_issuer', 'ETC Issuer');
        $airport = Airport::factory()->create([
            'name' => 'Freetown International Airport',
            'code' => 'FNA',
        ]);
        $application = $this->onlineApplication($airport->id);

        $permit = Permit::factory()
            ->for($application, 'visaApplication')
            ->create([
                'payment_id' => null,
            ]);

        $this->assertFalse(Gate::forUser($issuer)->allows('print', $permit));
    }

    #[Test]
    public function applicant_checkout_registers_wangov_payment_request_without_marking_paid_until_webhook(): void
    {
        config([
            'services.wangov.enabled' => true,
            'services.wangov.external.base_url' => 'https://wangov.example.test',
            'services.wangov.external.endpoint' => '/external-service',
            'services.wangov.external.service_key' => 'secret',
            'services.wangov.external.service_code' => 'slid003',
            'services.wangov.external.service_display' => 'Sierra Leone Emergency Travel Certificate',
        ]);

        Http::fake([
            'wangov.example.test/*' => Http::response([
                'ok' => true,
                'checkout_url' => 'https://wangov.example.test/checkout/ETC-123',
            ], 200),
        ]);

        User::factory()->create();
        $airport = Airport::factory()->create([
            'name' => 'Freetown International Airport',
            'code' => 'FNA',
        ]);
        $application = $this->onlineApplication($airport->id);

        $result = app(InitiateOnlineEvisaPaymentService::class)->handle($application);

        $this->assertSame('registered', $result['status']);
        $this->assertSame('https://wangov.example.test/checkout/ETC-123', $result['checkout_url']);
        $this->assertSame('payment_pending', $application->fresh()->status->value);
        $this->assertSame('initiated', $application->fresh(['latestInvoice'])->latestInvoice->status->value);

        $payment = Payment::query()->firstOrFail();
        $this->assertSame('pending', $payment->status->value);

        Http::assertSent(fn ($request) => $request['application_number'] === $application->fresh(['latestInvoice'])->latestInvoice->payment_reference
            && $request->hasHeader('X-Service-Code', 'slid003')
            && $request->hasHeader('X-Service-Key', 'secret'));
    }

    #[Test]
    public function wangov_checkout_rejects_untrusted_redirect_hosts(): void
    {
        config([
            'services.wangov.enabled' => true,
            'services.wangov.checkout_allowed_hosts' => ['checkout.govpay.sl'],
            'services.wangov.external.base_url' => 'https://wangov.example.test',
            'services.wangov.external.endpoint' => '/external-service',
            'services.wangov.external.service_key' => 'secret',
            'services.wangov.external.service_code' => 'slid003',
            'services.wangov.external.service_display' => 'Sierra Leone Emergency Travel Certificate',
        ]);

        Http::fake([
            'wangov.example.test/*' => Http::response([
                'ok' => true,
                'checkout_url' => 'https://attacker.example.test/pay',
            ], 200),
        ]);

        User::factory()->create();
        $airport = Airport::factory()->create([
            'name' => 'Freetown International Airport',
            'code' => 'FNA',
        ]);
        $application = $this->onlineApplication($airport->id);

        $result = app(InitiateOnlineEvisaPaymentService::class)->handle($application);

        $this->assertSame('registered', $result['status']);
        $this->assertNull($result['checkout_url']);
    }

    #[Test]
    public function wangov_paid_webhook_confirms_evisa_payment_for_hq_review(): void
    {
        User::factory()->create();
        $airport = Airport::factory()->create([
            'name' => 'Freetown International Airport',
            'code' => 'FNA',
        ]);
        $application = $this->onlineApplication($airport->id);

        app(InitiateOnlineEvisaPaymentService::class)->handle($application);

        $invoice = $application->fresh(['latestInvoice'])->latestInvoice;
        $result = app(ProcessWangovPaymentWebhookService::class)->handle(
            $invoice->payment_reference,
            'successful',
            [
                'application_number' => $invoice->payment_reference,
                'status' => 'successful',
                'transaction_id' => 'WAN-123',
                'amount' => 80,
                'currency' => 'USD',
            ],
            'WAN-123',
            80,
            'USD',
            now()
        );

        $this->assertSame('marked_paid', $result['action']);
        $this->assertSame('paid', $application->fresh()->status->value);
        $this->assertSame('paid', $invoice->fresh()->status->value);
        $this->assertSame('successful', $invoice->payments()->latest()->first()->status->value);
    }

    #[Test]
    public function wangov_paid_webhook_rejects_amount_mismatch_for_manual_review(): void
    {
        User::factory()->create();
        $airport = Airport::factory()->create([
            'name' => 'Freetown International Airport',
            'code' => 'FNA',
        ]);
        $application = $this->onlineApplication($airport->id);

        app(InitiateOnlineEvisaPaymentService::class)->handle($application);

        $invoice = $application->fresh(['latestInvoice'])->latestInvoice;
        $result = app(ProcessWangovPaymentWebhookService::class)->handle(
            $invoice->payment_reference,
            'paid',
            [
                'application_number' => $invoice->payment_reference,
                'status' => 'paid',
                'transaction_id' => 'WAN-MISMATCH-123',
                'amount' => 25,
                'currency' => 'USD',
            ],
            'WAN-MISMATCH-123',
            25,
            'USD',
            now()
        );

        $this->assertSame('payment_mismatch_requires_review', $result['action']);
        $this->assertSame('payment_pending', $application->fresh()->status->value);
        $this->assertSame('initiated', $invoice->fresh()->status->value);
        $this->assertSame('pending', $invoice->payments()->latest()->first()->status->value);
    }

    #[Test]
    public function wangov_webhook_event_id_is_idempotent(): void
    {
        config(['services.wangov.webhook.vendor_secret' => 'test-secret']);

        User::factory()->create();
        $airport = Airport::factory()->create([
            'name' => 'Freetown International Airport',
            'code' => 'FNA',
        ]);
        $application = $this->onlineApplication($airport->id);

        app(InitiateOnlineEvisaPaymentService::class)->handle($application);

        $invoice = $application->fresh(['latestInvoice'])->latestInvoice;
        $payload = [
            'event_id' => 'WANGOV-EVENT-123',
            'application_number' => $invoice->payment_reference,
            'status' => 'paid',
            'transaction_id' => 'WAN-IDEMPOTENT-123',
            'amount' => 80,
            'currency' => 'USD',
        ];

        $first = $this->postJson('/webhooks/wangov', $payload, [
            'X-Webhook-Secret' => 'test-secret',
        ]);
        $second = $this->postJson('/webhooks/wangov', $payload, [
            'X-Webhook-Secret' => 'test-secret',
        ]);

        $first->assertOk()->assertJsonPath('action', 'marked_paid');
        $second->assertOk()->assertJsonPath('duplicate', true);
        $this->assertSame(1, PaymentWebhookEvent::query()->where('event_id', 'WANGOV-EVENT-123')->count());
        $event = PaymentWebhookEvent::query()->where('event_id', 'WANGOV-EVENT-123')->firstOrFail();
        $this->assertNotNull($event->request_id);
        $this->assertNotNull($event->source_ip);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', (string) $event->payload_sha256);
        $this->assertSame(1, $invoice->payments()->where('status', 'successful')->count());
    }

    #[Test]
    public function airport_created_visa_on_arrival_can_launch_wangov_checkout_and_be_confirmed(): void
    {
        config([
            'services.wangov.enabled' => true,
            'services.wangov.external.base_url' => 'https://wangov.example.test',
            'services.wangov.external.endpoint' => '/external-service',
            'services.wangov.external.service_key' => 'secret',
            'services.wangov.external.service_code' => 'slid003',
            'services.wangov.external.service_display' => 'Sierra Leone Visa Permit',
        ]);

        Http::fake([
            'wangov.example.test/*' => Http::response(['ok' => true], 200),
        ]);

        $officer = User::factory()->create();
        $airport = Airport::factory()->create(['code' => 'FNA']);
        $application = VisaApplication::factory()->create([
            'airport_id' => $airport->id,
            'created_by' => $officer->id,
            'status' => 'awaiting_payment',
            'application_channel' => 'staff_visa_on_arrival',
        ]);

        $invoice = app(GenerateInvoiceService::class)->handle($officer, $application->fresh(['airport']), 100, 'USD');
        $result = app(InitiateOnlineEvisaPaymentService::class)->handle($application->fresh(['latestInvoice', 'passenger']), '+23276111111');

        $this->assertSame('registered', $result['status']);
        $this->assertSame('initiated', $invoice->fresh()->status->value);
        $this->assertSame('payment_pending', $application->fresh()->status->value);

        Http::assertSent(fn ($request) => $request['phone_number'] === '+23276111111'
            && $request['application_number'] === $invoice->payment_reference);

        app(ProcessWangovPaymentWebhookService::class)->handle(
            $invoice->payment_reference,
            'paid',
            [
                'application_number' => $invoice->payment_reference,
                'status' => 'paid',
                'transaction_id' => 'WAN-VOA-123',
                'amount' => 100,
                'currency' => 'USD',
            ],
            'WAN-VOA-123',
            100,
            'USD',
            now()
        );

        $this->assertSame('paid', $application->fresh()->status->value);
        $this->assertSame('paid', $invoice->fresh()->status->value);
        $this->assertSame('successful', $invoice->payments()->latest()->first()->status->value);
    }

    private function onlineApplication(int $airportId)
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
            'airport_id' => $airportId,
            'point_of_entry' => 'Freetown International Airport',
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
        $title = StaffTitle::query()->create([
            'name' => $name,
            'code' => $code,
            'description' => "{$name} test role",
            'active' => true,
        ]);

        $user = User::factory()->create(['active' => true]);

        $user->staffTitles()->attach($title->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        return $user->fresh(['staffTitles']);
    }
}
