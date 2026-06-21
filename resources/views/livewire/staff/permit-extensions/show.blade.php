{{-- FILE: resources/views/livewire/staff/permit-extensions/show.blade.php --}}
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Permit Extension</h1>
            <p class="text-sm text-gray-600">{{ $permitExtension->extension_no }}</p>
        </div>

        <div class="flex gap-3">
            @if ($permitExtension->newPermit)
                <a
                    href="{{ route('staff.permits.show', $permitExtension->newPermit) }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
                >
                    View New Permit
                </a>
            @endif

            <a
                href="{{ route('staff.permit-extensions.index') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
            >
                Back to Extensions
            </a>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <div class="text-sm text-gray-500">Status</div>
                <div class="font-medium text-gray-900">{{ strtoupper($permitExtension->status) }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Extension Number</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->extension_no }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Original Permit</div>
                <div class="font-medium text-gray-900">
                    @if ($permitExtension->originalPermit)
                        <a href="{{ route('staff.permits.show', $permitExtension->originalPermit) }}" class="underline underline-offset-2">
                            {{ $permitExtension->originalPermit->permit_no }}
                        </a>
                    @else
                        —
                    @endif
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">New Permit</div>
                <div class="font-medium text-gray-900">
                    @if ($permitExtension->newPermit)
                        <a href="{{ route('staff.permits.show', $permitExtension->newPermit) }}" class="underline underline-offset-2">
                            {{ $permitExtension->newPermit->permit_no }}
                        </a>
                    @else
                        Not issued
                    @endif
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Traveler</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->passenger?->full_name ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Passport Number</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->passenger?->passport_number ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Current Valid Until</div>
                <div class="font-medium text-gray-900">{{ optional($permitExtension->current_valid_until)->format('Y-m-d') ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Requested New Valid Until</div>
                <div class="font-medium text-gray-900">{{ optional($permitExtension->requested_new_valid_until)->format('Y-m-d') ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Extra Days Requested</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->requested_extra_days }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Fee Waived</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->is_fee_waived ? 'Yes' : 'No' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Fee Amount</div>
                <div class="font-medium text-gray-900">{{ number_format((float) $permitExtension->fee_amount, 2) }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Requested By</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->requester?->name ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Requested At</div>
                <div class="font-medium text-gray-900">{{ optional($permitExtension->requested_at)->format('Y-m-d H:i') ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Approved By</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->approver?->name ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Approved At</div>
                <div class="font-medium text-gray-900">{{ optional($permitExtension->approved_at)->format('Y-m-d H:i') ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Rejected By</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->rejector?->name ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Rejected At</div>
                <div class="font-medium text-gray-900">{{ optional($permitExtension->rejected_at)->format('Y-m-d H:i') ?: '—' }}</div>
            </div>

            <div>
                <div class="text-sm text-gray-500">Reason Code</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->reason_code ?: '—' }}</div>
            </div>

            <div class="md:col-span-2">
                <div class="text-sm text-gray-500">Reason</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->reason }}</div>
            </div>

            <div class="md:col-span-2">
                <div class="text-sm text-gray-500">Decision Note</div>
                <div class="font-medium text-gray-900">{{ $permitExtension->decision_note ?: '—' }}</div>
            </div>
        </div>
    </div>

    @if ($permitExtension->status === 'pending' && $canReview)
        <div class="rounded-xl bg-white p-6 shadow space-y-6">
            <h2 class="text-lg font-semibold text-gray-900">Supervisor Review</h2>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Decision Note</label>
                <textarea
                    wire:model.defer="decision_note"
                    rows="4"
                    class="w-full rounded-lg border-gray-300 shadow-sm"
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">Add an approval or rejection note for the audit trail.</p>
                @error('decision_note')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    wire:click="reject"
                    class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700"
                >
                    Reject Extension
                </button>

                <button
                    type="button"
                    wire:click="approve"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Approve Extension
                </button>
            </div>
        </div>
    @endif
</div>