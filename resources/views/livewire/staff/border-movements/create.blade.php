<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Record Border Movement</h1>
            <p class="mt-1 text-sm text-gray-600">Screen a traveler and record an immigration entry, exit, refusal, or referral decision.</p>
        </div>

        <a href="{{ route('staff.border-movements.index') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
            Movement Register
        </a>
    </div>

    @if ($notice)
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ $notice }}</div>
    @endif

    @if ($error)
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $error }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm font-medium text-gray-700">Permit Number</label>
                    <div class="mt-1 flex gap-2">
                        <input wire:model="permit_no" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="SVA-FNA-20260614-0001">
                        <button wire:click="searchPermit" type="button" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Load</button>
                    </div>
                    @error('permit_no') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Movement Type</label>
                    <select wire:model.live="movement_type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="entry">Entry</option>
                        <option value="exit">Exit</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Officer Decision</label>
                    <select wire:model="decision" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="admitted">Admitted</option>
                        <option value="departed">Departed</option>
                        <option value="refused">Refused</option>
                        <option value="referred">Referred</option>
                    </select>
                    @error('decision') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Operational Standard</label>
                    <div class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                        ICAO document checks, IATA movement context, IOM migration-management referral cues
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <label class="text-sm font-medium text-gray-700">Officer Notes</label>
                <textarea wire:model="notes" rows="4" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="Record inspection notes, exceptions, referral reason, protection concern, or supervisor instruction."></textarea>
                @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
            </div>

            @if ($requiresSupervisorOverride)
                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4">
                    <div class="text-sm font-semibold text-amber-900">Supervisor Override Required</div>
                    <p class="mt-1 text-sm text-amber-800">The screening result is not CLEAR. Admission or departure can be recorded only after supervisor review is confirmed and a reason is entered.</p>

                    <label class="mt-3 flex items-start gap-2 text-sm text-amber-900">
                        <input wire:model="supervisor_override_confirmed" type="checkbox" class="mt-1 rounded border-amber-300 text-emerald-700 focus:ring-emerald-600">
                        <span>Supervisor reviewed and authorized this movement decision.</span>
                    </label>

                    <div class="mt-3">
                        <label class="text-sm font-medium text-amber-900">Override Reason</label>
                        <textarea wire:model="supervisor_override_reason" rows="3" class="mt-1 w-full rounded-md border-amber-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="Record supervisor instruction, operational justification, and reference number if available."></textarea>
                    </div>
                </div>
            @endif

            <div class="mt-5 flex flex-wrap gap-3">
                <button wire:click="runScreening" type="button" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
                    Run Screening
                </button>
                <button wire:click="recordMovement" type="button" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">
                    Record Movement
                </button>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">Traveler</h2>
                @if ($permit)
                    <dl class="mt-4 space-y-3 text-sm">
                        <div>
                            <dt class="text-gray-500">Name</dt>
                            <dd class="font-medium text-gray-900">{{ $permit->visaApplication?->passenger?->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Passport</dt>
                            <dd class="font-medium text-gray-900">{{ $permit->visaApplication?->passenger?->passport_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Nationality</dt>
                            <dd class="font-medium text-gray-900">{{ $permit->visaApplication?->passenger?->nationality_code ?: $permit->visaApplication?->passenger?->nationality }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Permit Valid Until</dt>
                            <dd class="font-medium text-gray-900">{{ $permit->valid_until?->format('Y-m-d') ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Airport / Point of Entry</dt>
                            <dd class="font-medium text-gray-900">{{ $permit->visaApplication?->airport?->name ?: '—' }} / {{ $permit->visaApplication?->pointOfEntry?->name ?: $permit->visaApplication?->point_of_entry ?: '—' }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="mt-3 text-sm text-gray-500">Load a permit to see traveler and permit details.</p>
                @endif
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-semibold text-gray-900">Screening Result</h2>
                @if ($screening)
                    <div class="mt-4 flex gap-2">
                        <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ strtoupper($screening->status) }}</span>
                        <span class="rounded-full {{ $screening->risk_level === 'high' ? 'bg-red-100 text-red-700' : ($screening->risk_level === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }} px-2 py-1 text-xs font-semibold">{{ strtoupper($screening->risk_level) }}</span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div><dt class="text-gray-500">Passport</dt><dd class="font-medium">{{ $screening->passport_valid ? 'Valid' : 'Review' }}</dd></div>
                        <div><dt class="text-gray-500">Permit</dt><dd class="font-medium">{{ $screening->permit_valid ? 'Valid' : 'Review' }}</dd></div>
                        <div><dt class="text-gray-500">MRZ</dt><dd class="font-medium">{{ $screening->mrz_verified ? 'Verified' : 'Manual' }}</dd></div>
                        <div><dt class="text-gray-500">History</dt><dd class="font-medium">{{ $screening->traveler_history_reviewed ? 'Reviewed' : 'Pending' }}</dd></div>
                    </dl>

                    @if ($screening->reasons)
                        <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-amber-800">Reasons</div>
                            <ul class="mt-2 list-disc space-y-1 pl-4 text-sm text-amber-900">
                                @foreach ($screening->reasons as $reason)
                                    <li>{{ $reason }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @else
                    <div class="mt-3 rounded-md border border-gray-200 bg-gray-50 p-3 text-sm text-gray-600">
                        Screening is required before the movement can be recorded.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
