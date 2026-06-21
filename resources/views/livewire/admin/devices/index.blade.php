<div class="rounded-xl bg-white p-5 shadow">
    <h1 class="text-2xl font-bold text-gray-900">Registered Devices</h1>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b text-left text-gray-500">
                <tr>
                    <th class="py-3 pr-4">Device</th>
                    <th class="py-3 pr-4">Airport</th>
                    <th class="py-3 pr-4">Desk</th>
                    <th class="py-3 pr-4">Printer</th>
                    <th class="py-3 pr-4">Trusted</th>
                    <th class="py-3 pr-4">Active</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($devices as $device)
                    <tr class="border-b">
                        <td class="py-3 pr-4">{{ $device->device_name }}</td>
                        <td class="py-3 pr-4">{{ $device->airport?->code ?: '—' }}</td>
                        <td class="py-3 pr-4">{{ $device->desk?->code ?: '—' }}</td>
                        <td class="py-3 pr-4">{{ $device->printer_name ?: '—' }}</td>
                        <td class="py-3 pr-4">{{ $device->trusted ? 'Yes' : 'No' }}</td>
                        <td class="py-3 pr-4">{{ $device->active ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-gray-500">No devices found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
