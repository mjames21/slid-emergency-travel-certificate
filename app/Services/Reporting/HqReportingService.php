<?php

namespace App\Services\Reporting;

use App\Models\Payment;
use App\Models\Permit;
use App\Models\VisaApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HqReportingService
{
    public function dashboardMetrics(array $filters = []): array
    {
        $applications = VisaApplication::query()
            ->when($filters['airport_id'] ?? null, fn (Builder $q, $airportId) => $q->where('airport_id', $airportId))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));

        $payments = Payment::query()
            ->where('status', 'successful')
            ->whereHas('invoice.visaApplication', function (Builder $query) use ($filters) {
                $query
                    ->when($filters['airport_id'] ?? null, fn (Builder $q, $airportId) => $q->where('airport_id', $airportId))
                    ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                    ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date));
            });

        $permits = Permit::query()
            ->whereHas('visaApplication', function (Builder $query) use ($filters) {
                $query
                    ->when($filters['airport_id'] ?? null, fn (Builder $q, $airportId) => $q->where('airport_id', $airportId));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('issued_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('issued_at', '<=', $date));

        return [
            'total_applications' => (clone $applications)->count(),
            'awaiting_payment' => (clone $applications)->where('status', 'awaiting_payment')->count(),
            'under_review' => (clone $applications)->where('status', 'under_review')->count(),
            'approved' => (clone $applications)->where('status', 'approved')->count(),
            'permits_issued' => (clone $permits)->count(),
            'successful_payments' => (clone $payments)->count(),
            'total_revenue' => (float) ((clone $payments)->sum('amount_paid')),
        ];
    }

    public function airportRevenue(array $filters = []): Collection
    {
        return Payment::query()
            ->selectRaw('airports.name as airport_name, airports.code as airport_code, COUNT(payments.id) as total_payments, COALESCE(SUM(payments.amount_paid), 0) as total_revenue')
            ->join('invoices', 'invoices.id', '=', 'payments.invoice_id')
            ->join('visa_applications', 'visa_applications.id', '=', 'invoices.visa_application_id')
            ->join('airports', 'airports.id', '=', 'visa_applications.airport_id')
            ->where('payments.status', 'successful')
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('payments.created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('payments.created_at', '<=', $date))
            ->groupBy('airports.name', 'airports.code')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function transactionsQuery(array $filters = []): Builder
    {
        return Payment::query()
            ->with([
                'invoice.visaApplication.passenger',
                'invoice.visaApplication.airport',
                'invoice.visaApplication.creator',
                'receipt',
            ])
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['airport_id'] ?? null, function (Builder $q, $airportId) {
                $q->whereHas('invoice.visaApplication', fn (Builder $sub) => $sub->where('airport_id', $airportId));
            })
            ->when($filters['search'] ?? null, function (Builder $q, $search) {
                $like = '%' . trim($search) . '%';

                $q->where(function (Builder $inner) use ($like) {
                    $inner
                        ->where('gateway_transaction_id', 'like', $like)
                        ->orWhere('gateway_reference', 'like', $like)
                        ->orWhereHas('invoice', function (Builder $invoiceQuery) use ($like) {
                            $invoiceQuery
                                ->where('invoice_no', 'like', $like)
                                ->orWhere('payment_reference', 'like', $like)
                                ->orWhereHas('visaApplication', function (Builder $applicationQuery) use ($like) {
                                    $applicationQuery
                                        ->where('application_no', 'like', $like)
                                        ->orWhereHas('passenger', function (Builder $passengerQuery) use ($like) {
                                            $passengerQuery
                                                ->where('full_name', 'like', $like)
                                                ->orWhere('passport_number', 'like', $like);
                                        });
                                });
                        });
                });
            })
            ->when($filters['date_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('created_at');
    }

    public function paginatedTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->transactionsQuery($filters)->paginate($perPage);
    }
}
