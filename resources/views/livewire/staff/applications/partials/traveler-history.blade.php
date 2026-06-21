@if ($passport_number !== '')
    <details class="group overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-4 py-3 marker:hidden [&::-webkit-details-marker]:hidden">
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-gray-900">Traveler History</h3>
                <p class="truncate text-xs text-gray-500">
                    Passport {{ $passport_number }} -
                    {{ ($passengerHistory['found'] ?? false) === true ? 'prior records available' : 'no prior record found' }}
                </p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                @if (($passengerHistory['found'] ?? false) === true)
                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">
                        Match Found
                    </span>
                @else
                    <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700">
                        No Prior History
                    </span>
                @endif

                <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 group-open:hidden">
                    View
                </span>

                <span class="hidden rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-xs font-semibold text-gray-700 group-open:inline-flex">
                    Hide
                </span>
            </div>
        </summary>

        <div class="border-t border-gray-200 p-4">
        @if (($passengerHistory['found'] ?? false) === true)
            <div class="grid gap-4 md:grid-cols-4">
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

            @if (! empty($passengerHistory['alerts']))
                <div class="mt-4 space-y-2">
                    @foreach ($passengerHistory['alerts'] as $alert)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            {{ $alert }}
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="text-sm font-semibold text-gray-900">Latest Record</h4>

                    <div class="mt-3 space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Traveler:</span>
                            <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['traveler_name'] ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Latest Permit:</span>
                            <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['permit_no'] ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Valid Until:</span>
                            <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['valid_until'] ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Airport:</span>
                            <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['airport_name'] ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Latest Application Date:</span>
                            <span class="font-medium text-gray-900">{{ $passengerHistory['latest']['application_date'] ?? '-' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Lifecycle Status:</span>
                            <span class="font-medium text-gray-900">{{ strtoupper($passengerHistory['latest']['lifecycle_status'] ?? '-') }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h4 class="text-sm font-semibold text-gray-900">Risk Summary</h4>

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

                    <div class="mt-4 space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Active Permits:</span>
                            <span class="font-medium text-gray-900">{{ $passengerHistory['counts']['active_permits'] ?? 0 }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Expired Permits:</span>
                            <span class="font-medium text-gray-900">{{ $passengerHistory['counts']['expired_permits'] ?? 0 }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Passenger Records:</span>
                            <span class="font-medium text-gray-900">{{ $passengerHistory['counts']['passengers'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if (! empty($passengerHistory['recent_permits']) || ! empty($passengerHistory['recent_extensions']))
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <h4 class="text-sm font-semibold text-gray-900">Recent Permits</h4>

                        <div class="mt-3 space-y-2">
                            @forelse ($passengerHistory['recent_permits'] as $historyPermit)
                                <div class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm">
                                    @if (! empty($historyPermit['id']))
                                        <a
                                            href="{{ route('staff.permits.show', $historyPermit['id']) }}"
                                            class="font-medium text-gray-900 underline underline-offset-2"
                                        >
                                            {{ $historyPermit['permit_no'] ?? '-' }}
                                        </a>
                                    @else
                                        <div class="font-medium text-gray-900">{{ $historyPermit['permit_no'] ?? '-' }}</div>
                                    @endif
                                    <div class="text-xs text-gray-600">
                                        Valid until {{ $historyPermit['valid_until'] ?? '-' }} -
                                        {{ strtoupper($historyPermit['lifecycle_status'] ?? '-') }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">No prior permits.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-200 p-4">
                        <h4 class="text-sm font-semibold text-gray-900">Recent Extensions</h4>

                        <div class="mt-3 space-y-2">
                            @forelse ($passengerHistory['recent_extensions'] as $historyExtension)
                                <div class="rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 text-sm">
                                    @if (! empty($historyExtension['id']))
                                        <a
                                            href="{{ route('staff.permit-extensions.show', $historyExtension['id']) }}"
                                            class="font-medium text-gray-900 underline underline-offset-2"
                                        >
                                            {{ $historyExtension['extension_no'] ?? '-' }}
                                        </a>
                                    @else
                                        <div class="font-medium text-gray-900">{{ $historyExtension['extension_no'] ?? '-' }}</div>
                                    @endif
                                    <div class="text-xs text-gray-600">
                                        {{ strtoupper($historyExtension['status'] ?? '-') }} -
                                        New valid until {{ $historyExtension['requested_new_valid_until'] ?? '-' }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500">No prior extensions.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                No prior traveler history found for this passport number.
            </div>
        @endif
        </div>
    </details>
@endif
