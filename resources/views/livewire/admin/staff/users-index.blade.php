<div class="space-y-6">
    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Staff Users</h1>
                <p class="mt-1 text-sm text-gray-600">System administrators create the ETC Issuer and Executive users here.</p>
            </div>
        </div>

        @if ($message)
            <div class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ $message }}</div>
        @endif

        <form wire:submit="createUser" class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <div>
                <label class="text-sm font-medium text-gray-700">Name</label>
                <input wire:model="name" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Email</label>
                <input wire:model="email" type="email" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <div class="mt-1 text-xs text-gray-500">Use an approved staff email domain.</div>
                @error('email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Role</label>
                <select wire:model="titleCode" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @foreach ($provisionableTitles as $title)
                        <option value="{{ $title->code }}">{{ $title->name }}</option>
                    @endforeach
                </select>
                @error('titleCode') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Staff Number</label>
                <input wire:model="staffNumber" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('staffNumber') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Phone</label>
                <input wire:model="phone" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="text-sm font-medium text-gray-700">Initial Password</label>
                <input wire:model="password" type="password" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('password') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <button type="submit" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">
                    Create User
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-5 py-4">
            <h2 class="text-lg font-semibold text-gray-900">Existing Staff</h2>
        </div>
        <div class="overflow-x-auto px-5 pb-5">
        <table class="min-w-full text-sm">
            <thead class="border-b text-left text-gray-500">
                <tr>
                    <th class="py-3 pr-4">Name</th>
                    <th class="py-3 pr-4">Email</th>
                    <th class="py-3 pr-4">Airport</th>
                    <th class="py-3 pr-4">Desk</th>
                    <th class="py-3 pr-4">Titles</th>
                    <th class="py-3 pr-4">Active</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b">
                        <td class="py-3 pr-4">{{ $user->name }}</td>
                        <td class="py-3 pr-4">{{ $user->email }}</td>
                        <td class="py-3 pr-4">{{ $user->primaryAirport?->code ?: '—' }}</td>
                        <td class="py-3 pr-4">{{ $user->primaryDesk?->code ?: '—' }}</td>
                        <td class="py-3 pr-4">{{ $user->staffTitles->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="py-3 pr-4">{{ $user->active ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-gray-500">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
