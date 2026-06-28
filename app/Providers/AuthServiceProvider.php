<?php

namespace App\Providers;

use App\Models\Permit;
use App\Policies\PermitPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Permit::class => PermitPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
