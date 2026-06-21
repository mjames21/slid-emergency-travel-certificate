<?php

namespace App\Livewire\Hq\InternationalReadiness;

use App\Models\PolicyApproval;
use App\Models\TravelDocumentAlert;
use App\Models\TravelRequirementRule;
use App\Models\WatchlistEntry;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        return view('livewire.hq.international-readiness.index', [
            'policyApprovals' => PolicyApproval::query()
                ->orderByRaw("case status when 'pending' then 1 when 'approved' then 2 else 3 end")
                ->orderBy('policy_area')
                ->get(),
            'activeTravelRulesCount' => TravelRequirementRule::query()->where('active', true)->count(),
            'activeWatchlistCount' => WatchlistEntry::query()->where('status', 'active')->count(),
            'documentAlertCount' => TravelDocumentAlert::query()->count(),
            'recentRules' => TravelRequirementRule::query()
                ->latest('updated_at')
                ->take(5)
                ->get(),
            'certificationEvidence' => collect(config('certification.evidence_register', [])),
        ]);
    }
}
