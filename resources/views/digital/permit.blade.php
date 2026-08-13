<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Digital Emergency Travel Certificate | SLID</title>
    @include('partials.pwa')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-950 antialiased">
    @php
        use App\Support\PrintableSecurityValue;

        $passenger = $permit->visaApplication?->passenger;
        $paymentReference = $permit->payment?->gateway_transaction_id
            ?: ($permit->payment?->gateway_reference ?: $permit->visaApplication?->latestInvoice?->payment_reference);
        $statusClass = match (strtolower($publicStatus)) {
            'valid' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
            'expired' => 'border-amber-200 bg-amber-50 text-amber-800',
            'revoked', 'cancelled' => 'border-red-200 bg-red-50 text-red-800',
            default => 'border-gray-200 bg-gray-50 text-gray-800',
        };
    @endphp

    <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/slid-logo.png') }}" alt="SLID" class="h-12 w-12 object-contain">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-emerald-800">Sierra Leone Immigration Department</div>
                    <h1 class="text-2xl font-bold tracking-tight">Digital Emergency Travel Certificate</h1>
                </div>
            </div>
            <div class="rounded-md border px-3 py-2 text-sm font-bold {{ $statusClass }}">{{ strtoupper($publicStatus) }}</div>
        </div>

        <section class="overflow-hidden border border-gray-300 bg-white shadow-sm">
            <div class="border-b border-emerald-900 bg-emerald-950 px-5 py-4 text-white">
                <div class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">Official digital ETC</div>
                <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-3xl font-bold">{{ $permit->permit_no }}</div>
                        <div class="mt-1 text-sm text-emerald-50">Verification Code: {{ $permit->verification_code }}</div>
                    </div>
                    <div class="text-sm text-emerald-50">
                        Issued {{ optional($permit->issued_at)->format('Y-m-d H:i') }}<br>
                        Valid until {{ optional($permit->valid_until)->format('Y-m-d') ?: 'N/A' }}
                    </div>
                </div>
            </div>

            <div class="grid gap-0 lg:grid-cols-[1fr_260px]">
                <div class="space-y-5 p-5">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="border border-gray-200 bg-gray-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Traveler</div>
                            <div class="mt-1 text-xl font-bold">{{ $passenger?->full_name ?: 'N/A' }}</div>
                            <div class="mt-1 text-sm text-gray-600">{{ $passenger?->nationality ?: 'N/A' }}</div>
                        </div>
                        <div class="border border-gray-200 bg-gray-50 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Identity</div>
                            <div class="mt-1 text-xl font-bold">{{ $passenger?->passport_number ?: 'N/A' }}</div>
                            <div class="mt-1 text-sm text-gray-600">
                                DOB {{ optional($passenger?->date_of_birth)->format('Y-m-d') ?: 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="border border-gray-200 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Travel Basis</div>
                            <div class="mt-2 text-sm leading-6">
                                <div><span class="font-semibold">Purpose:</span> {{ $permit->visaApplication?->purpose_of_visit ?: 'N/A' }}</div>
                                <div><span class="font-semibold">Destination:</span> {{ $permit->visaApplication?->destination_country ?: 'N/A' }}</div>
                                <div><span class="font-semibold">Point of Entry:</span> {{ $permit->visaApplication?->point_of_entry ?: 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="border border-gray-200 p-4">
                            <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Payment</div>
                            <div class="mt-2 text-sm leading-6">
                                <div><span class="font-semibold">Receipt:</span> {{ $paymentReference ?: 'N/A' }}</div>
                                <div><span class="font-semibold">Amount:</span> {{ $permit->payment ? number_format((float) $permit->payment->amount_paid, 2).' '.strtoupper($permit->payment->currency) : 'N/A' }}</div>
                                <div><span class="font-semibold">Paid:</span> {{ optional($permit->payment?->paid_at)->format('Y-m-d H:i') ?: 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-200 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Security Features</div>
                        <div class="mt-3 grid gap-3 text-sm md:grid-cols-2">
                            <div>
                                <div class="text-gray-500">Security Seal Ref</div>
                                <div class="font-mono font-semibold">{{ PrintableSecurityValue::short($permit->security_seal) }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Document Hash Ref</div>
                                <div class="font-mono font-semibold">{{ PrintableSecurityValue::short($permit->document_hash) }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Issued By</div>
                                <div class="font-semibold">{{ $permit->issuer?->name ?: 'ETC Issuer' }}</div>
                            </div>
                            <div>
                                <div class="text-gray-500">Verification URL</div>
                                <a href="{{ $verificationUrl }}" class="break-all font-semibold text-emerald-800 underline underline-offset-2">{{ $verificationUrl }}</a>
                            </div>
                        </div>
                    </div>

                    <div class="border border-gray-900 bg-gray-50 p-4">
                        <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Machine Readable Zone</div>
                        <div class="mt-2 overflow-x-auto whitespace-nowrap font-mono text-lg tracking-wider text-gray-950">
                            {{ $permit->mrz_line_1 }}<br>
                            {{ $permit->mrz_line_2 }}
                        </div>
                    </div>
                </div>

                <aside class="border-t border-gray-200 bg-gray-50 p-5 lg:border-l lg:border-t-0">
                    @if (!empty($qrImageBase64))
                        <div class="border border-gray-200 bg-white p-4">
                            <img src="{{ $qrImageBase64 }}" alt="Verification QR Code" class="mx-auto h-44 w-44">
                        </div>
                    @endif
                    <a href="{{ $verificationUrl }}" class="mt-4 block rounded-md bg-emerald-700 px-4 py-3 text-center text-sm font-bold text-white hover:bg-emerald-800">
                        Open Verification
                    </a>
                    <button type="button" onclick="window.print()" class="mt-3 w-full rounded-md border border-gray-300 bg-white px-4 py-3 text-sm font-bold text-gray-800 hover:bg-gray-50">
                        Print Digital ETC
                    </button>
                </aside>
            </div>
        </section>
    </main>
</body>
</html>
