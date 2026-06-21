<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emergency Travel Certificate Status | SLID LEAPS</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <script src="https://cdn.wan.gov.sl/wangov-embed.v1.2.9.js" defer></script>
</head>
<body class="bg-gray-50 text-gray-950 antialiased">
    <?php
        $invoice = $application->latestInvoice;
        $isPaid = $invoice?->status === \App\Enums\InvoiceStatus::Paid;
        $isInitiated = $invoice?->status === \App\Enums\InvoiceStatus::Initiated;
        $serviceName = (string) config('services.wangov.external.service_display', 'Sierra Leone Emergency Travel Certificate');
        $serviceCode = (string) config('services.wangov.external.service_code', '');
        $allowedMethods = trim((string) config('services.wangov.allowed_methods', ''));
    ?>
    <main class="mx-auto max-w-4xl px-5 py-8">
        <div class="border border-gray-200 bg-white p-6 shadow-sm">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
                <div class="mb-5 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"><?php echo e(session('success')); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Emergency Travel Certificate Status</h1>
                    <p class="mt-2 text-sm text-gray-600"><?php echo e($application->public_tracking_code); ?></p>
                </div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold uppercase text-gray-700"><?php echo e($application->status->value); ?></span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="border border-gray-200 bg-gray-50 p-4">
                    <div class="text-sm text-gray-500">Applicant</div>
                    <div class="mt-1 font-semibold text-gray-950"><?php echo e($application->passenger?->full_name); ?></div>
                    <div class="mt-1 text-sm text-gray-600"><?php echo e($application->passenger?->passport_number); ?></div>
                </div>
                <div class="border border-gray-200 bg-gray-50 p-4">
                    <div class="text-sm text-gray-500">Payment Reference</div>
                    <div class="mt-1 font-semibold text-gray-950"><?php echo e($invoice?->payment_reference ?: '—'); ?></div>
                    <div class="mt-1 text-sm text-gray-600"><?php echo e($invoice?->currency); ?> <?php echo e(number_format((float) $invoice?->amount, 2)); ?></div>
                </div>
            </div>

            <div class="mt-6 rounded-md border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900">
                <div class="font-bold">Required flow</div>
                <div class="mt-1">Apply online, pay the ETC fee through WanGov/GovPay, then wait for HQ approval. NRA receipt upload is not used for ETC applications. If approved, the official Emergency Travel Certificate is emailed to you.</div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($invoice && ! $isPaid): ?>
                    <form method="POST" action="<?php echo e(route('etc.pay', $application->public_access_token)); ?>">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="rounded-md bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                            <?php echo e($isInitiated ? 'Refresh Payment Checkout' : 'Start Certificate Payment'); ?>

                        </button>
                    </form>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isInitiated): ?>
                        <button
                            type="button"
                            class="rounded-md bg-[#0072c5] px-5 py-3 text-sm font-bold text-white hover:brightness-95"
                            data-wangov-checkout
                            data-application-number="<?php echo e($invoice->payment_reference); ?>"
                            data-service-name="<?php echo e($serviceName); ?>"
                            data-service-code="<?php echo e($serviceCode); ?>"
                            data-service-fee="<?php echo e($invoice->currency); ?> <?php echo e(number_format((float) $invoice->amount, 2, '.', '')); ?>"
                            <?php if($allowedMethods !== ''): ?> data-allowed-methods="<?php echo e($allowedMethods); ?>" <?php endif; ?>
                        >
                            Pay Certificate Application Fee
                        </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php elseif(! $application->permit): ?>
                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900">
                        Payment received. Pending HQ approval.
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($application->permit): ?>
                    <a href="<?php echo e(route('verify.permit', $application->permit->verification_code)); ?>" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-bold text-gray-800">Verify Issued Certificate</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="mt-8 border-t border-gray-200 pt-5 text-sm leading-7 text-gray-600">
                HQ reviews Emergency Travel Certificate applications after WanGov/GovPay payment is confirmed. If approved, the certificate is issued and sent to the applicant email. Airport officers can verify it and complete admissibility screening on arrival.
            </div>
        </div>
    </main>
    <script>
        (function () {
            function bootWanGov() {
                try { window.WanGov?.checkout?.auto?.(); } catch (_) {}
            }

            document.addEventListener('DOMContentLoaded', function () {
                bootWanGov();

                var reference = <?php echo json_encode(session('auto_checkout_reference'), 15, 512) ?>;
                if (!reference) return;

                var button = document.querySelector('[data-wangov-checkout][data-application-number="' + reference + '"]');
                if (button) button.click();
            });
        })();
    </script>
</body>
</html>
<?php /**PATH /Users/mohamedjames/Documents/SLID/slid-emergency-travel-certificate/resources/views/evisa/status.blade.php ENDPATH**/ ?>