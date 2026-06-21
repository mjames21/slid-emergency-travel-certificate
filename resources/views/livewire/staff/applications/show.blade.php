{{-- FILE: resources/views/livewire/staff/applications/show.blade.php --}}
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Application</h1>
            <p class="text-sm text-gray-600">Record #{{ $application->id }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('staff.applications.index') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
            >
                Back to Applications
            </a>

            @if ($application->permit)
                <a
                    href="{{ route('staff.permits.show', $application->permit) }}"
                    class="rounded-lg border border-blue-300 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-900"
                >
                    Open Permit
                </a>
            @endif

            @if ($application->receipt)
                <a
                    href="{{ route('staff.receipts.show', $application->receipt) }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
                >
                    Open Receipt
                </a>
            @endif
        </div>
    </div>

    <div class="rounded-xl border border-amber-200 bg-amber-50 p-5">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-950">Officer Flags</h2>
                <p class="mt-1 text-sm text-gray-700">
                    These flags help officers check passport identity, flight details, contact information, and manual travel-rule review before a final decision.
                </p>
            </div>
            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700">
                {{ $officerFlags['score'] ?? 0 }}% complete
            </span>
        </div>

        <div class="mt-4 grid gap-3 lg:grid-cols-2">
            <div class="rounded-lg border border-white/70 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-900">Must Fix Before Approval</h3>
                    <span class="text-xs font-semibold text-gray-500">{{ count($officerFlags['required'] ?? []) }} open</span>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse (($officerFlags['required'] ?? []) as $flag)
                        <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-900">
                            <div class="font-semibold">{{ $flag['label'] }}</div>
                            <div class="mt-0.5 text-xs">{{ $flag['detail'] }}</div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900">
                            No blocking flags.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-white/70 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-sm font-semibold text-gray-900">Review Notes</h3>
                    <span class="text-xs font-semibold text-gray-500">{{ count($officerFlags['advisory'] ?? []) }} noted</span>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse (array_slice($officerFlags['advisory'] ?? [], 0, 3) as $flag)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                            <div class="font-semibold">{{ $flag['label'] }}</div>
                            <div class="mt-0.5">{{ $flag['detail'] }}</div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                            No review notes.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Application Summary</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-sm text-gray-500">Application ID</div>
                        <div class="font-medium text-gray-900">{{ $application->id }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Created At</div>
                        <div class="font-medium text-gray-900">
                            {{ optional($application->created_at)->format('Y-m-d H:i') ?: '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Airport</div>
                        <div class="font-medium text-gray-900">{{ $application->airport?->name ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Desk</div>
                        <div class="font-medium text-gray-900">{{ $application->desk?->name ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Purpose of Visit</div>
                        <div class="font-medium text-gray-900">{{ $application->purpose_of_visit ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Point of Entry</div>
                        <div class="font-medium text-gray-900">{{ $application->pointOfEntry?->name ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Arrival Date</div>
                        <div class="font-medium text-gray-900">{{ $application->arrival_date ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Period of Stay</div>
                        <div class="font-medium text-gray-900">
                            {{ $application->period_of_stay_text ?: ($application->period_of_stay_days ? $application->period_of_stay_days . ' DAYS' : '—') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Valid From</div>
                        <div class="font-medium text-gray-900">{{ $application->valid_from ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Valid Until</div>
                        <div class="font-medium text-gray-900">{{ $application->valid_until ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Flight Carrier</div>
                        <div class="font-medium text-gray-900">{{ $application->flight_carrier ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Flight Number</div>
                        <div class="font-medium text-gray-900">{{ $application->flight_number ?: '—' }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500">Flight Details</div>
                        <div class="font-medium text-gray-900">{{ $application->flight_details ?: '—' }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500">Destination Address</div>
                        <div class="font-medium text-gray-900">{{ $application->destination_address ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Host Name</div>
                        <div class="font-medium text-gray-900">{{ $application->host_name ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Host Address</div>
                        <div class="font-medium text-gray-900">{{ $application->host_address ?: '—' }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500">Migration & Assistance Observations</div>
                        <div class="font-medium text-gray-900">{{ $application->remarks ?: '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Traveler Details</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-sm text-gray-500">Surname</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->surname ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Given Names</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->given_names ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Full Name</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->full_name ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Passport Number</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->passport_number ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Nationality</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->nationality ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Sex</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->sex ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Date of Birth</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->date_of_birth ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Passport Expiry</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->passport_expiry_date ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Email</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->email ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Phone</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->phone ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Country of Birth</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->country_of_birth ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Country of Residence</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->country_of_residence ?: '—' }}</div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="text-sm text-gray-500">Occupation</div>
                        <div class="font-medium text-gray-900">{{ $application->passenger?->occupation ?: '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Outcome</h2>

                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <div class="text-gray-500">Permit</div>
                        <div class="font-medium text-gray-900">
                            @if ($application->permit)
                                <a href="{{ route('staff.permits.show', $application->permit) }}" class="underline underline-offset-2">
                                    {{ $application->permit->permit_no }}
                                </a>
                            @else
                                Not issued
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Receipt</div>
                        <div class="font-medium text-gray-900">
                            @if ($application->receipt)
                                <a href="{{ route('staff.receipts.show', $application->receipt) }}" class="underline underline-offset-2">
                                    {{ $application->receipt->receipt_no ?? ('Receipt #' . $application->receipt->id) }}
                                </a>
                            @else
                                Not issued
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-gray-500">Payment</div>
                        <div class="font-medium text-gray-900">{{ $application->payment?->status ?? '—' }}</div>
                    </div>
                </div>
            </div>

            @if (($passengerHistory['found'] ?? false) === true)
                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="text-lg font-semibold text-gray-900">Traveler History</h2>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Applications</div>
                            <div class="mt-1 text-xl font-bold text-gray-900">{{ $passengerHistory['counts']['applications'] ?? 0 }}</div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Permits</div>
                            <div class="mt-1 text-xl font-bold text-gray-900">{{ $passengerHistory['counts']['permits'] ?? 0 }}</div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Extensions</div>
                            <div class="mt-1 text-xl font-bold text-gray-900">{{ $passengerHistory['counts']['extensions'] ?? 0 }}</div>
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                            <div class="text-xs uppercase tracking-wide text-gray-500">Fraud Flags</div>
                            <div class="mt-1 text-xl font-bold text-gray-900">{{ $passengerHistory['counts']['fraud_flags'] ?? 0 }}</div>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <h3 class="text-sm font-semibold text-gray-900">Latest Record</h3>

                            <div class="mt-3 space-y-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Traveler:</span>
                                    <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['traveler_name'] ?? '—' }}</span>
                                </div>

                                <div>
                                    <span class="text-gray-500">Latest Permit:</span>
                                    <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['permit_no'] ?? '—' }}</span>
                                </div>

                                <div>
                                    <span class="text-gray-500">Latest Airport:</span>
                                    <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['airport_name'] ?? '—' }}</span>
                                </div>

                                <div>
                                    <span class="text-gray-500">Latest Valid Until:</span>
                                    <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['valid_until'] ?? '—' }}</span>
                                </div>

                                <div>
                                    <span class="text-gray-500">Latest Application Date:</span>
                                    <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['application_date'] ?? '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <h3 class="text-sm font-semibold text-gray-900">Risk Summary</h3>

                            <div class="mt-3 flex flex-wrap gap-2">
                                @if (($passengerHistory['is_repeat_traveler'] ?? false) === true)
                                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">
                                        Repeat Traveler
                                    </span>
                                @endif

                                @if (($passengerHistory['has_extension_history'] ?? false) === true)
                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                        Prior Extension
                                    </span>
                                @endif

                                @if (($passengerHistory['has_expired_permit_history'] ?? false) === true)
                                    <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800">
                                        Prior Expired Permit
                                    </span>
                                @endif

                                @if (($passengerHistory['has_fraud_history'] ?? false) === true)
                                    <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800">
                                        Fraud History
                                    </span>
                                @endif

                                @if (($passengerHistory['has_duplicate_passenger_records'] ?? false) === true)
                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                        Duplicate Passport Records
                                    </span>
                                @endif
                            </div>

                            @if (! empty($passengerHistory['alerts']))
                                <div class="mt-4 space-y-2">
                                    @foreach ($passengerHistory['alerts'] as $alert)
                                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                                            {{ $alert }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-gray-200 p-4">
                            <h3 class="text-sm font-semibold text-gray-900">Recent Permits</h3>

                            <div class="mt-3 space-y-2">
                                @forelse ($passengerHistory['recent_permits'] as $historyPermit)
                                    <div class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm">
                                        @if (! empty($historyPermit['id']))
                                            <a
                                                href="{{ route('staff.permits.show', $historyPermit['id']) }}"
                                                class="font-medium text-gray-900 underline underline-offset-2"
                                            >
                                                {{ $historyPermit['permit_no'] ?? '—' }}
                                            </a>
                                        @else
                                            <div class="font-medium text-gray-900">{{ $historyPermit['permit_no'] ?? '—' }}</div>
                                        @endif

                                        <div class="text-xs text-gray-600">
                                            Valid until {{ $historyPermit['valid_until'] ?? '—' }} ·
                                            {{ strtoupper($historyPermit['lifecycle_status'] ?? '—') }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">No prior permits.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-4">
                            <h3 class="text-sm font-semibold text-gray-900">Recent Extensions</h3>

                            <div class="mt-3 space-y-2">
                                @forelse ($passengerHistory['recent_extensions'] as $historyExtension)
                                    <div class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm">
                                        @if (! empty($historyExtension['id']))
                                            <a
                                                href="{{ route('staff.permit-extensions.show', $historyExtension['id']) }}"
                                                class="font-medium text-gray-900 underline underline-offset-2"
                                            >
                                                {{ $historyExtension['extension_no'] ?? '—' }}
                                            </a>
                                        @else
                                            <div class="font-medium text-gray-900">{{ $historyExtension['extension_no'] ?? '—' }}</div>
                                        @endif

                                        <div class="text-xs text-gray-600">
                                            {{ strtoupper($historyExtension['status'] ?? '—') }} ·
                                            New valid until {{ $historyExtension['requested_new_valid_until'] ?? '—' }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-sm text-gray-500">No prior extensions.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
