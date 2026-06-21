<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WangovDoctorCommand extends Command
{
    protected $signature = 'wangov:doctor';

    protected $description = 'Check WanGov/GovPay configuration and print callback URLs.';

    public function handle(): int
    {
        $external = (array) config('services.wangov.external', []);
        $webhook = (array) config('services.wangov.webhook', []);

        $enabled = (bool) config('services.wangov.enabled');

        $checks = [
            ['WANGOV_ENABLED', $enabled, $enabled],
            ['WANGOV_BASE_URL', trim((string) ($external['base_url'] ?? '')) !== '', $external['base_url'] ?? ''],
            ['WANGOV_ENDPOINT', trim((string) ($external['endpoint'] ?? '')) !== '', $external['endpoint'] ?? ''],
            ['WANGOV_SERVICE_KEY', trim((string) ($external['service_key'] ?? '')) !== '', $this->mask((string) ($external['service_key'] ?? ''))],
            ['WANGOV_SERVICE_CODE', trim((string) ($external['service_code'] ?? '')) !== '', $external['service_code'] ?? ''],
            ['WANGOV_SERVICE_DISPLAY', trim((string) ($external['service_display'] ?? '')) !== '', $external['service_display'] ?? ''],
            ['WANGOV_WEBHOOK_SECRET', trim((string) ($webhook['vendor_secret'] ?? '')) !== '', $this->mask((string) ($webhook['vendor_secret'] ?? ''))],
            ['APP_URL', trim((string) config('app.url')) !== '', config('app.url')],
        ];

        $this->newLine();
        $this->info('WanGov / GovPay readiness');
        $this->table(
            ['Setting', 'Status', 'Value'],
            array_map(fn (array $check): array => [
                $check[0],
                $check[1] ? 'OK' : 'MISSING',
                is_bool($check[2]) ? ($check[2] ? 'true' : 'false') : (string) $check[2],
            ], $checks)
        );

        $this->newLine();
        $this->line('Give WanGov one of these callback URLs:');
        $this->line('  ' . url('/api/wangov/payment-update'));
        $this->line('  ' . url('/webhooks/wangov'));

        $this->newLine();
        $this->line('Expected callback authentication header:');
        $this->line('  X-Webhook-Secret: <WANGOV_WEBHOOK_SECRET>');
        $this->line('  or X-Service-Key: <WANGOV_WEBHOOK_SECRET>');
        $this->line('  or Authorization: Bearer <WANGOV_WEBHOOK_SECRET>');

        $ready = collect($checks)->every(fn (array $check): bool => $check[1] === true);

        if (! $ready) {
            $this->warn('WanGov is not fully configured yet. Fill the missing .env values, then run php artisan config:clear and php artisan wangov:doctor again.');

            return self::FAILURE;
        }

        $this->info('WanGov configuration looks ready for a provider sandbox/live payment test.');

        return self::SUCCESS;
    }

    private function mask(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4) . str_repeat('*', max(strlen($value) - 8, 4)) . substr($value, -4);
    }
}
