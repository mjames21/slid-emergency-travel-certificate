<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-gray-900">International Readiness</h1>
        <p class="mt-1 text-sm text-gray-600">ICAO, IATA, IOM, security accreditation, and Sierra Leone policy sign-off status.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Active Travel Rules</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $activeTravelRulesCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Local IATA-style requirement rules available to screening.</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Active Watchlist Entries</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $activeWatchlistCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Internal or imported watchlist records currently active.</p>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Document Alerts</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $documentAlertCount }}</div>
            <p class="mt-2 text-xs text-gray-500">Lost, stolen, revoked, or suspect travel-document records.</p>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Certification Evidence Register</h2>
            <p class="mt-1 text-sm text-gray-500">Engineering evidence and external launch gates for national deployment.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Area</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Owner</th>
                        <th class="px-4 py-3">Evidence</th>
                        <th class="px-4 py-3">Next Gate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($certificationEvidence as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $item['area'] }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full {{ str_contains($item['status'], 'required') ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700' }} px-2 py-1 text-xs font-semibold">
                                    {{ strtoupper(str_replace('_', ' ', $item['status'])) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $item['owner'] }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700">{{ $item['evidence'] }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $item['next_gate'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Policy Approval Register</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Area</th>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Version</th>
                        <th class="px-4 py-3">Summary</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($policyApprovals as $approval)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ strtoupper(str_replace('_', ' ', $approval->policy_area)) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $approval->standard_reference ?: '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full {{ $approval->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} px-2 py-1 text-xs font-semibold">
                                    {{ strtoupper($approval->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $approval->version ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $approval->summary }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">No policy approvals registered.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Recent Travel Requirement Rules</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Source</th>
                        <th class="px-4 py-3">Nationality</th>
                        <th class="px-4 py-3">Visa</th>
                        <th class="px-4 py-3">Max Stay</th>
                        <th class="px-4 py-3">Passport Validity</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentRules as $rule)
                        <tr>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ strtoupper($rule->source) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $rule->nationality_code ?: 'ALL' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ strtoupper(str_replace('_', ' ', $rule->visa_type)) }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $rule->max_stay_days ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $rule->min_passport_validity_days }} days</td>
                            <td class="px-4 py-3 text-gray-700">{{ $rule->active ? 'Active' : 'Inactive' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No travel rules registered.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
