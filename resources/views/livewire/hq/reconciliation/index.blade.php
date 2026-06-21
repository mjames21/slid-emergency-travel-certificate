<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Reconciliation</h1>
        <p class="text-sm text-gray-600">Financial and issuance mismatch monitoring.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-5">
        @foreach ($summary as $label => $value)
            <div class="rounded-xl bg-white p-5 shadow">
                <div class="text-sm text-gray-500">{{ str_replace('_', ' ', $label) }}</div>
                <div class="mt-2 text-3xl font-bold">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Applications Without Invoice</h2>
            <div class="mt-4 space-y-3">
                @forelse ($mismatches['applications_without_invoice'] as $item)
                    <div class="rounded-lg border border-gray-200 px-4 py-3 text-sm">
                        <div class="font-medium text-gray-900">{{ $item->application_no }}</div>
                        <div class="text-gray-500">{{ $item->passenger->full_name }} · {{ $item->airport->code }}</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">No mismatches.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Paid Invoices Without Payment</h2>
            <div class="mt-4 space-y-3">
                @forelse ($mismatches['paid_invoices_without_payment'] as $item)
                    <div class="rounded-lg border border-gray-200 px-4 py-3 text-sm">
                        <div class="font-medium text-gray-900">{{ $item->invoice_no }}</div>
                        <div class="text-gray-500">{{ $item->visaApplication->passenger->full_name }} · {{ $item->visaApplication->airport->code }}</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">No mismatches.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Payments Without Receipt</h2>
            <div class="mt-4 space-y-3">
                @forelse ($mismatches['payments_without_receipt'] as $item)
                    <div class="rounded-lg border border-gray-200 px-4 py-3 text-sm">
                        <div class="font-medium text-gray-900">{{ $item->invoice->invoice_no }}</div>
                        <div class="text-gray-500">{{ $item->invoice->visaApplication->passenger->full_name }} · {{ $item->invoice->visaApplication->airport->code }}</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">No mismatches.</div>
                @endforelse
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Permits Without Payment or Waiver</h2>
            <div class="mt-4 space-y-3">
                @forelse ($mismatches['permits_without_payment_or_waiver'] as $item)
                    <div class="rounded-lg border border-gray-200 px-4 py-3 text-sm">
                        <div class="font-medium text-gray-900">{{ $item->permit_no }}</div>
                        <div class="text-gray-500">{{ $item->visaApplication->passenger->full_name }} · {{ $item->visaApplication->airport->code }}</div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">No mismatches.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
