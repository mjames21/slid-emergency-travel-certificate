<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SLID LEAPS | Sierra Leone Immigration Department</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-white text-gray-950 antialiased">
    @php
        $etcEnabled = (bool) config('features.emergency_travel_certificate');
        $borderManagementEnabled = (bool) config('features.border_management');
    @endphp

    <div class="min-h-screen">
        <div class="border-b border-emerald-900 bg-emerald-950 text-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-2.5">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-100">
                    Government of Sierra Leone
                </div>
                <div class="hidden text-xs font-medium text-emerald-100 sm:block">
                    Official Sierra Leone Immigration Department Digital Service
                </div>
            </div>
        </div>

        <header class="border-b border-gray-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                    <img src="{{ asset('images/slid-logo.png') }}" alt="Sierra Leone Immigration Department" class="h-12 w-12 shrink-0 object-contain">
                    <div class="min-w-0">
                        <div class="text-sm font-semibold uppercase tracking-[0.14em] text-emerald-800">Sierra Leone Immigration Department</div>
                        <div class="mt-0.5 text-lg font-bold tracking-tight text-gray-950">SLID LEAPS</div>
                    </div>
                </a>

                <nav class="hidden items-center gap-7 text-sm font-semibold text-gray-700 lg:flex">
                    <a href="#verify" class="hover:text-emerald-800">Verify</a>
                    @if ($etcEnabled)
                        <a href="{{ route('etc.apply') }}" class="hover:text-emerald-800">Apply for ETC</a>
                    @endif
                    <a href="#operations" class="hover:text-emerald-800">Operations</a>
                    <a href="#standards" class="hover:text-emerald-800">Standards</a>
                    <a href="#security" class="hover:text-emerald-800">Security</a>
                </nav>

                <div class="flex items-center gap-2">
                    @if ($etcEnabled)
                        <a href="{{ route('etc.apply') }}" class="hidden rounded-md border border-emerald-700 px-4 py-2 text-sm font-bold text-emerald-800 hover:bg-emerald-50 sm:inline-flex">
                            Apply for ETC
                        </a>
                    @endif

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-800">
                            Open Dashboard
                        </a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden border-b border-gray-200 bg-white">
                <div class="mx-auto grid min-h-[calc(100vh-112px)] max-w-7xl items-center gap-10 px-5 py-12 lg:grid-cols-[1.02fr_0.98fr] lg:py-16">
                    <div class="max-w-3xl">
                        <h1 class="text-5xl font-bold leading-[1.02] tracking-tight text-gray-950 sm:text-6xl lg:text-7xl">
                            SLID LEAPS
                        </h1>
                        <p class="mt-5 max-w-2xl text-xl font-semibold leading-8 text-gray-800">
                            National landing permit and Emergency Travel Certificate platform for Sierra Leone Immigration Department operations.
                        </p>
                        <p class="mt-5 max-w-2xl text-base leading-8 text-gray-600">
                            A serious national platform for visa-on-arrival landing permit intake, Emergency Travel Certificate applications, passport and MRZ checks, payment-controlled issuance, verification, audit records, and headquarters oversight.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-3">
                            @if ($etcEnabled)
                                <a href="{{ route('etc.apply') }}" class="rounded-md bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                                    Apply for Emergency Travel Certificate
                                </a>
                            @endif
                            <a href="#verify" class="rounded-md bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                                Verify Landing Permit
                            </a>
                            @auth
                                @if ($borderManagementEnabled)
                                <a href="{{ route('staff.border-movements.create') }}" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-bold text-gray-800 hover:border-emerald-700 hover:text-emerald-800">
                                    Record Border Movement
                                </a>
                                @else
                                    <a href="{{ route('dashboard') }}" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-bold text-gray-800 hover:border-emerald-700 hover:text-emerald-800">
                                        Open Permit Dashboard
                                    </a>
                                @endif
                            @endauth
                        </div>

                        <div class="mt-10 grid max-w-3xl gap-3 sm:grid-cols-3">
                            <div class="border-l-4 border-emerald-700 bg-gray-50 px-4 py-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Identity</div>
                                <div class="mt-1 text-sm font-semibold text-gray-950">Passport, MRZ, nationality code</div>
                            </div>
                            <div class="border-l-4 border-emerald-700 bg-gray-50 px-4 py-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Control</div>
                                <div class="mt-1 text-sm font-semibold text-gray-950">Screening, referrals, overrides</div>
                            </div>
                            <div class="border-l-4 border-emerald-700 bg-gray-50 px-4 py-3">
                                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Oversight</div>
                                <div class="mt-1 text-sm font-semibold text-gray-950">Audit, reports, reconciliation</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="border border-gray-200 bg-gray-50 p-5 shadow-sm">
                            <div class="flex items-start justify-between gap-5 border-b border-gray-200 pb-5">
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-800">Operational Console</div>
                                    <div class="mt-2 text-2xl font-bold text-gray-950">Permit desk readiness</div>
                                </div>
                                <img src="{{ asset('images/slid-logo.png') }}" alt="" class="h-14 w-14 object-contain">
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="border border-gray-200 bg-white p-4">
                                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Permit Status</div>
                                    <div class="mt-2 text-lg font-bold text-emerald-800">Verified</div>
                                    <div class="mt-1 text-sm text-gray-600">QR and verification code available</div>
                                </div>
                                <div class="border border-gray-200 bg-white p-4">
                                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Screening</div>
                                    <div class="mt-2 text-lg font-bold text-amber-700">Officer Review</div>
                                    <div class="mt-1 text-sm text-gray-600">Risk, watchlist, document checks</div>
                                </div>
                                <div class="border border-gray-200 bg-white p-4">
                                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Permit</div>
                                    <div class="mt-2 text-lg font-bold text-gray-950">Issue / Verify</div>
                                    <div class="mt-1 text-sm text-gray-600">Payment, approval, document control</div>
                                </div>
                                <div class="border border-gray-200 bg-white p-4">
                                    <div class="text-xs font-bold uppercase tracking-wide text-gray-500">HQ</div>
                                    <div class="mt-2 text-lg font-bold text-gray-950">Live Oversight</div>
                                    <div class="mt-1 text-sm text-gray-600">Audit trail and readiness status</div>
                                </div>
                            </div>

                            <div class="mt-5 border border-emerald-200 bg-emerald-50 p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <div class="text-sm font-bold text-emerald-950">International readiness layer</div>
                                        <div class="mt-1 text-sm text-emerald-800">Passport evidence, MRZ controls, permit verification</div>
                                    </div>
                                    <div class="shrink-0 text-right text-sm font-bold text-emerald-900">ACTIVE</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="verify" class="border-b border-gray-200 bg-gray-50">
                <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 lg:grid-cols-[0.95fr_1.05fr]">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight text-gray-950">Verify an official Landing Permit.</h2>
                        <p class="mt-4 max-w-2xl text-base leading-8 text-gray-600">
                            Verification confirms that a Landing Permit exists in the official SLID LEAPS record. A permit that cannot be verified through this service should not be accepted as valid.
                        </p>
                        <div class="mt-7 grid gap-3 sm:grid-cols-2">
                            <div class="border border-gray-200 bg-white p-4">
                                <div class="text-sm font-bold text-gray-950">Public verification</div>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Use the QR code or printed verification code.</p>
                            </div>
                            <div class="border border-gray-200 bg-white p-4">
                                <div class="text-sm font-bold text-gray-950">Officer verification</div>
                                <p class="mt-2 text-sm leading-6 text-gray-600">Review permit lifecycle, print history, and fraud flags.</p>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-200 bg-white p-6 shadow-sm">
                        <form
                            method="GET"
                            onsubmit="event.preventDefault(); const code = document.getElementById('verification_code').value.trim(); if (code) { window.location.href = '/verify/' + encodeURIComponent(code); }"
                        >
                            <label for="verification_code" class="block text-sm font-bold text-gray-800">
                                Permit Verification Code
                            </label>
                            <input
                                id="verification_code"
                                type="text"
                                required
                                placeholder="Enter verification code"
                                class="mt-3 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm font-semibold uppercase text-gray-950 outline-none placeholder:normal-case placeholder:font-normal placeholder:text-gray-400 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-100"
                            >
                            <button type="submit" class="mt-4 w-full rounded-md bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                                Verify Permit
                            </button>
                        </form>

                        <div class="mt-5 border-t border-gray-200 pt-5 text-xs leading-6 text-gray-500">
                            Verification results are returned from the official Sierra Leone Immigration Department permit record.
                        </div>
                    </div>
                </div>
            </section>

            <section id="operations" class="border-b border-gray-200 bg-white">
                <div class="mx-auto max-w-7xl px-5 py-16">
                    <div class="max-w-3xl">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-950">Built for airport immigration operations.</h2>
                        <p class="mt-4 text-base leading-8 text-gray-600">
                            SLID LEAPS gives officers a controlled path from visa-on-arrival intake to payment evidence, permit issuance, verification, and headquarters oversight.
                        </p>
                    </div>

                    <div class="mt-10 grid gap-4 md:grid-cols-2 {{ $borderManagementEnabled ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
                        <div class="border border-gray-200 bg-gray-50 p-5">
                            <div class="text-base font-bold text-gray-950">Visa-on-Arrival Intake</div>
                            <p class="mt-3 text-sm leading-7 text-gray-600">Capture traveler identity, purpose of visit, stay duration, point of entry, host details, and flight context.</p>
                        </div>
                        <div class="border border-gray-200 bg-gray-50 p-5">
                            <div class="text-base font-bold text-gray-950">Payment-Controlled Issuance</div>
                            <p class="mt-3 text-sm leading-7 text-gray-600">Issue permits only after successful payment or an authorized waiver has been reviewed and recorded.</p>
                        </div>
                        <div class="border border-gray-200 bg-gray-50 p-5">
                            <div class="text-base font-bold text-gray-950">Admissibility Screening</div>
                            <p class="mt-3 text-sm leading-7 text-gray-600">Check passport validity, permit validity, MRZ, watchlist records, document alerts, and travel rules.</p>
                        </div>
                        @if ($borderManagementEnabled)
                            <div class="border border-gray-200 bg-gray-50 p-5">
                                <div class="text-base font-bold text-gray-950">Border Movements</div>
                                <p class="mt-3 text-sm leading-7 text-gray-600">Record entry, exit, refusal, referral, overstay, and supervisor override decisions with audit history.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <section id="standards" class="border-b border-gray-200 bg-emerald-950 text-white">
                <div class="mx-auto max-w-7xl px-5 py-16">
                    <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
                        <div>
                            <h2 class="text-3xl font-bold tracking-tight">National permit operating model.</h2>
                            <p class="mt-4 text-base leading-8 text-emerald-50">
                                The platform provides official operational controls for permit intake, payment evidence, permit issuance, verification, audit history, and headquarters oversight. External integrations can be connected as approvals and credentials are issued.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="border border-emerald-800 bg-emerald-900 p-5">
                                <div class="text-xl font-bold">ICAO</div>
                                <p class="mt-3 text-sm leading-7 text-emerald-50">Passport evidence, MRZ validation, nationality codes, document security, permit verification.</p>
                            </div>
                            <div class="border border-emerald-800 bg-emerald-900 p-5">
                                <div class="text-xl font-bold">IATA</div>
                                <p class="mt-3 text-sm leading-7 text-emerald-50">Carrier context, flight details, travel requirement rules, entry-readiness controls.</p>
                            </div>
                            <div class="border border-emerald-800 bg-emerald-900 p-5">
                                <div class="text-xl font-bold">IOM</div>
                                <p class="mt-3 text-sm leading-7 text-emerald-50">Traveler history, referral cues, migration-management notes, protection-sensitive workflow.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="security" class="bg-white">
                <div class="mx-auto max-w-7xl px-5 py-16">
                    <div class="grid gap-10 lg:grid-cols-[1.05fr_0.95fr]">
                        <div>
                            <h2 class="text-3xl font-bold tracking-tight text-gray-950">Security, oversight, and deployment readiness.</h2>
                            <p class="mt-4 text-base leading-8 text-gray-600">
                                Headquarters can track audit activity, permit expiry, reconciliation, fraud flags, policy approval status, and integration readiness from the operational back office.
                            </p>
                        </div>

                        <div class="border border-gray-200 bg-gray-50 p-5">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="bg-white p-4">
                                    <div class="text-sm font-bold text-gray-950">Watchlist-ready</div>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Internal records now; official feeds later.</p>
                                </div>
                                <div class="bg-white p-4">
                                    <div class="text-sm font-bold text-gray-950">Document alerts</div>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Lost, stolen, revoked, suspect documents.</p>
                                </div>
                                <div class="bg-white p-4">
                                    <div class="text-sm font-bold text-gray-950">Supervisor overrides</div>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">Required for non-clear admissions.</p>
                                </div>
                                <div class="bg-white p-4">
                                    <div class="text-sm font-bold text-gray-950">Policy sign-off</div>
                                    <p class="mt-2 text-sm leading-6 text-gray-600">ICAO, IATA, IOM, security approvals.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="border-t border-emerald-900 bg-emerald-950 text-white">
                <div class="mx-auto grid max-w-7xl gap-6 px-5 py-10 md:grid-cols-[1fr_auto] md:items-center">
                    <div>
                        <h2 class="text-xl font-bold">Official use notice</h2>
                        <p class="mt-3 max-w-4xl text-sm leading-7 text-emerald-50">
                            SLID LEAPS is an official operational platform of the Sierra Leone Immigration Department. Unauthorized access, misuse, falsification of records, or tampering with permit verification is prohibited.
                        </p>
                    </div>
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-white px-5 py-3 text-sm font-bold text-emerald-950 hover:bg-gray-100">
                            Open Dashboard
                        </a>
                    @endauth
                </div>
            </section>
        </main>

        <footer class="border-t border-emerald-900 bg-emerald-950 text-white">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-6 text-xs text-emerald-100 sm:flex-row sm:items-center sm:justify-between">
                <div>© {{ date('Y') }} Sierra Leone Immigration Department. All rights reserved.</div>
                <div>SLID LEAPS · Landing Permit and Emergency Travel Certificate Platform</div>
            </div>
        </footer>
    </div>
</body>
</html>
