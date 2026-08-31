<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SLID Emergency Travel Certificate') }}</title>

        @include('partials.pwa')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-gray-100 font-sans antialiased" x-data="{ mobileNavigationOpen: false }" @keydown.escape.window="mobileNavigationOpen = false">
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
                            @include('partials.staff-navigation-links')
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
                            <div class="flex min-h-16 items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                                <div class="flex min-w-0 items-center gap-3">
                                    <button
                                        type="button"
                                        class="shrink-0 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 md:hidden"
                                        @click="mobileNavigationOpen = true"
                                        aria-controls="mobile-staff-navigation"
                                        :aria-expanded="mobileNavigationOpen.toString()"
                                    >
                                        Menu
                                    </button>
                                    <div class="min-w-0">
                                    @isset($header)
                                        {{ $header }}
                                    @else
                                        <h1 class="text-lg font-semibold text-gray-900">Emergency Travel Certificate</h1>
                                    @endisset
                                    </div>
                                </div>

                                <div class="hidden text-right sm:block">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500">HQ review access</div>
                                </div>
                            </div>
                        </header>

                        <div
                            x-cloak
                            x-show="mobileNavigationOpen"
                            class="fixed inset-0 z-50 md:hidden"
                            role="dialog"
                            aria-modal="true"
                            aria-label="Staff navigation"
                        >
                            <button
                                type="button"
                                class="absolute inset-0 bg-black/50"
                                @click="mobileNavigationOpen = false"
                                aria-label="Close navigation"
                            ></button>

                            <aside
                                id="mobile-staff-navigation"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="-translate-x-full"
                                x-transition:enter-end="translate-x-0"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="translate-x-0"
                                x-transition:leave-end="-translate-x-full"
                                class="relative flex h-full w-[min(20rem,88vw)] flex-col bg-emerald-950 text-white shadow-xl"
                            >
                                <div class="flex items-start justify-between gap-4 border-b border-emerald-900 px-5 py-5">
                                    <div>
                                        <div class="text-xs uppercase tracking-wider text-emerald-200">Sierra Leone Immigration Department</div>
                                        <div class="mt-1 font-bold">Emergency Travel Certificate</div>
                                    </div>
                                    <button type="button" class="rounded-md border border-emerald-700 px-3 py-2 text-sm font-semibold hover:bg-emerald-900" @click="mobileNavigationOpen = false">
                                        Close
                                    </button>
                                </div>

                                <div class="border-b border-emerald-900 px-5 py-4">
                                    <div class="font-semibold">{{ $user->name }}</div>
                                    <div class="mt-1 text-sm text-emerald-100">{{ $user->job_title ?: $user->email }}</div>
                                </div>

                                <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" @click="mobileNavigationOpen = false">
                                    @include('partials.staff-navigation-links')
                                </nav>

                                <div class="border-t border-emerald-900 p-3">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded-md border border-emerald-700 px-4 py-3 text-sm font-medium text-emerald-50 hover:bg-emerald-900">
                                            Log Out
                                        </button>
                                    </form>
                                </div>
                            </aside>
                        </div>

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
