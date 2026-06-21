<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Emergency Travel Certificate Applications</h1>
            <p class="mt-1 text-sm text-gray-600">HQ review queue for paid Emergency Travel Certificate applications.</p>
        </div>
    </div>

    @if ($message)
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ $message }}</div>
    @endif

    @if ($error)
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $error }}</div>
    @endif

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">Paid, Pending HQ</div>
            <div class="mt-1 text-2xl font-bold text-emerald-800">{{ $paidCount }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">Awaiting Payment</div>
            <div class="mt-1 text-2xl font-bold text-amber-700">{{ $awaitingPaymentCount }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="text-sm text-gray-500">Certificates Issued</div>
            <div class="mt-1 text-2xl font-bold text-gray-900">{{ $issuedCount }}</div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">Search</label>
                <input wire:model.live.debounce.300ms="search" type="search" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" placeholder="Tracking code, application, passport, traveler">
            </div>
            <div>
                <label class="text-sm font-medium text-gray-700">Status</label>
                <select wire:model.live="status" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">All</option>
                    <option value="awaiting_payment">Awaiting payment</option>
                    <option value="paid">Paid</option>
                    <option value="approved">Approved</option>
                    <option value="permit_issued">Certificate issued</option>
                </select>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Tracking</th>
                        <th class="px-4 py-3">Traveler</th>
                        <th class="px-4 py-3">Arrival</th>
                        <th class="px-4 py-3">Payment</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Certificate</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($applications as $application)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $application->public_tracking_code }}</div>
                                <div class="text-xs text-gray-500">{{ $application->application_no }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $application->passenger?->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $application->passenger?->passport_number }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $application->arrival_date?->format('Y-m-d') ?: '—' }}</td>
                            <td class="px-4 py-3 text-gray-700">
                                {{ strtoupper($application->latestInvoice?->status?->value ?? '—') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ strtoupper($application->status->value) }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-700">
                                @if ($application->permit)
                                    @can('print', $application->permit)
                                        <a href="{{ route('documents.certificates.show', $application->permit) }}" class="font-medium underline underline-offset-2">{{ $application->permit->permit_no }}</a>
                                    @else
                                        <span class="font-medium text-gray-700">{{ $application->permit->permit_no }}</span>
                                    @endcan
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($canIssueEtc && $application->status->value === 'paid' && ! $application->permit)
                                    <button wire:click="approve({{ $application->id }})" type="button" class="rounded-md bg-emerald-700 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-800">
                                        Approve & Email Certificate
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-500">No Emergency Travel Certificate applications found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-4 py-3">{{ $applications->links() }}</div>
    </div>
</div>
