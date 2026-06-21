<?php

namespace App\Services\Evisa;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Payment;
use App\Models\VisaApplication;
use App\Services\Billing\WangovExternalClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InitiateOnlineEvisaPaymentService
{
    public function __construct(protected WangovExternalClient $client) {}

    public function handle(VisaApplication $application, ?string $payerPhone = null): array
    {
        return DB::transaction(function () use ($application, $payerPhone) {
            $application->loadMissing(['passenger', 'latestInvoice']);
            $invoice = $application->latestInvoice;

            if (! $invoice) {
                throw new \RuntimeException('No invoice is available for this Emergency Travel Certificate application.');
            }

            if ($invoice->status === InvoiceStatus::Paid) {
                return ['status' => 'already_paid'];
            }

            $pendingPayment = $invoice->payments()
                ->where('status', PaymentStatus::Pending)
                ->latest()
                ->first();

            if (! $pendingPayment) {
                $pendingPayment = Payment::query()->create([
                    'invoice_id' => $invoice->id,
                    'confirmed_by' => null,
                    'gateway' => 'wangov',
                    'gateway_transaction_id' => null,
                    'gateway_reference' => $invoice->payment_reference,
                    'payment_channel' => 'wangov_checkout',
                    'amount_due' => $invoice->amount,
                    'amount_paid' => 0,
                    'currency' => $invoice->currency,
                    'status' => PaymentStatus::Pending,
                    'raw_payload' => [],
                    'verification_payload' => [],
                    'initiated_at' => now(),
                ]);
            }

            $invoice->update([
                'gateway' => 'wangov',
                'status' => InvoiceStatus::Initiated,
            ]);

            $application->update([
                'status' => VisaApplicationStatus::PaymentPending,
                'last_status_changed_at' => now(),
            ]);

            if (! (bool) config('services.wangov.enabled', false)) {
                $this->storeGatewayPayload($pendingPayment, [
                    'mode' => 'sandbox',
                    'message' => 'WanGov integration disabled; bill staged locally.',
                ]);

                return [
                    'status' => 'sandbox_registered',
                    'reference' => $invoice->payment_reference,
                ];
            }

            $correlationId = (string) Str::uuid();
            $payload = $this->wangovPayload($application, $payerPhone);

            try {
                $response = $this->client->create($payload, $correlationId);
                $this->storeGatewayPayload($pendingPayment, [
                    'correlation_id' => $correlationId,
                    'request' => $payload,
                    'response' => $response,
                ]);

                return [
                    'status' => 'registered',
                    'reference' => $invoice->payment_reference,
                    'response' => $response,
                    'checkout_url' => $this->extractCheckoutUrl($response),
                ];
            } catch (\Throwable $e) {
                Log::error('WANGOV Emergency Travel Certificate bill registration failed', [
                    'x_corr_id' => $correlationId,
                    'payment_reference' => $invoice->payment_reference,
                    'type' => $e::class,
                    'error' => $e->getMessage(),
                ]);

                $pendingPayment->update([
                    'status' => PaymentStatus::Failed,
                    'failed_at' => now(),
                    'failure_reason' => $e->getMessage(),
                    'raw_payload' => [
                        'correlation_id' => $correlationId,
                        'request' => $payload,
                        'error' => $e->getMessage(),
                    ],
                ]);

                $invoice->update(['status' => InvoiceStatus::Failed]);
                $application->update([
                    'status' => VisaApplicationStatus::AwaitingPayment,
                    'last_status_changed_at' => now(),
                ]);

                throw $e;
            }
        });
    }

    private function wangovPayload(VisaApplication $application, ?string $payerPhone = null): array
    {
        $invoice = $application->latestInvoice;
        $passenger = $application->passenger;
        $phone = trim((string) ($payerPhone ?: $passenger?->phone ?: ''));

        return [
            'phone_number' => $phone,
            'amount' => (float) $invoice->amount,
            'currency' => (string) $invoice->currency,
            'applicant_nin' => (string) config('services.wangov.fallback_applicant_nin', 'SLIDETC'),
            'applicant_fullname' => (string) ($passenger?->full_name ?: $application->application_no),
            'application_number' => (string) $invoice->payment_reference,
            'expires_at' => $invoice->expires_at?->toIso8601String(),
        ];
    }

    private function storeGatewayPayload(Payment $payment, array $payload): void
    {
        $payment->update([
            'raw_payload' => array_filter([
                ...((array) ($payment->raw_payload ?? [])),
                'wangov_registration' => $payload,
            ]),
        ]);
    }

    private function extractCheckoutUrl(array $response): ?string
    {
        foreach (['checkout_url', 'checkoutUrl', 'payment_url', 'paymentUrl', 'url', 'redirect_url', 'redirectUrl'] as $key) {
            $value = $response[$key] ?? null;

            if (! is_string($value) || ! filter_var($value, FILTER_VALIDATE_URL)) {
                continue;
            }

            if ($this->checkoutUrlAllowed($value)) {
                return $value;
            }

            Log::warning('WANGOV checkout URL rejected by host allowlist', [
                'key' => $key,
                'scheme' => parse_url($value, PHP_URL_SCHEME),
                'host' => parse_url($value, PHP_URL_HOST),
            ]);
        }

        return null;
    }

    private function checkoutUrlAllowed(string $url): bool
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        return in_array($host, $this->checkoutAllowedHosts(), true);
    }

    private function checkoutAllowedHosts(): array
    {
        $hosts = array_map(
            fn (string $host): string => strtolower($host),
            array_filter((array) config('services.wangov.checkout_allowed_hosts', []))
        );

        $baseHost = strtolower((string) parse_url((string) config('services.wangov.external.base_url', ''), PHP_URL_HOST));

        if ($baseHost !== '') {
            $hosts[] = $baseHost;
        }

        return array_values(array_unique(array_filter($hosts)));
    }
}
