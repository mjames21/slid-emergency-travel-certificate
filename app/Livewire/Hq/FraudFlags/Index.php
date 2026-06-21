<?php

namespace App\Livewire\Hq\FraudFlags;

use App\Models\FraudFlag;
use App\Services\Reporting\CsvExportService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $severity = '';

    #[Url]
    public string $resolved = '';

    public function exportCsv(CsvExportService $csvExportService): StreamedResponse
    {
        $rows = $this->query()->cursor()->map(function ($flag) {
            return [
                optional($flag->flagged_at)->format('Y-m-d H:i:s'),
                $flag->flag_type,
                $flag->severity,
                $flag->description,
                $flag->resolved ? 'Yes' : 'No',
                optional($flag->resolved_at)->format('Y-m-d H:i:s'),
            ];
        });

        return $csvExportService->stream(
            'fraud-flags-' . now()->format('Ymd-His') . '.csv',
            ['Flagged At', 'Type', 'Severity', 'Description', 'Resolved', 'Resolved At'],
            $rows
        );
    }

    protected function query()
    {
        return FraudFlag::query()
            ->with(['visaApplication', 'permit', 'payment', 'flagger', 'resolver'])
            ->when($this->severity !== '', fn ($query) => $query->where('severity', $this->severity))
            ->when($this->resolved !== '', fn ($query) => $query->where('resolved', $this->resolved === '1'))
            ->latest();
    }

    public function render(): View
    {
        return view('livewire.hq.fraud-flags.index', [
            'flags' => $this->query()->paginate(20),
        ]);
    }
}
