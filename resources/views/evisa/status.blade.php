<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emergency Travel Certificate Status | SLID LEAPS</title>
    @include('partials.pwa')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-950 antialiased">
    @php
        $invoice = $application->latestInvoice;
        $isPaid = $invoice?->status === \App\Enums\InvoiceStatus::Paid;
    @endphp
    <main class="mx-auto max-w-4xl px-5 py-8">
        <div class="border border-gray-200 bg-white p-6 shadow-sm">
            @if (session('success'))
                <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif

            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Emergency Travel Certificate Status</h1>
                    <p class="mt-2 text-sm text-gray-600">{{ $application->public_tracking_code }}</p>
                </div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">{{ str($application->status->value)->replace('_', ' ')->title() }}</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="border border-gray-200 bg-gray-50 p-4">
                    <div class="text-sm text-gray-500">Traveler</div>
                    <div class="mt-1 font-semibold text-gray-950">{{ $application->passenger?->full_name }}</div>
                    <div class="mt-1 text-sm text-gray-600">{{ $application->passenger?->passport_number }}</div>
                </div>
                <div class="border border-gray-200 bg-gray-50 p-4">
                    <div class="text-sm text-gray-500">Payment Reference</div>
                    <div class="mt-1 font-semibold text-gray-950">{{ $invoice?->payment_reference ?: '—' }}</div>
                    <div class="mt-1 text-sm text-gray-600">{{ $invoice?->currency }} {{ number_format((float) $invoice?->amount, 2) }}</div>
                </div>
            </div>

            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                <div class="font-bold">Required flow</div>
                <div class="mt-1">An authorized officer enters the ETC request in the office. The traveler pays the ETC fee through WanGov/GovPay, the ETC Issuer records the receipt number, then issues the official Emergency Travel Certificate.</div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                @if ($invoice && ! $isPaid)
                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                        Payment not recorded. Use the payment reference {{ $invoice->payment_reference }} and record the WanGov/GovPay receipt number on the HQ request screen.
                    </div>
                @elseif (! $application->permit)
                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                        Payment received. Pending ETC Issuer approval and issue.
                    </div>
                @endif

                @if ($application->permit)
                    @can('print', $application->permit)
                        <a href="{{ route('documents.certificates.show', $application->permit) }}" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-bold text-gray-800 hover:bg-gray-50">Print Official ETC</a>
                    @endcan
                    <a href="{{ route('verify.permit', $application->permit->verification_code) }}" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-bold text-gray-800">Verify Issued Certificate</a>
                    <a href="{{ route('digital.certificates.show', $application->permit->verification_code) }}" class="rounded-md bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">Open Digital ETC</a>
                @endif

                <a href="{{ route('hq.emergency-travel-certificates.index') }}" class="rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-bold text-gray-800 hover:bg-gray-50">Back to ETC Requests</a>
            </div>

            <div class="mt-8 border-t border-gray-200 pt-5 text-sm leading-7 text-gray-600">
                The ETC Issuer reviews office-assisted Emergency Travel Certificate requests after WanGov/GovPay payment is confirmed. If approved, the certificate is issued and sent to the traveler email for verification.
            </div>
        </div>
    </main>
</body>
</html>
