{{-- FILE: resources/views/livewire/staff/dashboard.blade.php --}}
<div class="space-y-6">
    @php
        $borderManagementEnabled = (bool) config('features.border_management');
    @endphp

    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Staff Dashboard</h1>
            <p class="text-sm text-gray-600">
                @if ($dashboardAirportName)
                    Operational overview for {{ $dashboardAirportName }}.
                @else
                    Operational overview.
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            @if ($borderManagementEnabled)
                <a
                    href="{{ route('staff.border-movements.create') }}"
                    class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white"
                >
                    Record Movement
                </a>
            @endif

            @if ($canReviewExtensions)
                <a
                    href="{{ route('staff.permit-extensions.index') }}"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
                >
                    Open Permit Extensions
                </a>
            @endif

            <a
                href="{{ route('staff.reports.permit-expiry') }}"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
            >
                Open Expiry Report
            </a>
        </div>
    </div>

    @if ($borderManagementEnabled)
        <div class="grid gap-6 md:grid-cols-3">
            <a href="{{ route('staff.border-movements.index') }}" class="block rounded-xl border border-emerald-100 bg-white p-6 shadow">
                <div class="text-sm text-gray-500">Entries Today</div>
                <div class="mt-2 text-3xl font-bold text-emerald-800">{{ $entriesTodayCount }}</div>
                <p class="mt-2 text-xs text-gray-500">Admitted inbound movements recorded today.</p>
            </a>

            <a href="{{ route('staff.border-movements.index') }}" class="block rounded-xl border border-emerald-100 bg-white p-6 shadow">
                <div class="text-sm text-gray-500">Exits Today</div>
                <div class="mt-2 text-3xl font-bold text-emerald-800">{{ $exitsTodayCount }}</div>
                <p class="mt-2 text-xs text-gray-500">Departures recorded today.</p>
            </a>

            <a href="{{ route('staff.border-movements.index') }}" class="block rounded-xl border border-amber-100 bg-white p-6 shadow">
                <div class="text-sm text-gray-500">Refusals / Referrals Today</div>
                <div class="mt-2 text-3xl font-bold text-amber-700">{{ $referralsTodayCount }}</div>
                <p class="mt-2 text-xs text-gray-500">Secondary inspection, referrals, or refusals.</p>
            </a>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-7">
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Pending Approvals</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $pendingExtensionApprovalsCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Extension requests awaiting review.</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Extensions Today</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $extensionsTodayCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Requests created today.</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Approved Today</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $approvedExtensionsTodayCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Requests approved today.</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Rejected Today</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $rejectedExtensionsTodayCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Requests rejected today.</p>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Waived Today</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $feeWaivedExtensionsTodayCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Fee-waived requests today.</p>
        </div>

        <a href="{{ route('staff.reports.permit-expiry') }}" class="block rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Expiring in 7 Days</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $expiringSoonPermitsCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Active permits nearing expiry.</p>
        </a>

        <a href="{{ route('staff.reports.permit-expiry') }}" class="block rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Expired Active Permits</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $expiredPermitsCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Active permits already past valid-until.</p>
        </a>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Recent Pending Extension Requests</h2>
                <p class="text-sm text-gray-500">Open requests that still need action.</p>
            </div>

            @if ($canReviewExtensions)
                <a
                    href="{{ route('staff.permit-extensions.create') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
                >
                    New Request
                </a>
            @endif
        </div>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-3 py-3">Extension No</th>
                        <th class="px-3 py-3">Permit</th>
                        <th class="px-3 py-3">Traveler</th>
                        <th class="px-3 py-3">Requested New Valid Until</th>
                        <th class="px-3 py-3">Requested By</th>
                        <th class="px-3 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-700">
                    @forelse ($recentPendingExtensions as $extension)
                        <tr>
                            <td class="px-3 py-3 font-medium text-gray-900">
                                {{ $extension->extension_no }}
                            </td>
                            <td class="px-3 py-3">
                                {{ $extension->originalPermit?->permit_no ?: '—' }}
                            </td>
                            <td class="px-3 py-3">
                                {{ $extension->passenger?->full_name ?: '—' }}
                            </td>
                            <td class="px-3 py-3">
                                {{ optional($extension->requested_new_valid_until)->format('Y-m-d') ?: '—' }}
                            </td>
                            <td class="px-3 py-3">
                                {{ $extension->requester?->name ?: '—' }}
                            </td>
                            <td class="px-3 py-3">
                                <a
                                    href="{{ route('staff.permit-extensions.show', $extension) }}"
                                    class="font-medium text-gray-900 underline underline-offset-2"
                                >
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                No pending extension requests.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($borderManagementEnabled)
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Recent Border Movements</h2>
                    <p class="text-sm text-gray-500">Latest entry, exit, referral, and refusal records.</p>
                </div>

                <a
                    href="{{ route('staff.border-movements.index') }}"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700"
                >
                    Open Register
                </a>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-3 py-3">Reference</th>
                            <th class="px-3 py-3">Traveler</th>
                            <th class="px-3 py-3">Permit</th>
                            <th class="px-3 py-3">Movement</th>
                            <th class="px-3 py-3">Decision</th>
                            <th class="px-3 py-3">Risk</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-700">
                        @forelse ($recentBorderMovements as $movement)
                            <tr>
                                <td class="px-3 py-3 font-medium text-gray-900">{{ $movement->movement_reference }}</td>
                                <td class="px-3 py-3">{{ $movement->passenger?->full_name ?: '—' }}</td>
                                <td class="px-3 py-3">{{ $movement->permit?->permit_no ?: '—' }}</td>
                                <td class="px-3 py-3">{{ strtoupper($movement->movement_type) }}</td>
                                <td class="px-3 py-3">{{ strtoupper($movement->decision) }}</td>
                                <td class="px-3 py-3">{{ strtoupper($movement->risk_level) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-3 py-6 text-center text-sm text-gray-500">
                                    No border movements recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
