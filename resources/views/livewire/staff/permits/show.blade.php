{{-- FILE: resources/views/livewire/staff/permits/show.blade.php --}}
<div class="space-y-6">
    @php
        $borderManagementEnabled = (bool) config('features.border_management');
    @endphp

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($linkedPermitNotice)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            {{ $linkedPermitNotice }}

            @if ($latestLinkedPermit)
                <a href="{{ route('staff.permits.show', $latestLinkedPermit) }}" class="font-semibold underline underline-offset-2">
                    Open latest permit
                </a>
            @endif
        </div>
    @endif

    @if ($pendingExtension)
        <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            This permit has a pending extension request.
            <a href="{{ route('staff.permit-extensions.show', $pendingExtension) }}" class="font-semibold underline underline-offset-2">
                Review extension
            </a>
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Permit</h1>
            <p class="text-sm text-gray-600">{{ $permit->permit_no }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            @if ($borderManagementEnabled)
                <a href="{{ route('staff.border-movements.create', ['permit_no' => $permit->permit_no]) }}"
                   class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white">
                    Record Movement
                </a>
            @endif

            <a href="{{ route('documents.permits.show', $permit) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Print Permit
            </a>

            <button wire:click="emailPermit"
                    type="button"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">
                Send Official Email
            </button>

            <a href="{{ route('verify.permit', $permit->verification_code) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">
                Verify Publicly
            </a>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow lg:col-span-2">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm text-gray-500">Permit Number</div>
                    <div class="font-medium text-gray-900">{{ $permit->permit_no }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Status</div>
                    <div class="font-medium text-gray-900">{{ $permit->status->value }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Lifecycle Status</div>
                    <div class="font-medium text-gray-900">{{ strtoupper($permitLifecycleStatus) }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Verification Code</div>
                    <div class="font-medium text-gray-900">{{ $permit->verification_code }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Security Seal</div>
                    <div class="break-all font-medium text-gray-900">{{ $permit->security_seal ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Visa ID</div>
                    <div class="font-medium text-gray-900">{{ $permit->visa_id ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">MRZ Line 1</div>
                    <div class="font-mono text-sm text-gray-900">{{ $permit->mrz_line_1 ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">MRZ Line 2</div>
                    <div class="font-mono text-sm text-gray-900">{{ $permit->mrz_line_2 ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Valid From</div>
                    <div class="font-medium text-gray-900">{{ optional($permit->valid_from)->format('Y-m-d') ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Valid Until</div>
                    <div class="font-medium text-gray-900">{{ optional($permit->valid_until)->format('Y-m-d') ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Issued By</div>
                    <div class="font-medium text-gray-900">{{ $permit->issuer?->name ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Checked By</div>
                    <div class="font-medium text-gray-900">{{ $permit->checker?->name ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Traveler</div>
                    <div class="font-medium text-gray-900">{{ $permit->visaApplication->passenger->full_name }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Passport Number</div>
                    <div class="font-medium text-gray-900">{{ $permit->visaApplication->passenger->passport_number }}</div>
                </div>
            </div>

            <div class="mt-8 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                Traveler verification notice: verify before leaving the immigration desk. A permit that cannot be verified on the official system should not be accepted as valid.
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Virtual Visa</h2>

                <div class="mt-4 space-y-3 text-sm">
                    <div>
                        <div class="text-gray-500">Permit Number</div>
                        <div class="font-medium text-gray-900">{{ $virtualVisa['permit_no'] }}</div>
                    </div>

                    <div>
                        <div class="text-gray-500">Verification URL</div>
                        <div class="break-all font-medium text-gray-900">{{ $virtualVisa['verification_url'] }}</div>
                    </div>

                    <div>
                        <div class="text-gray-500">Receipt Number</div>
                        <div class="font-medium text-gray-900">{{ $virtualVisa['receipt_no'] ?: '—' }}</div>
                    </div>

                    <div>
                        <div class="text-gray-500">Payment Basis</div>
                        <div class="font-medium text-gray-900">{{ $virtualVisa['payment_basis'] }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Print Tracking</h2>

                <div class="mt-4 space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">Print Count</div>
                        <div class="font-medium text-gray-900">{{ $permit->print_count }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Last Printed</div>
                        <div class="font-medium text-gray-900">{{ optional($permit->last_printed_at)->format('Y-m-d H:i') ?: '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Recent Verification</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($permit->verifications->take(5) as $verification)
                        <div class="rounded-lg border border-gray-200 px-3 py-2">
                            <div class="text-sm font-medium text-gray-900">{{ $verification->result->value }}</div>
                            <div class="text-xs text-gray-500">{{ optional($verification->verified_at)->format('Y-m-d H:i:s') }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">No verification history.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Fraud Flags</h2>

                <div class="mt-4 space-y-3">
                    @forelse ($permit->fraudFlags as $flag)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
                            <div class="text-sm font-medium text-amber-900">{{ $flag->flag_type }}</div>
                            <div class="text-xs text-amber-700">{{ $flag->description }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-500">No fraud flags.</div>
                    @endforelse
                </div>
            </div>

            @if (($travelerHistory['found'] ?? false) === true)
                <div class="rounded-xl bg-white p-6 shadow">
                    <h2 class="text-lg font-semibold text-gray-900">Traveler History</h2>

                    <div class="mt-4 grid gap-3 md:grid-cols-2">
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

                    <div class="mt-4 space-y-2 text-sm">
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
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if (($travelerHistory['is_repeat_traveler'] ?? false) === true)
                            <span class="inline-flex rounded-full border border-blue-200 bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800">
                                Repeat Traveler
                            </span>
                        @endif

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

                    @if (! empty($travelerHistory['alerts']))
                        <div class="mt-4 space-y-2">
                            @foreach ($travelerHistory['alerts'] as $alert)
                                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                                    {{ $alert }}
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (! empty($travelerHistory['recent_permits']))
                        <div class="mt-4">
                            <h3 class="text-sm font-semibold text-gray-900">Recent Permits</h3>

                            <div class="mt-2 space-y-2">
                                @foreach ($travelerHistory['recent_permits'] as $historyPermit)
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
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
