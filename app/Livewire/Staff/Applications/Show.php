<?php
// FILE: app/Livewire/Staff/Applications/Show.php

namespace App\Livewire\Staff\Applications;

use App\Models\VisaApplication;
use App\Services\Passenger\BuildPassengerHistoryService;
use App\Services\Standards\StandardsAlignmentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public VisaApplication $application;
    public array $passengerHistory = [];

    public function mount(VisaApplication $application, BuildPassengerHistoryService $historyService): void
    {
        $this->application = $application->load($this->applicationRelations());

        $this->passengerHistory = $historyService->handle(
            $this->application->passenger?->passport_number
        );
    }

    public function render(
        BuildPassengerHistoryService $historyService,
        StandardsAlignmentService $standardsService
    ): View
    {
        $application = $this->application->fresh($this->applicationRelations());

        $this->passengerHistory = $historyService->handle(
            $application->passenger?->passport_number
        );

        $readiness = $standardsService->forApplication($application, $this->passengerHistory);

        return view('livewire.staff.applications.show', [
            'application' => $application,
            'passengerHistory' => $this->passengerHistory,
            'officerFlags' => $this->officerFlags($readiness),
        ]);
    }

    protected function applicationRelations(): array
    {
        return [
            'passenger',
            'airport',
            'desk',
            'pointOfEntry',
            'permit',
            'payment.receipt',
        ];
    }

    protected function officerFlags(array $readiness): array
    {
        $items = collect($readiness['sections'] ?? [])
            ->flatMap(fn (array $section) => collect($section['items'] ?? [])->map(
                fn (array $item) => array_merge($item, ['section' => $section['label'] ?? 'Check'])
            ));

        return [
            'required' => $items->where('status', 'fail')->values()->all(),
            'advisory' => $items->where('status', 'warn')->values()->all(),
            'score' => $readiness['summary']['score'] ?? 0,
        ];
    }
}
