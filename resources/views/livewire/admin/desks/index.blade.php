<div class="rounded-xl bg-white p-5 shadow">
    <h1 class="text-2xl font-bold text-gray-900">Desks</h1>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b text-left text-gray-500">
                <tr>
                    <th class="py-3 pr-4">Airport</th>
                    <th class="py-3 pr-4">Desk</th>
                    <th class="py-3 pr-4">Code</th>
                    <th class="py-3 pr-4">Location</th>
                    <th class="py-3 pr-4">Active</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($desks as $desk)
                    <tr class="border-b">
                        <td class="py-3 pr-4">{{ $desk->airport->code }}</td>
                        <td class="py-3 pr-4">{{ $desk->name }}</td>
                        <td class="py-3 pr-4">{{ $desk->code }}</td>
                        <td class="py-3 pr-4">{{ $desk->location ?: '—' }}</td>
                        <td class="py-3 pr-4">{{ $desk->active ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-gray-500">No desks found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
