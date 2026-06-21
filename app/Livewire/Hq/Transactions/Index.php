<?php

namespace App\Livewire\Hq\Transactions;

use App\Models\Airport;
use App\Services\Reporting\CsvExportService;
use App\Services\Reporting\HqReportingService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $airport_id = '';

    #[Url]
    public string $date_from = '';

    #[Url]
    public string $date_to = '';

    public int $perPage = 20;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->toDateString();
        $this->date_to = now()->toDateString();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }
    public function updatingAirportId(): void { $this->resetPage(); }
    public function updatingDateFrom(): void { $this->resetPage(); }
    public function updatingDateTo(): void { $this->resetPage(); }

    public function exportCsv(HqReportingService $reportingService, CsvExportService $csvExportService): StreamedResponse
    {
        $rows = $reportingService->transactionsQuery($this->filters())->cursor()->map(function ($payment) {
            return [
                optional($payment->created_at)->format('Y-m-d H:i:s'),
                $payment->invoice->visaApplication->airport->code ?? '',
                $payment->invoice->visaApplication->passenger->full_name ?? '',
                $payment->invoice->visaApplication->passenger->passport_number ?? '',
                $payment->invoice->invoice_no ?? '',
                $payment->invoice->payment_reference ?? '',
                $payment->gateway_transaction_id ?? '',
                number_format((float) $payment->amount_paid, 2, '.', ''),
                $payment->currency,
                $payment->status->value,
                $payment->receipt?->receipt_no ?? '',
            ];
        });

        return $csvExportService->stream(
            'transactions-' . now()->format('Ymd-His') . '.csv',
            ['Date', 'Airport', 'Passenger', 'Passport', 'Invoice', 'Payment Reference', 'Gateway Txn', 'Amount', 'Currency', 'Status', 'Receipt'],
            $rows
        );
    }

    protected function filters(): array
    {
        return [
            'search' => $this->search ?: null,
            'status' => $this->status ?: null,
            'airport_id' => $this->airport_id ?: null,
            'date_from' => $this->date_from ?: null,
            'date_to' => $this->date_to ?: null,
        ];
    }

    public function render(HqReportingService $reportingService): View
    {
        return view('livewire.hq.transactions.index', [
            'transactions' => $reportingService->paginatedTransactions($this->filters(), $this->perPage),
            'airports' => Airport::query()->orderBy('name')->get(),
        ]);
    }
}
