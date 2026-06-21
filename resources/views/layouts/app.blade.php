<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SLID Emergency Travel Certificate') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-100 font-sans antialiased">
        @php($user = auth()->user())

        <div class="min-h-screen">
            @auth
                <div class="flex min-h-screen">
                    <aside class="hidden w-72 flex-col border-r border-emerald-900 bg-emerald-950 text-white md:flex">
                        <div class="border-b border-emerald-900 px-6 py-5">
                            <div class="text-xs uppercase tracking-wider text-emerald-200">Sierra Leone Immigration Department</div>
                            <div class="mt-1 text-lg font-bold">Emergency Travel Certificate</div>
                        </div>

                        <div class="px-4 py-4">
                            <div class="rounded-lg bg-emerald-900 px-4 py-3">
                                <div class="text-xs uppercase tracking-wide text-emerald-200">Signed in as</div>
                                <div class="mt-1 font-semibold">{{ $user->name }}</div>
                                <div class="text-sm text-emerald-100">{{ $user->job_title ?: $user->email }}</div>
                            </div>
                        </div>

                        <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-6">
                            <div class="px-4 text-xs font-semibold uppercase tracking-wider text-emerald-300">HQ Review</div>

                            <a href="{{ route('hq.emergency-travel-certificates.index') }}"
                               class="{{ request()->routeIs('hq.emergency-travel-certificates.*') || request()->routeIs('dashboard') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white' }} block rounded-lg px-4 py-3 text-sm font-medium">
                                ETC Applications
                            </a>

                            <a href="{{ route('etc.apply') }}"
                               class="block rounded-lg px-4 py-3 text-sm font-medium text-emerald-100 hover:bg-emerald-900 hover:text-white">
                                Public Application
                            </a>

                            @if ($user?->hasStaffTitle('system_administrator'))
                                <div class="px-4 pt-4 text-xs font-semibold uppercase tracking-wider text-emerald-300">Administration</div>

                                <a href="{{ route('admin.staff.users.index') }}"
                                   class="{{ request()->routeIs('admin.staff.users.*') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white' }} block rounded-lg px-4 py-3 text-sm font-medium">
                                    Staff Users
                                </a>
                            @endif

                            <a href="{{ route('profile.show') }}"
                               class="{{ request()->routeIs('profile.show') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white' }} block rounded-lg px-4 py-3 text-sm font-medium">
                                Profile And MFA
                            </a>
                        </nav>

                        <div class="border-t border-emerald-900 p-3">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full rounded-lg border border-emerald-700 px-4 py-2 text-sm font-medium text-emerald-50 hover:bg-emerald-900">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </aside>

                    <div class="flex min-w-0 flex-1 flex-col">
                        <header class="border-b border-gray-200 bg-white">
                            <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                                <div>
                                    @isset($header)
                                        {{ $header }}
                                    @else
                                        <h1 class="text-lg font-semibold text-gray-900">Emergency Travel Certificate</h1>
                                    @endisset
                                </div>

                                <div class="hidden text-right sm:block">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">HQ review access</div>
                                </div>
                            </div>
                        </header>

                        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                            {{ $slot }}
                        </main>
                    </div>
                </div>
            @else
                <div class="min-h-screen bg-gray-100">
                    {{ $slot }}
                </div>
            @endauth
        </div>

        @stack('modals')
        @livewireScripts
    </body>
</html>
