<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Border Movements</h1>
            <p class="mt-1 text-sm text-gray-600">Entry, exit, refusal, and referral records for the Sierra Leone Immigration Department.</p>
        </div>

        <a href="{{ route('staff.border-movements.create') }}" class="inline-flex items-center justify-center rounded-md bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">
            Record Movement
        </a>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">Search</label>
                <input wire:model.live.debounce.300ms="search" type="search" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="Movement ref, permit, passport, traveler">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Movement</label>
                <select wire:model.live="movement_type" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">All</option>
                    <option value="entry">Entry</option>
                    <option value="exit">Exit</option>
                </select>
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Decision</label>
                <select wire:model.live="decision" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">All</option>
                    <option value="admitted">Admitted</option>
                    <option value="departed">Departed</option>
                    <option value="refused">Refused</option>
                    <option value="referred">Referred</option>
                </select>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Entries Today</div>
            <div class="mt-2 text-2xl font-bold text-emerald-800">{{ $entriesTodayCount }}</div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Exits Today</div>
            <div class="mt-2 text-2xl font-bold text-emerald-800">{{ $exitsTodayCount }}</div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Refused / Referred</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">{{ $referralsTodayCount }}</div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Overrides Today</div>
            <div class="mt-2 text-2xl font-bold text-red-700">{{ $overridesTodayCount }}</div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Traveler</th>
                        <th class="px-4 py-3">Permit</th>
                        <th class="px-4 py-3">Movement</th>
                        <th class="px-4 py-3">Decision</th>
                        <th class="px-4 py-3">Risk</th>
                        <th class="px-4 py-3">Override</th>
                        <th class="px-4 py-3">Location</th>
                        <th class="px-4 py-3">Officer</th>
                        <th class="px-4 py-3">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($movements as $movement)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $movement->movement_reference }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $movement->passenger?->full_name ?: 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">{{ $movement->passport_number }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $movement->permit?->permit_no ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ strtoupper($movement->movement_type) }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ strtoupper($movement->decision) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full {{ $movement->risk_level === 'high' ? 'bg-red-100 text-red-700' : ($movement->risk_level === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }} px-2 py-1 text-xs font-semibold">
                                    {{ strtoupper($movement->risk_level) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($movement->is_supervisor_override)
                                    <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-semibold text-red-700">YES</span>
                                @else
                                    <span class="text-xs text-gray-400">NO</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $movement->airport?->code ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $movement->officer?->name ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $movement->occurred_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-gray-500">No border movements recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-4 py-3">
            {{ $movements->links() }}
        </div>
    </div>
</div>
