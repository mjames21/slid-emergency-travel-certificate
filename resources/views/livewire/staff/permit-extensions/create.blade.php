{{-- FILE: resources/views/livewire/staff/permit-extensions/create.blade.php --}}
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-bold text-gray-900">New Permit Extension Request</h1>
        <p class="text-sm text-gray-600">
            Search by permit number, review the traveler, then submit the extension request.
        </p>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="grid gap-4 md:grid-cols-[1fr_auto]">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Permit Number</label>
                <input
                    type="text"
                    wire:model.defer="permit_no"
                    class="w-full rounded-lg border-gray-300 shadow-sm"
                    placeholder="Enter permit number"
                >
                @error('permit_no')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-end">
                <button
                    type="button"
                    wire:click="searchPermit"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Search Permit
                </button>
            </div>
        </div>

        @if ($eligibility_message)
            <div class="mt-4 rounded-lg px-4 py-3 text-sm {{ $eligible ? 'border border-blue-200 bg-blue-50 text-blue-800' : 'border border-red-200 bg-red-50 text-red-800' }}">
                {{ $eligibility_message }}
            </div>
        @endif
    </div>

    @if ($permit)
        <div class="rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Permit Summary</h2>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm text-gray-500">Permit Number</div>
                    <div class="font-medium text-gray-900">{{ $permit->permit_no }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Lifecycle Status</div>
                    <div class="font-medium text-gray-900">{{ $permit->lifecycle_status ?? $permit->permit_status ?? 'active' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Traveler</div>
                    <div class="font-medium text-gray-900">{{ $permit->visaApplication->passenger->full_name }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Passport Number</div>
                    <div class="font-medium text-gray-900">{{ $permit->visaApplication->passenger->passport_number }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Current Valid Until</div>
                    <div class="font-medium text-gray-900">{{ optional($permit->valid_until)->format('Y-m-d') ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Issued By</div>
                    <div class="font-medium text-gray-900">{{ $permit->issuer?->name ?: '—' }}</div>
                </div>
            </div>
        </div>
    @endif

    @if (($travelerHistory['found'] ?? false) === true)
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Traveler History</h2>
                    <p class="text-sm text-gray-500">
                        Prior records for passport {{ $travelerHistory['passport_number'] ?? '—' }}.
                    </p>
                </div>

                @if (($travelerHistory['is_repeat_traveler'] ?? false) === true)
                    <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">
                        Repeat Traveler
                    </span>
                @endif
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-gray-500">Applications</div>
                    <div class="mt-1 text-xl font-bold text-gray-900">{{ $travelerHistory['counts']['applications'] ?? 0 }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-gray-500">Permits</div>
                    <div class="mt-1 text-xl font-bold text-gray-900">{{ $travelerHistory['counts']['permits'] ?? 0 }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-gray-500">Extensions</div>
                    <div class="mt-1 text-xl font-bold text-gray-900">{{ $travelerHistory['counts']['extensions'] ?? 0 }}</div>
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 p-3">
                    <div class="text-xs uppercase tracking-wide text-gray-500">Fraud Flags</div>
                    <div class="mt-1 text-xl font-bold text-gray-900">{{ $travelerHistory['counts']['fraud_flags'] ?? 0 }}</div>
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Latest Record</h3>

                    <div class="mt-3 space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Traveler:</span>
                            <span class="font-medium text-gray-900">{{ $travelerHistory['latest']['traveler_name'] ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Latest Permit:</span>
                            <span class="font-medium text-gray-900">{{ $travelerHistory['latest']['permit_no'] ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Latest Airport:</span>
                            <span class="font-medium text-gray-900">{{ $travelerHistory['latest']['airport_name'] ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Latest Valid Until:</span>
                            <span class="font-medium text-gray-900">{{ $travelerHistory['latest']['valid_until'] ?? '—' }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Lifecycle Status:</span>
                            <span class="font-medium text-gray-900">{{ strtoupper($travelerHistory['latest']['lifecycle_status'] ?? '—') }}</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Risk Summary</h3>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @if (($travelerHistory['has_extension_history'] ?? false) === true)
                            <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                Prior Extension
                            </span>
                        @endif

                        @if (($travelerHistory['has_expired_permit_history'] ?? false) === true)
                            <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800">
                                Prior Expired Permit
                            </span>
                        @endif

                        @if (($travelerHistory['has_fraud_history'] ?? false) === true)
                            <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-800">
                                Fraud History
                            </span>
                        @endif

                        @if (($travelerHistory['has_duplicate_passenger_records'] ?? false) === true)
                            <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                                Duplicate Passport Records
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2 text-sm">
                        <div>
                            <span class="text-gray-500">Active Permits:</span>
                            <span class="font-medium text-gray-900">{{ $travelerHistory['counts']['active_permits'] ?? 0 }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Expired Permits:</span>
                            <span class="font-medium text-gray-900">{{ $travelerHistory['counts']['expired_permits'] ?? 0 }}</span>
                        </div>

                        <div>
                            <span class="text-gray-500">Passenger Records:</span>
                            <span class="font-medium text-gray-900">{{ $travelerHistory['counts']['passengers'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if (! empty($travelerHistory['alerts']))
                <div class="mt-4 space-y-2">
                    @foreach ($travelerHistory['alerts'] as $alert)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                            {{ $alert }}
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Recent Permits</h3>

                    <div class="mt-3 space-y-2">
                        @forelse ($travelerHistory['recent_permits'] as $historyPermit)
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

                                @if (! empty($historyPermit['issuer_name']))
                                    <div class="mt-1 text-xs text-gray-500">
                                        Issued by {{ $historyPermit['issuer_name'] }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No prior permits.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <h3 class="text-sm font-semibold text-gray-900">Recent Extensions</h3>

                    <div class="mt-3 space-y-2">
                        @forelse ($travelerHistory['recent_extensions'] as $historyExtension)
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

    @if ($permit && $eligible)
        <form wire:submit="submit" class="space-y-6 rounded-xl bg-white p-6 shadow">
            <h2 class="text-lg font-semibold text-gray-900">Extension Request</h2>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Extra Days Requested</label>
                    <input
                        type="number"
                        min="1"
                        max="365"
                        wire:model.live="requested_extra_days"
                        class="w-full rounded-lg border-gray-300 shadow-sm"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Number of extra days requested beyond the current permit validity.
                    </p>
                    @error('requested_extra_days')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Current Valid Until</label>
                    <input
                        type="text"
                        value="{{ $current_valid_until }}"
                        readonly
                        class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Requested New Valid Until</label>
                    <input
                        type="text"
                        value="{{ $requested_new_valid_until }}"
                        readonly
                        class="w-full rounded-lg border-gray-300 bg-gray-50 shadow-sm"
                    >
                    <p class="mt-1 text-xs text-gray-500">
                        Auto-calculated from the current valid-until date and extra days requested.
                    </p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Reason Code</label>
                    <input
                        type="text"
                        wire:model.defer="reason_code"
                        class="w-full rounded-lg border-gray-300 shadow-sm"
                        placeholder="Optional code"
                    >
                    @error('reason_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Fee Amount</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        wire:model.defer="fee_amount"
                        class="w-full rounded-lg border-gray-300 shadow-sm"
                    >
                    @error('fee_amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Reason</label>
                    <textarea
                        wire:model.defer="reason"
                        rows="4"
                        class="w-full rounded-lg border-gray-300 shadow-sm"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Explain why the traveler is requesting an extension.
                    </p>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="inline-flex items-center gap-2">
                        <input
                            type="checkbox"
                            wire:model.defer="is_fee_waived"
                            class="rounded border-gray-300 text-gray-900 shadow-sm"
                        >
                        <span class="text-sm font-medium text-gray-700">Fee Waived / Gratis</span>
                    </label>
                    @error('is_fee_waived')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-gray-700">Decision Note</label>
                    <textarea
                        wire:model.defer="decision_note"
                        rows="3"
                        class="w-full rounded-lg border-gray-300 shadow-sm"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Optional operational note for the supervisor or audit trail.
                    </p>
                    @error('decision_note')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Submit Extension Request
                </button>
            </div>
        </form>
    @endif
</div>
