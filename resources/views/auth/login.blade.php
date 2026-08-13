<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button class="ms-4">
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>

        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::passkeys()))
            <div class="my-6 flex items-center gap-3">
                <div class="h-px flex-1 bg-gray-200"></div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Or') }}</div>
                <div class="h-px flex-1 bg-gray-200"></div>
            </div>

            <div
                data-passkey-login
                data-options-url="{{ route('passkey.login-options') }}"
                data-login-url="{{ route('passkey.login') }}"
                data-remember-selector="#remember_me"
                class="space-y-3"
            >
                <x-secondary-button type="button" data-passkey-login-button class="w-full justify-center border-emerald-700 text-emerald-800 hover:bg-emerald-50">
                    {{ __('Sign in with passkey') }}
                </x-secondary-button>
                <p data-passkey-login-status class="hidden text-sm font-medium"></p>
            </div>
        @endif
    </x-authentication-card>
</x-guest-layout>
