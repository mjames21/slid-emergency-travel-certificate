<div class="space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fraud Flags</h1>
            <p class="text-sm text-gray-600">Security, waiver, reprint, and verification anomalies.</p>
        </div>

        <button wire:click="exportCsv" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
            Export CSV
        </button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow">
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Severity</label>
                <select wire:model.live="severity" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">All</option>
                    <option value="low">low</option>
                    <option value="medium">medium</option>
                    <option value="high">high</option>
                    <option value="critical">critical</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">Resolved</label>
                <select wire:model.live="resolved" class="w-full rounded-lg border-gray-300 shadow-sm">
                    <option value="">All</option>
                    <option value="0">Open</option>
                    <option value="1">Resolved</option>
                </select>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b text-left text-gray-500">
                    <tr>
                        <th class="py-3 pr-4">Time</th>
                        <th class="py-3 pr-4">Type</th>
                        <th class="py-3 pr-4">Severity</th>
                        <th class="py-3 pr-4">Description</th>
                        <th class="py-3 pr-4">Resolved</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($flags as $flag)
                        <tr class="border-b">
                            <td class="py-3 pr-4">{{ optional($flag->flagged_at)->format('Y-m-d H:i:s') }}</td>
                            <td class="py-3 pr-4">{{ $flag->flag_type }}</td>
                            <td class="py-3 pr-4">{{ $flag->severity }}</td>
                            <td class="py-3 pr-4">{{ $flag->description }}</td>
                            <td class="py-3 pr-4">{{ $flag->resolved ? 'Yes' : 'No' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-4 text-gray-500">No fraud flags found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $flags->links() }}
        </div>
    </div>
</div>
