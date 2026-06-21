<?php
// FILE: app/Livewire/Staff/Permits/Show.php

namespace App\Livewire\Staff\Permits;

use App\Models\Permit;
use App\Models\PermitExtension;
use App\Services\Documents\BuildVirtualVisaPayloadService;
use App\Services\Notifications\SendPermitEmailService;
use App\Services\Passenger\BuildPassengerHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Show extends Component
{
    public Permit $permit;
    public array $travelerHistory = [];

    public function mount(Permit $permit, BuildPassengerHistoryService $historyService): void
    {
        $this->permit = $permit->load($this->permitRelations());
        $this->travelerHistory = $historyService->handle(
            $this->permit->visaApplication?->passenger?->passport_number
        );
    }

    public function emailPermit(SendPermitEmailService $service): void
    {
        $service->handle($this->permit);

        session()->flash('success', 'Official email notification processed.');
    }

    public function render(
        BuildVirtualVisaPayloadService $payloadService,
        BuildPassengerHistoryService $historyService
    ): View {
        $permit = $this->permit->fresh($this->permitRelations());

        [$latestLinkedPermit, $linkedPermitNotice] = $this->resolveLatestLinkedPermit($permit);
        $pendingExtension = $this->resolvePendingExtension($permit);

        $this->travelerHistory = $historyService->handle(
            $permit->visaApplication?->passenger?->passport_number
        );

        return view('livewire.staff.permits.show', [
            'permit' => $permit,
            'virtualVisa' => $payloadService->handle($permit),
            'latestLinkedPermit' => $latestLinkedPermit,
            'linkedPermitNotice' => $linkedPermitNotice,
            'permitLifecycleStatus' => $this->resolveLifecycleStatus($permit),
            'pendingExtension' => $pendingExtension,
            'travelerHistory' => $this->travelerHistory,
        ]);
    }

    protected function permitRelations(): array
    {
        return [
            'visaApplication.passenger',
            'visaApplication.airport',
            'visaApplication.desk',
            'payment',
            'receipt',
            'issuer',
            'checker',
            'printLogs',
            'verifications',
            'fraudFlags',
            'notificationLogs',
        ];
    }

    protected function resolveLatestLinkedPermit(Permit $permit): array
    {
        if (! Schema::hasColumn('permits', 'parent_permit_id')) {
            return [null, null];
        }

        $current = $permit;
        $visited = [];
        $foundNewerPermit = false;

        for ($i = 0; $i < 10; $i++) {
            if (in_array($current->id, $visited, true)) {
                break;
            }

            $visited[] = $current->id;

            $child = Permit::query()
                ->with($this->permitRelations())
                ->where('parent_permit_id', $current->id)
                ->latest('id')
                ->first();

            if (! $child) {
                break;
            }

            $current = $child;
            $foundNewerPermit = true;
        }

        if (! $foundNewerPermit || $current->id === $permit->id) {
            return [null, null];
        }

        $status = $this->resolveLifecycleStatus($permit);

        $notice = match ($status) {
            'extended' => "This permit was extended and superseded by permit {$current->permit_no}.",
            'replaced' => "This permit was replaced by permit {$current->permit_no}.",
            default => "This permit is no longer the latest linked record. Permit {$current->permit_no} is the newest linked permit.",
        };

        return [$current, $notice];
    }

    protected function resolvePendingExtension(Permit $permit): ?PermitExtension
    {
        if (! Schema::hasTable('permit_extensions')) {
            return null;
        }

        return PermitExtension::query()
            ->with(['requester'])
            ->where('original_permit_id', $permit->id)
            ->where('status', 'pending')
            ->latest('id')
            ->first();
    }

    protected function resolveLifecycleStatus(Permit $permit): string
    {
        return strtolower((string) ($permit->lifecycle_status ?? $permit->permit_status ?? 'active'));
    }
}