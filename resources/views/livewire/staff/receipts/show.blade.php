{{-- FILE: resources/views/livewire/staff/receipts/show.blade.php --}}
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Receipt</h1>
            <p class="text-sm text-gray-600">{{ $receipt->receipt_no }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('documents.receipts.show', $receipt) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Open PDF
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900">Receipt Details</h2>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm text-gray-500">Receipt Number</div>
                    <div class="font-medium text-gray-900">{{ $receipt->receipt_no }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Issued At</div>
                    <div class="font-medium text-gray-900">{{ optional($receipt->issued_at)->format('Y-m-d H:i') ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Printed At</div>
                    <div class="font-medium text-gray-900">{{ optional($receipt->printed_at)->format('Y-m-d H:i') ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Issued By</div>
                    <div class="font-medium text-gray-900">{{ $receipt->issuer?->name ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Receipt Source</div>
                    <div class="font-medium text-gray-900">{{ strtoupper(str_replace('_', ' ', $receipt->receipt_source ?: 'internal')) }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Uploaded Evidence</div>
                    <div class="font-medium text-gray-900">{{ $receipt->evidence_path ? 'Stored privately' : '—' }}</div>
                </div>

                @if ($receipt->evidence_original_name)
                    <div>
                        <div class="text-sm text-gray-500">Evidence Filename</div>
                        <div class="break-all font-medium text-gray-900">{{ $receipt->evidence_original_name }}</div>
                    </div>
                @endif

                <div>
                    <div class="text-sm text-gray-500">Document Hash</div>
                    <div class="break-all font-medium text-gray-900">{{ $receipt->document_hash ?: '—' }}</div>
                </div>

                @if ($receipt->evidence_hash)
                    <div>
                        <div class="text-sm text-gray-500">Evidence Hash</div>
                        <div class="break-all font-medium text-gray-900">{{ $receipt->evidence_hash }}</div>
                    </div>
                @endif

                <div>
                    <div class="text-sm text-gray-500">Document Path</div>
                    <div class="font-medium text-gray-900">{{ $receipt->document_path ?: '—' }}</div>
                </div>
            </div>

            <h2 class="mt-8 text-lg font-semibold text-gray-900">Payment Details</h2>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm text-gray-500">Amount Due</div>
                    <div class="font-medium text-gray-900">
                        {{ number_format((float) $receipt->payment->amount_due, 2) }} {{ strtoupper($receipt->payment->currency) }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Amount Paid</div>
                    <div class="font-medium text-gray-900">
                        {{ number_format((float) $receipt->payment->amount_paid, 2) }} {{ strtoupper($receipt->payment->currency) }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Payment Status</div>
                    <div class="font-medium text-gray-900">{{ strtoupper($receipt->payment->status->value) }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Payment Channel</div>
                    <div class="font-medium text-gray-900">{{ strtoupper($receipt->payment->payment_channel ?: '—') }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Gateway</div>
                    <div class="font-medium text-gray-900">{{ strtoupper($receipt->payment->gateway) }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Gateway Reference</div>
                    <div class="font-medium text-gray-900">{{ $receipt->payment->gateway_reference ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Gateway Transaction ID</div>
                    <div class="font-medium text-gray-900">{{ $receipt->payment->gateway_transaction_id ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Verified At</div>
                    <div class="font-medium text-gray-900">{{ optional($receipt->payment->verified_at)->format('Y-m-d H:i') ?: '—' }}</div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Traveler</h2>

                <div class="mt-4 space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">Full Name</div>
                        <div class="font-medium text-gray-900">{{ $receipt->payment->invoice->visaApplication->passenger->full_name }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Passport Number</div>
                        <div class="font-medium text-gray-900">{{ $receipt->payment->invoice->visaApplication->passenger->passport_number }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Nationality</div>
                        <div class="font-medium text-gray-900">{{ $receipt->payment->invoice->visaApplication->passenger->nationality }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Linked Records</h2>

                <div class="mt-4 space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">Invoice</div>
                        <a href="{{ route('staff.invoices.show', $receipt->payment->invoice) }}" class="font-medium text-gray-900 underline">
                            {{ $receipt->payment->invoice->invoice_no }}
                        </a>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Application</div>
                        @if (auth()->user()?->hasAnyStaffTitle(['system_administrator', 'airport_manager', 'shift_supervisor', 'visa_processing_officer']))
                            <a href="{{ route('staff.applications.show', $receipt->payment->invoice->visaApplication) }}" class="font-medium text-gray-900 underline">
                                {{ $receipt->payment->invoice->visaApplication->application_no }}
                            </a>
                        @else
                            <div class="font-medium text-gray-900">{{ $receipt->payment->invoice->visaApplication->application_no }}</div>
                        @endif
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Permit</div>
                        @php $permit = $receipt->payment->invoice->visaApplication->permit; @endphp
                        @if ($permit && auth()->user()->can('view', $permit))
                            <a href="{{ route('staff.permits.show', $permit) }}" class="font-medium text-gray-900 underline">
                                {{ $permit->permit_no }}
                            </a>
                        @elseif ($permit)
                            <div class="font-medium text-gray-500">Restricted</div>
                        @else
                            <div class="font-medium text-gray-900">Not issued</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-emerald-900">Official Note</h2>
                <div class="mt-3 text-sm leading-6 text-emerald-800">
                    This receipt confirms that payment has been recorded in the official immigration payment workflow. Retain it for permit issuance, inspection, and audit purposes.
                </div>
            </div>
        </div>
    </div>
</div>
