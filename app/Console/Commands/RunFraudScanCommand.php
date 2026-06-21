<?php

namespace App\Console\Commands;

use App\Services\Reporting\FraudMonitoringService;
use Illuminate\Console\Command;

class RunFraudScanCommand extends Command
{
    protected $signature = 'fraud:scan';
    protected $description = 'Run automated fraud monitoring scan';

    public function handle(FraudMonitoringService $service): int
    {
        $count = $service->generateFlags();

        $this->info("Fraud scan complete. Flags created: {$count}");

        return self::SUCCESS;
    }
}
