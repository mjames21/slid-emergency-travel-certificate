<x-action-section>
    <x-slot name="title">
        {{ __('Passkeys') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Use Touch ID, Windows Hello, or a security key for staff sign-in and sensitive account access.') }}
    </x-slot>

    <x-slot name="content">
        @php($passkeys = $user->passkeys()->latest()->get())

        <div
            data-passkey-registration
            data-options-url="{{ route('passkey.registration-options') }}"
            data-store-url="{{ route('passkey.store') }}"
            class="space-y-6"
        >
            <div class="grid gap-3 sm:grid-cols-3">
                <div class="border border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Registered') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-950">{{ $passkeys->count() }}</div>
                </div>
                <div class="border border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Verification') }}</div>
                    <div class="mt-1 text-sm font-semibold text-emerald-800">{{ __('Biometric or PIN') }}</div>
                </div>
                <div class="border border-gray-200 bg-gray-50 px-4 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Security') }}</div>
                    <div class="mt-1 text-sm font-semibold text-emerald-800">{{ __('Password confirm required') }}</div>
                </div>
            </div>

            <div class="border border-emerald-100 bg-emerald-50 p-4">
                <label for="passkey_name" class="block text-sm font-semibold text-gray-800">{{ __('Passkey label') }}</label>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                    <x-input
                        id="passkey_name"
                        type="text"
                        data-passkey-name
                        class="w-full"
                        maxlength="255"
                        value="{{ __('Office device') }}"
                        autocomplete="off"
                    />
                    <x-button type="button" data-passkey-register class="justify-center bg-emerald-800 hover:bg-emerald-700 focus:bg-emerald-700">
                        {{ __('Register Passkey') }}
                    </x-button>
                </div>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('Use a device only you control. Passkeys cannot be exported from this system.') }}
                </p>
                <p data-passkey-status class="mt-3 hidden text-sm font-medium"></p>
            </div>

            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">{{ __('Registered passkeys') }}</h3>

                @if ($passkeys->isEmpty())
                    <p class="mt-3 border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-600">
                        {{ __('No passkeys are registered for this staff account yet.') }}
                    </p>
                @else
                    <div class="mt-3 divide-y divide-gray-200 border border-gray-200">
                        @foreach ($passkeys as $passkey)
                            <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="font-semibold text-gray-950">{{ $passkey->name }}</div>
                                    <div class="mt-1 text-sm text-gray-600">
                                        {{ $passkey->authenticator ?: __('Authenticator') }}
                                        <span class="mx-2 text-gray-300">|</span>
                                        {{ __('Added') }} {{ optional($passkey->created_at)->format('M j, Y g:i A') }}
                                        @if ($passkey->last_used_at)
                                            <span class="mx-2 text-gray-300">|</span>
                                            {{ __('Last used') }} {{ $passkey->last_used_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('passkey.destroy', $passkey) }}" data-passkey-delete>
                                    @csrf
                                    @method('DELETE')
                                    <x-secondary-button type="submit" class="text-red-700 hover:text-red-800">
                                        {{ __('Remove') }}
                                    </x-secondary-button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </x-slot>
</x-action-section>
