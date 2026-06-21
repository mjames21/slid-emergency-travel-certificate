<div class="rounded-xl bg-white p-5 shadow">
    <h1 class="text-2xl font-bold text-gray-900">Airports</h1>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b text-left text-gray-500">
                <tr>
                    <th class="py-3 pr-4">Name</th>
                    <th class="py-3 pr-4">Code</th>
                    <th class="py-3 pr-4">City</th>
                    <th class="py-3 pr-4">Active</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($airports as $airport)
                    <tr class="border-b">
                        <td class="py-3 pr-4">{{ $airport->name }}</td>
                        <td class="py-3 pr-4">{{ $airport->code }}</td>
                        <td class="py-3 pr-4">{{ $airport->city }}</td>
                        <td class="py-3 pr-4">{{ $airport->active ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-gray-500">No airports found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
