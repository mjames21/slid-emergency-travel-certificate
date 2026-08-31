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
                <label for="staff-name" class="text-sm font-medium text-gray-700">Name</label>
                <input id="staff-name" wire:model="name" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="staff-email" class="text-sm font-medium text-gray-700">Email</label>
                <input id="staff-email" wire:model="email" type="email" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <div class="mt-1 text-xs text-gray-500">Use an approved staff email domain.</div>
                @error('email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="staff-role" class="text-sm font-medium text-gray-700">Role</label>
                <select id="staff-role" wire:model="titleCode" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @foreach ($provisionableTitles as $title)
                        <option value="{{ $title->code }}">{{ $title->name }}</option>
                    @endforeach
                </select>
                @error('titleCode') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="staff-number" class="text-sm font-medium text-gray-700">Staff Number</label>
                <input id="staff-number" wire:model="staffNumber" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('staffNumber') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="staff-phone" class="text-sm font-medium text-gray-700">Phone</label>
                <input id="staff-phone" wire:model="phone" type="text" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div>
                <label for="staff-password" class="text-sm font-medium text-gray-700">Initial Password</label>
                <input id="staff-password" wire:model="password" type="password" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('password') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
            </div>

            <div class="md:col-span-2 xl:col-span-3">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="createUser"
                    class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="createUser">Create User</span>
                    <span wire:loading wire:target="createUser">Creating...</span>
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
                    <th class="py-3 pr-4">Titles</th>
                    <th class="py-3 pr-4">Active</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="border-b">
                        <td class="py-3 pr-4">{{ $user->name }}</td>
                        <td class="py-3 pr-4">{{ $user->email }}</td>
                        <td class="py-3 pr-4">{{ $user->staffTitles->pluck('name')->join(', ') ?: '—' }}</td>
                        <td class="py-3 pr-4">{{ $user->active ? 'Yes' : 'No' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-gray-500">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    @if ($showLoginDetails && $loginDetails !== [])
        <div
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="staff-login-details-title"
            x-data
            x-on:keydown.escape.window="$wire.closeLoginDetails()"
        >
            <div class="fixed inset-0 bg-gray-950/70" aria-hidden="true" wire:click="closeLoginDetails"></div>

            <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">
                <section
                    class="relative w-full max-w-2xl overflow-hidden rounded-lg border-t-4 border-emerald-700 bg-white shadow-xl"
                    x-trap.inert.noscroll="true"
                    x-init="$nextTick(() => $refs.copy.focus())"
                >
                    <div class="px-5 pb-5 pt-7 text-center sm:px-10 sm:pb-7 sm:pt-9">
                        <img
                            src="{{ asset('images/slid-logo.png') }}"
                            alt=""
                            class="mx-auto size-16 object-contain"
                        >

                        <h2 id="staff-login-details-title" class="mt-4 text-xl font-bold text-gray-950 sm:text-2xl">
                            Share Staff Login Details
                        </h2>
                        <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-gray-600">
                            The {{ $loginDetails['role'] }} account for {{ $loginDetails['name'] }} was created successfully.
                            Share these details securely. The temporary password is displayed only in this window.
                        </p>

                        <div class="mt-6 flex justify-end">
                            <button
                                type="button"
                                x-ref="copy"
                                data-login-details-copy="staff-login-details-copy"
                                data-default-label="Copy login details"
                                data-success-label="Copied"
                                data-error-label="Copy failed"
                                class="min-h-11 rounded-md border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-800 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-70"
                            >
                                <span data-copy-label>Copy login details</span>
                            </button>
                        </div>

                        <div class="mt-3 border border-gray-200 bg-gray-50 p-4 text-left sm:p-5">
                            <div class="text-sm font-semibold text-gray-900">Emergency Travel Certificate staff access</div>

                            <dl class="mt-4 grid gap-4 text-sm sm:grid-cols-[9rem_minmax(0,1fr)] sm:gap-x-5 sm:gap-y-3">
                                <dt class="font-medium text-gray-600">Login page</dt>
                                <dd class="min-w-0 break-all font-medium text-emerald-800">{{ $loginDetails['login_url'] }}</dd>

                                <dt class="font-medium text-gray-600">Email</dt>
                                <dd class="min-w-0 break-all text-gray-950">{{ $loginDetails['email'] }}</dd>

                                <dt class="font-medium text-gray-600">Role</dt>
                                <dd class="text-gray-950">{{ $loginDetails['role'] }}</dd>

                                <dt class="font-medium text-gray-600">Temporary password</dt>
                                <dd class="min-w-0 break-all font-mono text-sm font-semibold text-gray-950">{{ $loginDetails['password'] }}</dd>
                            </dl>
                        </div>

                        <textarea id="staff-login-details-copy" class="sr-only" readonly>{{ "SLID Emergency Travel Certificate staff login\nLogin page: {$loginDetails['login_url']}\nEmail: {$loginDetails['email']}\nRole: {$loginDetails['role']}\nTemporary password: {$loginDetails['password']}" }}</textarea>
                    </div>

                    <div class="flex justify-end border-t border-gray-200 bg-gray-50 px-5 py-4 sm:px-10">
                        <button
                            type="button"
                            wire:click="closeLoginDetails"
                            class="min-h-11 rounded-md bg-emerald-800 px-5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
                        >
                            Close
                        </button>
                    </div>
                </section>
            </div>
        </div>
    @endif
</div>
