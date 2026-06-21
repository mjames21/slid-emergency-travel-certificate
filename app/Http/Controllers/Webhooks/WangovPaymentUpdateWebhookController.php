<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\PaymentWebhookEvent;
use App\Services\Evisa\ProcessWangovPaymentWebhookService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WangovPaymentUpdateWebhookController extends Controller
{
    public function __invoke(Request $request, ProcessWangovPaymentWebhookService $service): JsonResponse
    {
        $requestId = (string) (
            $request->header('X-Request-ID')
            ?: $request->header('X-Correlation-Id')
            ?: Str::uuid()
        );

        if (! $this->requestIpAllowed($request, $requestId)) {
            return response()->json(['ok' => false, 'error' => 'forbidden_source'], 403);
        }

        if (! $this->payloadSizeAllowed($request, $requestId)) {
            return response()->json(['ok' => false, 'error' => 'payload_too_large'], 413);
        }

        if (! $this->verifyWebhookSecret($request, $requestId)) {
            return response()->json(['ok' => false, 'error' => 'unauthorized'], 401);
        }

        $payload = $request->all();
        $payloadHash = hash('sha256', (string) $request->getContent());
        $reference = strtoupper($this->extractString($payload, [
            'application_number', 'applicationNumber', 'reference', 'payment_reference', 'paymentReference',
            'billing_number', 'billingNumber', 'bill_reference', 'billReference',
        ]));

        if ($reference === '') {
            return response()->json(['ok' => false, 'error' => 'missing_reference'], 422);
        }

        $status = strtolower($this->extractString($payload, [
            'billing_status', 'billingStatus', 'status', 'payment_status', 'paymentStatus', 'state',
        ]));

        $providerReference = $this->extractString($payload, [
            'transaction_id', 'transactionId', 'provider_reference', 'providerReference',
            'tx_ref', 'txRef', 'reference_id', 'referenceId',
        ]);

        $eventId = $this->extractString($payload, ['event_id', 'eventId', 'id']) ?: null;
        $event = $this->recordWebhookEvent($eventId, $reference, $status, $payload, $requestId, (string) $request->ip(), $payloadHash);

        if ($event->processed_at) {
            return response()->json([
                'ok' => true,
                'handled' => true,
                'duplicate' => true,
                'reference' => $reference,
            ]);
        }

        $result = $service->handle(
            $reference,
            $status,
            $payload,
            $providerReference !== '' ? $providerReference : null,
            $this->extractFloat($payload, ['amount', 'amount_paid', 'amountPaid', 'total', 'total_amount', 'totalAmount']),
            strtoupper($this->extractString($payload, ['currency', 'currency_code', 'currencyCode'])) ?: null,
            $this->extractTime($payload, ['created_at', 'createdAt', 'paid_at', 'paidAt', 'timestamp', 'time'])
        );

        $event->update(['processed_at' => now()]);

        Log::info('WANGOV Emergency Travel Certificate webhook processed', [
            'x_req_id' => $requestId,
            'reference' => $reference,
            'status' => $status,
            'result' => $result,
        ]);

        return response()->json($result);
    }

    private function requestIpAllowed(Request $request, string $requestId): bool
    {
        $allowedIps = (array) config('services.wangov.webhook.ips', []);

        if ($allowedIps === []) {
            return true;
        }

        $allowed = in_array((string) $request->ip(), array_map('strval', $allowedIps), true);

        if (! $allowed) {
            Log::warning('WANGOV webhook rejected by IP allowlist', [
                'x_req_id' => $requestId,
                'ip' => $request->ip(),
            ]);
        }

        return $allowed;
    }

    private function payloadSizeAllowed(Request $request, string $requestId): bool
    {
        $maxBytes = (int) config('services.wangov.webhook.max_payload_bytes', 20000);
        $bytes = strlen((string) $request->getContent());

        if ($maxBytes <= 0 || $bytes <= $maxBytes) {
            return true;
        }

        Log::warning('WANGOV webhook rejected by payload size limit', [
            'x_req_id' => $requestId,
            'bytes' => $bytes,
            'max_bytes' => $maxBytes,
        ]);

        return false;
    }

    private function recordWebhookEvent(
        ?string $eventId,
        string $reference,
        string $status,
        array $payload,
        string $requestId,
        string $sourceIp,
        string $payloadHash
    ): PaymentWebhookEvent
    {
        if ($eventId !== null) {
            return PaymentWebhookEvent::query()->firstOrCreate(
                ['provider' => 'wangov', 'event_id' => $eventId],
                [
                    'request_id' => $requestId,
                    'source_ip' => $sourceIp,
                    'reference' => $reference,
                    'payment_reference' => $reference,
                    'status' => $status ?: null,
                    'payload' => $payload,
                    'payload_sha256' => $payloadHash,
                ]
            );
        }

        return PaymentWebhookEvent::query()->create([
            'provider' => 'wangov',
            'event_id' => null,
            'request_id' => $requestId,
            'source_ip' => $sourceIp,
            'reference' => $reference,
            'payment_reference' => $reference,
            'status' => $status ?: null,
            'payload' => $payload,
            'payload_sha256' => $payloadHash,
        ]);
    }

    private function verifyWebhookSecret(Request $request, string $requestId): bool
    {
        $expected = (string) config('services.wangov.webhook.vendor_secret', '');

        if ($expected === '') {
            Log::error('WANGOV Emergency Travel Certificate webhook missing shared secret config', ['x_req_id' => $requestId]);

            return false;
        }

        $header = (string) (
            $request->header('X-Webhook-Secret')
            ?: $request->header('x-webhook-secret')
            ?: $request->header('X-Service-Key')
            ?: $request->header('x-service-key')
            ?: ''
        );

        $authorization = (string) $request->header('Authorization', '');

        if ($header === '' && str_starts_with(strtolower($authorization), 'bearer ')) {
            $header = trim(substr($authorization, 7));
        }

        return $header !== '' && hash_equals($expected, $header);
    }

    private function extractString(array $payload, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($payload[$key]) && is_scalar($payload[$key])) {
                return trim((string) $payload[$key]);
            }
        }

        return '';
    }

    private function extractFloat(array $payload, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return (float) $payload[$key];
            }
        }

        return null;
    }

    private function extractTime(array $payload, array $keys): ?Carbon
    {
        foreach ($keys as $key) {
            if (! empty($payload[$key]) && is_scalar($payload[$key])) {
                try {
                    return Carbon::parse((string) $payload[$key]);
                } catch (\Throwable) {
                    return null;
                }
            }
        }

        return null;
    }
}
