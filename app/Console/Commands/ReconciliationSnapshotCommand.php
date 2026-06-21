<?php

namespace App\Console\Commands;

use App\Services\Reporting\ReconciliationService;
use Illuminate\Console\Command;

class ReconciliationSnapshotCommand extends Command
{
    protected $signature = 'reconciliation:snapshot';
    protected $description = 'Generate reconciliation snapshot summary';

    public function handle(ReconciliationService $service): int
    {
        $summary = $service->summary();

        foreach ($summary as $key => $value) {
            $this->line($key . ': ' . $value);
        }

        return self::SUCCESS;
    }
}
