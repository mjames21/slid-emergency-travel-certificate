
{{-- FILE: resources/views/verify/permit.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Permit Verification</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">
    @php
        $statusClasses = match (strtolower($publicStatus)) {
            'valid' => 'border-green-200 bg-green-50 text-green-800',
            'expired' => 'border-amber-200 bg-amber-50 text-amber-800',
            'revoked', 'replaced' => 'border-red-200 bg-red-50 text-red-800',
            default => 'border-blue-200 bg-blue-50 text-blue-800',
        };
    @endphp

    <div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-white shadow">
            <div class="border-b border-gray-200 px-6 py-5">
                <div class="text-xs font-semibold uppercase tracking-wider text-gray-500">
                    Sierra Leone Immigration Department
                </div>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Permit Verification</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Official public verification result.
                </p>
            </div>

            <div class="space-y-6 px-6 py-6">
                <div class="rounded-xl border px-4 py-4 {{ $statusClasses }}">
                    <div class="text-xs uppercase tracking-wide">Verification Status</div>
                    <div class="mt-1 text-2xl font-bold">{{ $publicStatus }}</div>
                </div>

                @foreach ($notices as $notice)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        {{ $notice }}
                    </div>
                @endforeach

                @if ($searchedPermit->id !== $permit->id)
                    <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                        The verification code matches an older linked permit. The latest linked permit is shown below.
                    </div>
                @endif

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">Searched Permit Number</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">{{ $searchedPermit->permit_no }}</div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">Displayed Permit Number</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">{{ $permit->permit_no }}</div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">Traveler</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $permit->visaApplication->passenger->full_name ?? '—' }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">Passport Number</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $permit->visaApplication->passenger->passport_number ?? '—' }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">Valid Until</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ optional($permit->valid_until)->format('Y-m-d') ?: '—' }}
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 p-4">
                        <div class="text-sm text-gray-500">Issued By</div>
                        <div class="mt-1 font-semibold text-gray-900">
                            {{ $permit->issuer?->name ?: '—' }}
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                    Only permits that verify on this official page should be accepted as valid immigration documents.
                </div>
            </div>
        </div>
    </div>
</body>
</html>