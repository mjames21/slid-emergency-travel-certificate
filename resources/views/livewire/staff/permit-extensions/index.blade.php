{{-- FILE: resources/views/livewire/staff/permit-extensions/index.blade.php --}}
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Permit Extensions</h1>
            <p class="text-sm text-gray-600">Review, track, and manage permit extension requests.</p>
        </div>

        <a
            href="{{ route('staff.permit-extensions.create') }}"
            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white"
        >
            New Extension Request
        </a>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Total</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total'] }}</div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Pending</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['pending'] }}</div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Approved</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['approved'] }}</div>
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            <div class="text-sm text-gray-500">Rejected</div>
            <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['rejected'] }}</div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="grid gap-4 md:grid-cols-[1fr_220px]">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Search</label>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    class="w-full rounded-lg border-gray-300 shadow-sm"
                    placeholder="Extension no, permit no, traveler, passport"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Status</label>
                <select wire:model.live="status" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-6 shadow">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <th class="px-3 py-3">Extension No</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Original Permit</th>
                        <th class="px-3 py-3">Traveler</th>
                        <th class="px-3 py-3">Requested New Valid Until</th>
                        <th class="px-3 py-3">Requested By</th>
                        <th class="px-3 py-3">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 bg-white text-sm text-gray-700">
                    @forelse ($extensions as $extension)
                        @php
                            $statusClasses = match ($extension->status) {
                                'approved' => 'bg-green-100 text-green-800 border-green-200',
                                'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                default => 'bg-amber-100 text-amber-800 border-amber-200',
                            };
                        @endphp

                        <tr wire:key="permit-extension-{{ $extension->id }}">
                            <td class="px-3 py-3 font-medium text-gray-900">
                                {{ $extension->extension_no }}
                            </td>

                            <td class="px-3 py-3">
                                <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusClasses }}">
                                    {{ strtoupper($extension->status) }}
                                </span>
                            </td>

                            <td class="px-3 py-3">
                                {{ $extension->originalPermit?->permit_no ?: '—' }}
                            </td>

                            <td class="px-3 py-3">
                                <div class="font-medium text-gray-900">
                                    {{ $extension->passenger?->full_name ?: '—' }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    {{ $extension->passenger?->passport_number ?: '—' }}
                                </div>
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
                            <td colspan="7" class="px-3 py-6 text-center text-sm text-gray-500">
                                No permit extension requests found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $extensions->links() }}
        </div>
    </div>
</div>