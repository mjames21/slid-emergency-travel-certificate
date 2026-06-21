<?php

namespace App\Livewire\Hq\AuditLogs;

use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.hq.audit-logs.index', [
            'logs' => AuditLog::with(['user', 'airport', 'desk'])->latest()->paginate(25),
        ]);
    }
}
