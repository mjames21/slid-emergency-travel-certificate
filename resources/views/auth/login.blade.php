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
                <div class="relative mt-1">
                    <x-input id="password" class="block w-full pe-12" type="password" name="password" required autocomplete="current-password" />
                    <button
                        type="button"
                        data-password-toggle="password"
                        data-show-label="{{ __('Show password') }}"
                        data-hide-label="{{ __('Hide password') }}"
                        aria-controls="password"
                        aria-label="{{ __('Show password') }}"
                        aria-pressed="false"
                        title="{{ __('Show password') }}"
                        class="absolute inset-y-0 end-0 flex w-11 items-center justify-center rounded-e-md text-gray-500 hover:text-gray-800 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-emerald-600"
                    >
                        <svg data-password-icon="show" aria-hidden="true" class="size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2.062 12.348a1 1 0 0 1 0-.696C3.258 7.79 6.893 5 12 5c5.107 0 8.742 2.79 9.938 6.652a1 1 0 0 1 0 .696C20.742 16.21 17.107 19 12 19c-5.107 0-8.742-2.79-9.938-6.652" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg data-password-icon="hide" aria-hidden="true" class="hidden size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m15 18-.722-3.25" />
                            <path d="M2 8a10.645 10.645 0 0 0 20 0" />
                            <path d="m20 15-1.726-2.05" />
                            <path d="m4 15 1.726-2.05" />
                            <path d="m9 18 .722-3.25" />
                        </svg>
                    </button>
                </div>
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
