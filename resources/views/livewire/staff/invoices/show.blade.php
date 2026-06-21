{{-- FILE: resources/views/livewire/staff/invoices/show.blade.php --}}
@php
    $isPaid = $invoice->status === \App\Enums\InvoiceStatus::Paid;
    $isInitiated = $invoice->status === \App\Enums\InvoiceStatus::Initiated;
    $wangovEnabled = (bool) config('services.wangov.enabled', false);
    $canConfirmNraReceipt = auth()->user()?->hasAnyStaffTitle([
        'system_administrator',
        'airport_manager',
        'shift_supervisor',
        'visa_processing_officer',
        'payment_officer',
    ]) ?? false;
    $serviceName = (string) config('services.wangov.external.service_display', 'Sierra Leone Visa Permit');
    $serviceCode = (string) config('services.wangov.external.service_code', '');
    $allowedMethods = trim((string) config('services.wangov.allowed_methods', ''));
@endphp

<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoice</h1>
            <p class="text-sm text-gray-600">{{ $invoice->invoice_no }}</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('documents.invoices.show', $invoice) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                Open PDF
            </a>

            @if (! $isPaid && $wangovEnabled)
                <button
                    type="button"
                    wire:click="startWangovCheckout"
                    wire:loading.attr="disabled"
                    class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-800 disabled:opacity-60"
                >
                    <span wire:loading.remove wire:target="startWangovCheckout">
                        {{ $isInitiated ? 'Refresh WanGov Checkout' : 'Start WanGov Checkout' }}
                    </span>
                    <span wire:loading wire:target="startWangovCheckout">Preparing...</span>
                </button>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl bg-white p-6 shadow lg:col-span-2">
            <h2 class="text-lg font-semibold text-gray-900">Invoice Details</h2>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm text-gray-500">Invoice Number</div>
                    <div class="font-medium text-gray-900">{{ $invoice->invoice_no }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Payment Reference</div>
                    <div class="font-medium text-gray-900">{{ $invoice->payment_reference }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Status</div>
                    <div class="font-medium text-gray-900">{{ strtoupper($invoice->status->value) }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Gateway</div>
                    <div class="font-medium text-gray-900">{{ strtoupper($invoice->gateway) }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Amount</div>
                    <div class="font-medium text-gray-900">{{ number_format((float) $invoice->amount, 2) }} {{ strtoupper($invoice->currency) }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Issued At</div>
                    <div class="font-medium text-gray-900">{{ optional($invoice->issued_at)->format('Y-m-d H:i') ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Expires At</div>
                    <div class="font-medium text-gray-900">{{ optional($invoice->expires_at)->format('Y-m-d H:i') ?: '—' }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Paid At</div>
                    <div class="font-medium text-gray-900">{{ optional($invoice->paid_at)->format('Y-m-d H:i') ?: '—' }}</div>
                </div>
            </div>

            @if (! $isPaid)
                <div class="mt-8 rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-emerald-950">NRA Receipt Payment Confirmation</h2>
                            <p class="mt-1 text-sm leading-6 text-emerald-900">
                                The traveler pays the official fee at the NRA/customs payment desk, returns with the printed receipt, and an authorized officer captures or uploads the receipt evidence before permit processing continues.
                            </p>
                        </div>

                        <div class="rounded-full border border-emerald-300 bg-white px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-800">
                            Receipt Upload
                        </div>
                    </div>

                    @if ($canConfirmNraReceipt)
                        <form wire:submit.prevent="recordNraReceiptPayment" class="mt-5 grid gap-4 lg:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-emerald-950">NRA receipt number</label>
                                <input
                                    type="text"
                                    wire:model.defer="manual_receipt_no"
                                    class="mt-1 w-full rounded-lg border-emerald-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700"
                                    placeholder="Enter the number printed on the receipt"
                                    autocomplete="off"
                                >
                                <p class="mt-1 text-xs text-emerald-800">This number is unique. The system will reject a reused receipt.</p>
                                @error('manual_receipt_no')
                                    <div class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-emerald-950">Receipt image</label>
                                <div class="mt-1 grid gap-2 sm:grid-cols-2">
                                    <label class="flex cursor-pointer items-center justify-center rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm font-semibold text-emerald-900 shadow-sm hover:bg-emerald-50">
                                        Capture with camera
                                        <input
                                            type="file"
                                            wire:model="manual_receipt_image"
                                            accept="image/*"
                                            capture="environment"
                                            class="sr-only"
                                        >
                                    </label>

                                    <label class="flex cursor-pointer items-center justify-center rounded-lg border border-emerald-300 bg-white px-3 py-2 text-sm font-semibold text-emerald-900 shadow-sm hover:bg-emerald-50">
                                        Upload image
                                        <input
                                            type="file"
                                            wire:model="manual_receipt_image"
                                            accept="image/jpeg,image/png,image/webp,image/heic,image/heif,.heic,.heif"
                                            class="sr-only"
                                        >
                                    </label>
                                </div>
                                @if ($manual_receipt_image)
                                    <p class="mt-1 text-xs font-semibold text-emerald-900">
                                        Selected: {{ $manual_receipt_image->getClientOriginalName() }}
                                    </p>
                                @endif
                                <p class="mt-1 text-xs text-emerald-800">Use the camera to snap the NRA receipt or upload a clear receipt image. JPG, PNG, WEBP, HEIC, or HEIF only.</p>
                                @error('manual_receipt_image')
                                    <div class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="lg:col-span-2">
                                <label class="block text-sm font-semibold text-emerald-950">Payment note</label>
                                <textarea
                                    wire:model.defer="manual_receipt_note"
                                    rows="2"
                                    class="mt-1 w-full rounded-lg border-emerald-300 shadow-sm focus:border-emerald-700 focus:ring-emerald-700"
                                    placeholder="Optional note, such as NRA desk, cashier name, or correction reason"
                                ></textarea>
                                @error('manual_receipt_note')
                                    <div class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="flex items-center justify-end lg:col-span-2">
                                <button
                                    type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="recordNraReceiptPayment,manual_receipt_image"
                                    class="rounded-lg bg-emerald-700 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-800 disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="recordNraReceiptPayment,manual_receipt_image">Confirm NRA Receipt Payment</span>
                                    <span wire:loading wire:target="recordNraReceiptPayment,manual_receipt_image">Checking receipt...</span>
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            Only authorized airport or payment officers can confirm payment from an NRA receipt.
                        </div>
                    @endif
                </div>

                <div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="flex-1">
                            <h2 class="text-base font-semibold text-gray-900">WanGov / GovPay Digital Payment</h2>
                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                Alternative payment option for this permit invoice. Use it when the traveler will pay digitally instead of presenting an NRA receipt.
                            </p>

                            @if ($wangovEnabled)
                                <div class="mt-4 max-w-md">
                                    <label class="block text-sm font-semibold text-gray-800">Traveler / payer phone</label>
                                    <input
                                        type="text"
                                        wire:model.defer="payer_phone"
                                        class="mt-1 w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-700 focus:ring-gray-700"
                                        placeholder="+232..."
                                    >
                                    @error('payer_phone')
                                        <div class="mt-1 text-xs font-semibold text-red-700">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif
                        </div>

                        @if ($wangovEnabled)
                            <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                                <button
                                    type="button"
                                    wire:click="startWangovCheckout"
                                    wire:loading.attr="disabled"
                                    class="rounded-lg bg-gray-900 px-4 py-3 text-sm font-bold text-white hover:bg-black disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="startWangovCheckout">
                                        {{ $isInitiated ? 'Refresh WanGov Checkout' : 'Start WanGov Checkout' }}
                                    </span>
                                    <span wire:loading wire:target="startWangovCheckout">Preparing...</span>
                                </button>

                                @if ($isInitiated)
                                    <button
                                        type="button"
                                        class="rounded-lg bg-[#0072c5] px-4 py-3 text-sm font-bold text-white hover:brightness-95"
                                        data-wangov-checkout
                                        data-application-number="{{ $invoice->payment_reference }}"
                                        data-service-name="{{ $serviceName }}"
                                        data-service-code="{{ $serviceCode }}"
                                        data-service-fee="{{ $invoice->currency }} {{ number_format((float) $invoice->amount, 2, '.', '') }}"
                                        @if($allowedMethods !== '') data-allowed-methods="{{ $allowedMethods }}" @endif
                                    >
                                        Pay Digitally
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="rounded-full border border-gray-300 bg-white px-3 py-1 text-xs font-bold uppercase tracking-wide text-gray-600">
                                Not enabled
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <h2 class="mt-8 text-lg font-semibold text-gray-900">Application</h2>

            <div class="mt-4 grid gap-4 md:grid-cols-2">
                <div>
                    <div class="text-sm text-gray-500">Application Number</div>
                    <div class="font-medium text-gray-900">{{ $invoice->visaApplication->application_no }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Point of Entry</div>
                    <div class="font-medium text-gray-900">{{ $invoice->visaApplication->point_of_entry }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Purpose of Visit</div>
                    <div class="font-medium text-gray-900">{{ $invoice->visaApplication->purpose_of_visit }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Period of Stay</div>
                    <div class="font-medium text-gray-900">
                        {{ $invoice->visaApplication->period_of_stay_text ?: $invoice->visaApplication->period_of_stay_days . ' days' }}
                    </div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Airport</div>
                    <div class="font-medium text-gray-900">{{ $invoice->visaApplication->airport->name }}</div>
                </div>

                <div>
                    <div class="text-sm text-gray-500">Desk</div>
                    <div class="font-medium text-gray-900">{{ $invoice->visaApplication->desk?->name ?: '—' }}</div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Traveler</h2>

                <div class="mt-4 space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">Full Name</div>
                        <div class="font-medium text-gray-900">{{ $invoice->visaApplication->passenger->full_name }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Passport Number</div>
                        <div class="font-medium text-gray-900">{{ $invoice->visaApplication->passenger->passport_number }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Nationality</div>
                        <div class="font-medium text-gray-900">{{ $invoice->visaApplication->passenger->nationality }}</div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Passport Expiry</div>
                        <div class="font-medium text-gray-900">
                            {{ optional($invoice->visaApplication->passenger->passport_expiry_date)->format('Y-m-d') ?: '—' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white p-6 shadow">
                <h2 class="text-lg font-semibold text-gray-900">Linked Records</h2>

                <div class="mt-4 space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">Receipt</div>
                        @php $receipt = $invoice->payments->first(fn ($payment) => $payment->receipt !== null)?->receipt; @endphp
                        @if ($receipt)
                            <a href="{{ route('staff.receipts.show', $receipt) }}" class="font-medium text-gray-900 underline">
                                {{ $receipt->receipt_no }}
                            </a>
                            @if ($receipt->receipt_source === 'nra_manual')
                                <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">NRA receipt uploaded</div>
                            @endif
                        @else
                            <div class="font-medium text-gray-900">—</div>
                        @endif
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Permit</div>
                        @if ($invoice->visaApplication->permit && auth()->user()->can('view', $invoice->visaApplication->permit))
                            <a href="{{ route('staff.permits.show', $invoice->visaApplication->permit) }}" class="font-medium text-gray-900 underline">
                                {{ $invoice->visaApplication->permit->permit_no }}
                            </a>
                        @elseif ($invoice->visaApplication->permit)
                            <div class="font-medium text-gray-500">Restricted</div>
                        @else
                            <div class="font-medium text-gray-900">Not issued</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-amber-900">Operational Note</h2>
                <div class="mt-3 text-sm leading-6 text-amber-800">
                    This invoice is an official payment order. A permit must not be issued unless payment is confirmed in the official system or a properly approved waiver exists.
                </div>
            </div>
        </div>
    </div>

    @if ($wangovEnabled)
        <script src="https://cdn.wan.gov.sl/wangov-embed.v1.2.9.js" defer></script>
        <script>
            (function () {
                function bootWanGov() {
                    try { window.WanGov?.checkout?.auto?.(); } catch (_) {}
                }

                document.addEventListener('DOMContentLoaded', bootWanGov);
                document.addEventListener('livewire:navigated', bootWanGov);
                document.addEventListener('wangov-checkout-ready', function (event) {
                    bootWanGov();

                    setTimeout(function () {
                        var reference = event.detail && event.detail.reference;
                        if (!reference) return;

                        var button = document.querySelector('[data-wangov-checkout][data-application-number="' + reference + '"]');
                        if (button) button.click();
                    }, 150);
                });
            })();
        </script>
    @endif
</div>
