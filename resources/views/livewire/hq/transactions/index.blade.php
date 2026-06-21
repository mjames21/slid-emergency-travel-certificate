<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
            <p class="text-sm text-gray-600">All payment transactions across airports.</p>
        </div>

        <button wire:click="exportCsv" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
            Export CSV
        </button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow">
        <div class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-lg border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select wire:model.live="status" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">All statuses</option>
                    <option value="pending">pending</option>
                    <option value="successful">successful</option>
                    <option value="failed">failed</option>
                    <option value="cancelled">cancelled</option>
                    <option value="reversed">reversed</option>
                    <option value="refunded">refunded</option>
                    <option value="under_review">under_review</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Airport</label>
                <select wire:model.live="airport_id" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">All airports</option>
                    @foreach ($airports as $airport)
                        <option value="{{ $airport->id }}">{{ $airport->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Date From</label>
                    <input type="date" wire:model.live="date_from" class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Date To</label>
                    <input type="date" wire:model.live="date_to" class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b text-left text-gray-500">
                    <tr>
                        <th class="py-3 pr-4">Date</th>
                        <th class="py-3 pr-4">Airport</th>
                        <th class="py-3 pr-4">Passenger</th>
                        <th class="py-3 pr-4">Invoice</th>
                        <th class="py-3 pr-4">Reference</th>
                        <th class="py-3 pr-4">Amount</th>
                        <th class="py-3 pr-4">Status</th>
                        <th class="py-3 pr-4">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr class="border-b">
                            <td class="py-3 pr-4">{{ optional($transaction->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="py-3 pr-4">{{ $transaction->invoice->visaApplication->airport->code }}</td>
                            <td class="py-3 pr-4">{{ $transaction->invoice->visaApplication->passenger->full_name }}</td>
                            <td class="py-3 pr-4">{{ $transaction->invoice->invoice_no }}</td>
                            <td class="py-3 pr-4">{{ $transaction->invoice->payment_reference }}</td>
                            <td class="py-3 pr-4">{{ number_format((float) $transaction->amount_paid, 2) }} {{ $transaction->currency }}</td>
                            <td class="py-3 pr-4">{{ $transaction->status->value }}</td>
                            <td class="py-3 pr-4">{{ $transaction->receipt?->receipt_no ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-4 text-gray-500">No transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
