{{-- FILE: resources/views/livewire/staff/payments/index.blade.php --}}
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
            <p class="text-sm text-gray-600">
                @if ($paymentAirportName)
                    NRA receipt and WanGov payment queue for {{ $paymentAirportName }}.
                @else
                    NRA receipt and WanGov payment queue.
                @endif
            </p>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="grid gap-4 lg:grid-cols-[1fr_220px]">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Search payments</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-lg border-gray-300 shadow-sm"
                    placeholder="Invoice, payment reference, receipt, passport, or traveler"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select wire:model.live="status" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="open">Open payments</option>
                    <option value="pending">Pending invoice</option>
                    <option value="initiated">WanGov initiated</option>
                    <option value="paid">Paid</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl bg-white shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Traveler</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Payment</th>
                        <th class="px-4 py-3">Receipt</th>
                        <th class="px-4 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($invoices as $invoice)
                        @php
                            $application = $invoice->visaApplication;
                            $passenger = $application?->passenger;
                            $payment = $invoice->payments->sortByDesc('created_at')->first();
                            $receipt = $invoice->payments->first(fn ($payment) => $payment->receipt !== null)?->receipt;
                        @endphp
                        <tr>
                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-gray-900">{{ $invoice->invoice_no }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $invoice->payment_reference }}</div>
                                <div class="mt-2 inline-flex rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-xs font-semibold uppercase text-gray-700">
                                    {{ $invoice->status->value }}
                                </div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="font-medium text-gray-900">{{ $passenger?->full_name ?: '—' }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $passenger?->passport_number ?: '—' }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ $application?->airport?->code ?: '—' }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="font-semibold text-gray-900">{{ number_format((float) $invoice->amount, 2) }}</div>
                                <div class="text-xs text-gray-500">{{ strtoupper($invoice->currency) }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                <div class="font-medium text-gray-900">{{ strtoupper($payment?->gateway ?: $invoice->gateway ?: '—') }}</div>
                                <div class="mt-1 text-xs text-gray-500">{{ strtoupper($payment?->payment_channel ?: 'Awaiting confirmation') }}</div>
                            </td>
                            <td class="px-4 py-4 align-top">
                                @if ($receipt)
                                    <a href="{{ route('staff.receipts.show', $receipt) }}" class="font-medium text-emerald-800 underline underline-offset-2">
                                        {{ $receipt->receipt_no }}
                                    </a>
                                    <div class="mt-1 text-xs text-gray-500">{{ strtoupper(str_replace('_', ' ', $receipt->receipt_source ?: 'internal')) }}</div>
                                @else
                                    <div class="font-medium text-gray-900">Not uploaded</div>
                                    <div class="mt-1 text-xs text-gray-500">Open invoice to confirm payment.</div>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right align-top">
                                <a
                                    href="{{ route('staff.invoices.show', $invoice) }}"
                                    class="inline-flex rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                                >
                                    Open Payment
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center">
                                <h2 class="text-base font-semibold text-gray-900">No payments found</h2>
                                <p class="mt-1 text-sm text-gray-500">Try another receipt, invoice, passport, or status filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $invoices->links() }}
    </div>
</div>
