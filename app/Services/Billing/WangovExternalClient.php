<?php

namespace App\Services\Billing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WangovExternalClient
{
    public function create(array $data, string $correlationId = '', ?array $serviceConfig = null): array
    {
        $serviceConfig ??= (array) config('services.wangov.external', []);
        $correlationId = $correlationId !== '' ? $correlationId : (string) Str::uuid();

        $this->assertConfigured($serviceConfig, $correlationId);

        $baseUrl = rtrim((string) ($serviceConfig['base_url'] ?? ''), '/');
        $endpoint = '/' . ltrim((string) ($serviceConfig['endpoint'] ?? ''), '/');
        $url = $baseUrl . $endpoint;

        $payload = [
            'phone_number' => trim((string) ($data['phone_number'] ?? '')),
            'amount' => isset($data['amount']) ? (float) $data['amount'] : 0.0,
            'applicant_nin' => trim((string) ($data['applicant_nin'] ?? '')),
            'applicant_fullname' => trim((string) ($data['applicant_fullname'] ?? '')),
            'application_number' => trim((string) ($data['application_number'] ?? '')),
            'currency' => trim((string) ($data['currency'] ?? '')) ?: 'SLE',
        ];

        if (! empty($data['expires_at'])) {
            $payload['expires_at'] = (string) $data['expires_at'];
        }

        $missing = $this->missingRequiredFields($payload);

        Log::info('WANGOV Emergency Travel Certificate bill outbound', [
            'x_corr_id' => $correlationId,
            'url' => $url,
            'reference' => $payload['application_number'],
            'service_code' => (string) ($serviceConfig['service_code'] ?? ''),
            'service_slug' => (string) ($serviceConfig['service_slug'] ?? ''),
            'missing_required_fields_local' => $missing,
        ]);

        if ($missing !== []) {
            throw new \RuntimeException('WanGov payload missing required fields: ' . implode(', ', $missing));
        }

        $request = Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'X-Service-Code' => (string) ($serviceConfig['service_code'] ?? ''),
                'X-Service-Key' => (string) ($serviceConfig['service_key'] ?? ''),
                'Origin' => (string) ($serviceConfig['origin'] ?? config('app.url')),
                'Referer' => (string) ($serviceConfig['origin'] ?? config('app.url')),
                'X-Request-Id' => $correlationId,
                'X-Correlation-Id' => $correlationId,
                'X-Service-Slug' => (string) ($serviceConfig['service_slug'] ?? ''),
                'X-Service-Name' => (string) ($serviceConfig['service_display'] ?? ''),
            ])
            ->timeout((int) config('services.wangov.timeout', 15));

        $bearerToken = trim((string) ($serviceConfig['bearer_token'] ?? ''));

        if ($bearerToken !== '') {
            $request = $request->withToken($bearerToken);
        }

        $response = $request->post($url, $payload);

        if ($response->failed()) {
            Log::error('WANGOV Emergency Travel Certificate bill HTTP failed', [
                'x_corr_id' => $correlationId,
                'status' => $response->status(),
                'body' => str($response->body())->limit(8000)->toString(),
            ]);

            $response->throw();
        }

        $json = $response->json();

        return is_array($json) ? $json : ['ok' => true, 'raw' => $response->body()];
    }

    private function assertConfigured(array $serviceConfig, string $correlationId): void
    {
        $hasConfig = trim((string) ($serviceConfig['base_url'] ?? '')) !== ''
            && trim((string) ($serviceConfig['endpoint'] ?? '')) !== ''
            && trim((string) ($serviceConfig['service_code'] ?? '')) !== ''
            && trim((string) ($serviceConfig['service_display'] ?? '')) !== ''
            && trim((string) ($serviceConfig['service_key'] ?? '')) !== '';

        if ($hasConfig) {
            return;
        }

        Log::error('WANGOV Emergency Travel Certificate missing config', [
            'x_corr_id' => $correlationId,
            'has_base_url' => trim((string) ($serviceConfig['base_url'] ?? '')) !== '',
            'has_endpoint' => trim((string) ($serviceConfig['endpoint'] ?? '')) !== '',
            'has_service_code' => trim((string) ($serviceConfig['service_code'] ?? '')) !== '',
            'has_service_display' => trim((string) ($serviceConfig['service_display'] ?? '')) !== '',
            'has_service_key' => trim((string) ($serviceConfig['service_key'] ?? '')) !== '',
        ]);

        throw new \RuntimeException('WanGov payment service is not configured.');
    }

    private function missingRequiredFields(array $payload): array
    {
        $missing = [];

        foreach (['phone_number', 'applicant_nin', 'applicant_fullname', 'application_number', 'currency'] as $field) {
            if (trim((string) ($payload[$field] ?? '')) === '') {
                $missing[] = $field;
            }
        }

        if (! isset($payload['amount']) || ! is_numeric($payload['amount']) || (float) $payload['amount'] <= 0) {
            $missing[] = 'amount';
        }

        return $missing;
    }
}
