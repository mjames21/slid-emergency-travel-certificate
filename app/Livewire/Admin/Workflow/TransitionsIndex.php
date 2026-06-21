<?php

namespace App\Livewire\Admin\Workflow;

use App\Models\StaffTitleWorkflowTransition;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TransitionsIndex extends Component
{
    public function render(): View
    {
        return view('livewire.admin.workflow.transitions-index', [
            'transitions' => StaffTitleWorkflowTransition::with('staffTitle')
                ->orderBy('from_status_key')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }
}
