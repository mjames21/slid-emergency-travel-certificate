// FILE: resources/views/livewire/hq/expiry-report.blade.php
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">HQ Expiry Report</h1>
            <p class="text-sm text-gray-600">Filter and review expiring or expired active permits.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <form method="GET" action="{{ route('hq.reports.permit-expiry.csv') }}">
                <input type="hidden" name="scope" value="{{ $scope }}">
                <input type="hidden" name="start_date" value="{{ $start_date }}">
                <input type="hidden" name="end_date" value="{{ $end_date }}">
                <input type="hidden" name="airport_id" value="{{ $airport_id }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">
                    Export Current View CSV
                </button>
            </form>

            <form method="GET" action="{{ route('hq.reports.permit-expiry.xls') }}">
                <input type="hidden" name="scope" value="{{ $scope }}">
                <input type="hidden" name="start_date" value="{{ $start_date }}">
                <input type="hidden" name="end_date" value="{{ $end_date }}">
                <input type="hidden" name="airport_id" value="{{ $airport_id }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <button type="submit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700">
                    Export Current View Excel
                </button>
            </form>

            <a href="{{ route('hq.dashboard') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Back to HQ Dashboard
            </a>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Expiring Within 7 Days</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $expiringSoonCount }}</div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Expired Active Permits</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $expiredCount }}</div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="grid gap-4 md:grid-cols-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Scope</label>
                <select wire:model.live="scope" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="expiring_soon">Expiring Soon</option>
                    <option value="expired_active">Expired Active</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Airport</label>
                <select wire:model.live="airport_id" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">All Airports</option>
                    @foreach ($airports as $airport)
                        <option value="{{ $airport->id }}">{{ $airport->name }} ({{ $airport->code }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" wire:model.live="start_date" class="w-full rounded-lg border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" wire:model.live="end_date" class="w-full rounded-lg border-gray-300 shadow-sm">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-lg border-gray-300 shadow-sm"
                    placeholder="Permit, traveler, passport"
                >
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-3 py-3">
                            <button type="button" wire:click="sortBy('permit_no')" class="font-semibold">
                                Permit
                            </button>
                        </th>
                        <th class="px-3 py-3">Traveler</th>
                        <th class="px-3 py-3">Passport</th>
                        <th class="px-3 py-3">Airport</th>
                        <th class="px-3 py-3">
                            <button type="button" wire:click="sortBy('valid_until')" class="font-semibold">
                                Valid Until
                            </button>
                        </th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Issuer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-700">
                    @forelse ($permits as $permit)
                        @php
                            $validUntil = optional($permit->valid_until);
                            $isExpired = $validUntil->isBefore(today());
                            $statusClasses = $isExpired
                                ? 'bg-red-100 text-red-800 border-red-200'
                                : 'bg-amber-100 text-amber-800 border-amber-200';
                        @endphp
                        <tr wire:key="hq-expiry-permit-{{ $permit->id }}">
                            <td class="px-3 py-3 font-medium text-gray-900">{{ $permit->permit_no }}</td>
                            <td class="px-3 py-3">{{ $permit->visaApplication?->passenger?->full_name ?: '—' }}</td>
                            <td class="px-3 py-3">{{ $permit->visaApplication?->passenger?->passport_number ?: '—' }}</td>
                            <td class="px-3 py-3">{{ $permit->visaApplication?->airport?->name ?: '—' }}</td>
                            <td class="px-3 py-3">{{ $validUntil->format('Y-m-d') ?: '—' }}</td>
                            <td class="px-3 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ $isExpired ? 'EXPIRED' : 'EXPIRING' }}
                                </span>
                            </td>
                            <td class="px-3 py-3">{{ $permit->issuer?->name ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">
                                No permits found for the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $permits->links() }}
        </div>
    </div>
</div>