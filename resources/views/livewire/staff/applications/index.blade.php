{{-- FILE: resources/views/livewire/staff/applications/index.blade.php --}}
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Applications</h1>
            <p class="text-sm text-gray-600">Review and open visa-on-arrival applications.</p>
        </div>

        <a
            href="{{ route('staff.applications.create') }}"
            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
        >
            New Application
        </a>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="grid gap-4 md:grid-cols-[1fr_auto]">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-lg border-gray-300 shadow-sm"
                    placeholder="Application ID, passport, traveler, permit, receipt, airport"
                >
            </div>

            <div class="flex items-end">
                <a
                    href="{{ route('staff.reports.permit-expiry') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
                >
                    Expiry Report
                </a>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @forelse ($applications as $application)
            @php
                $passportKey = strtoupper(trim((string) ($application->passenger?->passport_number ?? '')));
                $history = $travelerHistories[$passportKey] ?? null;

                $paymentStatus = $application->payment?->status ?? null;
                $paymentLabel = is_object($paymentStatus) && isset($paymentStatus->value)
                    ? $paymentStatus->value
                    : ($paymentStatus ?: '—');
            @endphp

            <div class="rounded-xl bg-white p-6 shadow">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-4 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Application #{{ $application->id }}
                            </h2>

                            @if ($application->permit)
                                <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-800">
                                    Permit Issued
                                </span>
                            @else
                                <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">
                                    No Permit Yet
                                </span>
                            @endif
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 text-sm">
                            <div>
                                <div class="text-gray-500">Traveler</div>
                                <div class="font-medium text-gray-900">{{ $application->passenger?->full_name ?: '—' }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Passport</div>
                                <div class="font-medium text-gray-900">{{ $application->passenger?->passport_number ?: '—' }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Airport</div>
                                <div class="font-medium text-gray-900">{{ $application->airport?->name ?: '—' }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Desk</div>
                                <div class="font-medium text-gray-900">{{ $application->desk?->name ?: '—' }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Purpose</div>
                                <div class="font-medium text-gray-900">{{ $application->purpose_of_visit ?: '—' }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Arrival Date</div>
                                <div class="font-medium text-gray-900">{{ $application->arrival_date ?: '—' }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Valid Until</div>
                                <div class="font-medium text-gray-900">{{ $application->valid_until ?: '—' }}</div>
                            </div>

                            <div>
                                <div class="text-gray-500">Payment</div>
                                <div class="font-medium text-gray-900">{{ $paymentLabel }}</div>
                            </div>
                        </div>

                        @if (($history['found'] ?? false) === true)
                            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if (($history['is_repeat_traveler'] ?? false) === true)
                                        <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">
                                            Repeat Traveler
                                        </span>
                                    @endif

                                    @if (($history['has_extension_history'] ?? false) === true)
                                        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                            Prior Extension
                                        </span>
                                    @endif

                                    @if (($history['has_expired_permit_history'] ?? false) === true)
                                        <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800">
                                            Prior Expired Permit
                                        </span>
                                    @endif

                                    @if (($history['has_fraud_history'] ?? false) === true)
                                        <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800">
                                            Fraud History
                                        </span>
                                    @endif

                                    @if (($history['has_duplicate_passenger_records'] ?? false) === true)
                                        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                            Duplicate Passport Records
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 grid gap-3 md:grid-cols-4 text-xs">
                                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-2">
                                        <div class="uppercase tracking-wide text-gray-500">Applications</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $history['counts']['applications'] ?? 0 }}</div>
                                    </div>

                                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-2">
                                        <div class="uppercase tracking-wide text-gray-500">Permits</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $history['counts']['permits'] ?? 0 }}</div>
                                    </div>

                                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-2">
                                        <div class="uppercase tracking-wide text-gray-500">Extensions</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $history['counts']['extensions'] ?? 0 }}</div>
                                    </div>

                                    <div class="rounded-lg border border-gray-200 bg-white px-3 py-2">
                                        <div class="uppercase tracking-wide text-gray-500">Fraud Flags</div>
                                        <div class="mt-1 font-semibold text-gray-900">{{ $history['counts']['fraud_flags'] ?? 0 }}</div>
                                    </div>
                                </div>

                                <div class="mt-3 text-xs text-gray-600">
                                    Latest permit:
                                    <span class="font-medium text-gray-900">{{ $history['latest']['permit_no'] ?? '—' }}</span>
                                    · Airport:
                                    <span class="font-medium text-gray-900">{{ $history['latest']['airport_name'] ?? '—' }}</span>
                                    · Valid until:
                                    <span class="font-medium text-gray-900">{{ $history['latest']['valid_until'] ?? '—' }}</span>
                                </div>

                                @if (! empty($history['alerts']))
                                    <div class="mt-2 space-y-2">
                                        @foreach ($history['alerts'] as $alert)
                                            <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                                                {{ $alert }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-3 lg:w-56 lg:flex-col">
                        <a
                            href="{{ route('staff.applications.show', $application) }}"
                            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white text-center"
                        >
                            Open Application
                        </a>

                        @if ($application->permit)
                            <a
                                href="{{ route('staff.permits.show', $application->permit) }}"
                                class="rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-900 text-center"
                            >
                                Open Permit
                            </a>
                        @endif

                        @if ($application->receipt)
                            <a
                                href="{{ route('staff.receipts.show', $application->receipt) }}"
                                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 text-center"
                            >
                                Open Receipt
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-white p-10 text-center shadow">
                <h2 class="text-lg font-semibold text-gray-900">No applications found</h2>
                <p class="mt-2 text-sm text-gray-500">Try a different passport number, traveler name, permit number, or receipt number.</p>
            </div>
        @endforelse
    </div>

    <div>
        {{ $applications->links() }}
    </div>
</div>