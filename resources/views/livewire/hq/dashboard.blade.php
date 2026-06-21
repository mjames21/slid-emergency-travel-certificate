{{-- FILE: resources/views/livewire/hq/dashboard.blade.php --}}
<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">HQ Dashboard</h1>
            <p class="text-sm text-gray-600">Headquarters expiry and overstay monitoring.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a
                href="{{ route('hq.reports.permit-expiry') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
            >
                Open Expiry Report
            </a>

            <a
                href="{{ route('hq.reports.permit-expiry.csv', ['scope' => 'expiring_soon', 'days' => 7]) }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
            >
                Export Expiring CSV
            </a>

            <a
                href="{{ route('hq.reports.permit-expiry.xls', ['scope' => 'expiring_soon', 'days' => 7]) }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
            >
                Export Expiring Excel
            </a>

            <a
                href="{{ route('hq.reports.permit-expiry.csv', ['scope' => 'expired_active']) }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
            >
                Export Expired CSV
            </a>

            <a
                href="{{ route('hq.reports.permit-expiry.xls', ['scope' => 'expired_active']) }}"
                class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
            >
                Export Expired Excel
            </a>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <h2 class="text-lg font-semibold text-gray-900">Custom Export</h2>
        <p class="mt-1 text-sm text-gray-500">Export permits by a custom valid-until date range.</p>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <form method="GET" action="{{ route('hq.reports.permit-expiry.csv') }}" class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Scope</label>
                    <select name="scope" class="w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="expiring_soon">Expiring / Range</option>
                        <option value="expired_active">Expired Active</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
                    >
                        Export CSV
                    </button>
                </div>
            </form>

            <form method="GET" action="{{ route('hq.reports.permit-expiry.xls') }}" class="grid gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Scope</label>
                    <select name="scope" class="w-full rounded-lg border-gray-300 shadow-sm">
                        <option value="expiring_soon">Expiring / Range</option>
                        <option value="expired_active">Expired Active</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Start Date</label>
                    <input type="date" name="start_date" class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" class="w-full rounded-lg border-gray-300 shadow-sm">
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                    >
                        Export Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Expiring Within 7 Days</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $expiringSoonPermitsCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Active permits nearing expiry across all airports.</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Expired Active Permits</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $expiredPermitsCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Permits already past valid-until and still marked active.</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Expiring Soon by Airport</h2>
                    <p class="text-sm text-gray-500">Active permits expiring in the next 7 days.</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-3">Airport</th>
                            <th class="px-3 py-3">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-700">
                        @forelse ($expiringSoonByAirport as $airportName => $count)
                            <tr>
                                <td class="px-3 py-3 font-medium text-gray-900">{{ $airportName }}</td>
                                <td class="px-3 py-3">{{ $count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">
                                    No expiring permits found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Expired Active Permits by Airport</h2>
                    <p class="text-sm text-gray-500">Potential overstay attention queue.</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-3">Airport</th>
                            <th class="px-3 py-3">Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-700">
                        @forelse ($expiredByAirport as $airportName => $count)
                            <tr>
                                <td class="px-3 py-3 font-medium text-gray-900">{{ $airportName }}</td>
                                <td class="px-3 py-3">{{ $count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-3 py-6 text-center text-sm text-gray-500">
                                    No expired active permits found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Expiring Within 7 Days</h2>
                    <p class="text-sm text-gray-500">Latest permits nearing expiry.</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-3">Permit</th>
                            <th class="px-3 py-3">Traveler</th>
                            <th class="px-3 py-3">Airport</th>
                            <th class="px-3 py-3">Valid Until</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-700">
                        @forelse ($expiringSoonPermits as $permit)
                            <tr>
                                <td class="px-3 py-3 font-medium text-gray-900">{{ $permit->permit_no }}</td>
                                <td class="px-3 py-3">{{ $permit->visaApplication->passenger->full_name ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $permit->visaApplication->airport?->name ?: '—' }}</td>
                                <td class="px-3 py-3">{{ optional($permit->valid_until)->format('Y-m-d') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">
                                    No expiring permits found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Expired Active Permits</h2>
                    <p class="text-sm text-gray-500">Latest permits already past valid-until.</p>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-3">Permit</th>
                            <th class="px-3 py-3">Traveler</th>
                            <th class="px-3 py-3">Airport</th>
                            <th class="px-3 py-3">Valid Until</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-700">
                        @forelse ($expiredPermits as $permit)
                            <tr>
                                <td class="px-3 py-3 font-medium text-gray-900">{{ $permit->permit_no }}</td>
                                <td class="px-3 py-3">{{ $permit->visaApplication->passenger->full_name ?? '—' }}</td>
                                <td class="px-3 py-3">{{ $permit->visaApplication->airport?->name ?: '—' }}</td>
                                <td class="px-3 py-3">{{ optional($permit->valid_until)->format('Y-m-d') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">
                                    No expired active permits found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>