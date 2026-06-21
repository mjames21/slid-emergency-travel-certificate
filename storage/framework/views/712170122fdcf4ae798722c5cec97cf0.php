<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'SLID Emergency Travel Certificate')); ?></title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>
    <body class="bg-gray-100 font-sans antialiased">
        <?php ($user = auth()->user()); ?>

        <div class="min-h-screen">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <div class="flex min-h-screen">
                    <aside class="hidden w-72 flex-col border-r border-emerald-900 bg-emerald-950 text-white md:flex">
                        <div class="border-b border-emerald-900 px-6 py-5">
                            <div class="text-xs uppercase tracking-wider text-emerald-200">Sierra Leone Immigration Department</div>
                            <div class="mt-1 text-lg font-bold">Emergency Travel Certificate</div>
                        </div>

                        <div class="px-4 py-4">
                            <div class="rounded-lg bg-emerald-900 px-4 py-3">
                                <div class="text-xs uppercase tracking-wide text-emerald-200">Signed in as</div>
                                <div class="mt-1 font-semibold"><?php echo e($user->name); ?></div>
                                <div class="text-sm text-emerald-100"><?php echo e($user->job_title ?: $user->email); ?></div>
                            </div>
                        </div>

                        <nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-6">
                            <div class="px-4 text-xs font-semibold uppercase tracking-wider text-emerald-300">HQ Review</div>

                            <a href="<?php echo e(route('hq.emergency-travel-certificates.index')); ?>"
                               class="<?php echo e(request()->routeIs('hq.emergency-travel-certificates.*') || request()->routeIs('dashboard') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white'); ?> block rounded-lg px-4 py-3 text-sm font-medium">
                                ETC Applications
                            </a>

                            <a href="<?php echo e(route('etc.apply')); ?>"
                               class="block rounded-lg px-4 py-3 text-sm font-medium text-emerald-100 hover:bg-emerald-900 hover:text-white">
                                Public Application
                            </a>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user?->hasStaffTitle('system_administrator')): ?>
                                <div class="px-4 pt-4 text-xs font-semibold uppercase tracking-wider text-emerald-300">Administration</div>

                                <a href="<?php echo e(route('admin.staff.users.index')); ?>"
                                   class="<?php echo e(request()->routeIs('admin.staff.users.*') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white'); ?> block rounded-lg px-4 py-3 text-sm font-medium">
                                    Staff Users
                                </a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <a href="<?php echo e(route('profile.show')); ?>"
                               class="<?php echo e(request()->routeIs('profile.show') ? 'bg-emerald-800 text-white' : 'text-emerald-100 hover:bg-emerald-900 hover:text-white'); ?> block rounded-lg px-4 py-3 text-sm font-medium">
                                Profile And MFA
                            </a>
                        </nav>

                        <div class="border-t border-emerald-900 p-3">
                            <form method="POST" action="<?php echo e(route('logout')); ?>">
                                <?php echo csrf_field(); ?>
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
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                                        <?php echo e($header); ?>

                                    <?php else: ?>
                                        <h1 class="text-lg font-semibold text-gray-900">Emergency Travel Certificate</h1>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="hidden text-right sm:block">
                                    <div class="text-sm font-medium text-gray-900"><?php echo e($user->name); ?></div>
                                    <div class="text-xs text-gray-500">HQ review access</div>
                                </div>
                            </div>
                        </header>

                        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                            <?php echo e($slot); ?>

                        </main>
                    </div>
                </div>
            <?php else: ?>
                <div class="min-h-screen bg-gray-100">
                    <?php echo e($slot); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php echo $__env->yieldPushContent('modals'); ?>
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH /Users/mohamedjames/Documents/SLID/slid-emergency-travel-certificate/resources/views/components/layouts/app.blade.php ENDPATH**/ ?>