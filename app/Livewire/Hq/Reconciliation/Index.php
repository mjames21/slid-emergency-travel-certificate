<?php

namespace App\Livewire\Hq\Reconciliation;

use App\Services\Reporting\ReconciliationService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(ReconciliationService $service): View
    {
        return view('livewire.hq.reconciliation.index', [
            'summary' => $service->summary(),
            'mismatches' => $service->mismatches(),
        ]);
    }
}
