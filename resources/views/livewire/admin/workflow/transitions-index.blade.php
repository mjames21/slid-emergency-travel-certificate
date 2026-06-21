<div class="rounded-xl bg-white p-5 shadow">
    <h1 class="text-2xl font-bold text-gray-900">Workflow Transitions</h1>

    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="border-b text-left text-gray-500">
                <tr>
                    <th class="py-3 pr-4">Staff Title</th>
                    <th class="py-3 pr-4">From</th>
                    <th class="py-3 pr-4">Action</th>
                    <th class="py-3 pr-4">To</th>
                    <th class="py-3 pr-4">Checker</th>
                    <th class="py-3 pr-4">Active</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transitions as $transition)
                    <tr class="border-b">
                        <td class="py-3 pr-4">{{ $transition->staffTitle->code }}</td>
                        <td class="py-3 pr-4">{{ $transition->from_status_key }}</td>
                        <td class="py-3 pr-4">{{ $transition->action }}</td>
                        <td class="py-3 pr-4">{{ $transition->to_status_key }}</td>
                        <td class="py-3 pr-4">{{ $transition->requires_checker ? 'Yes' : 'No' }}</td>
                        <td class="py-3 pr-4">{{ $transition->active ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-gray-500">No transitions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
