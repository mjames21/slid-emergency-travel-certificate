<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Permit;
use App\Models\Receipt;
use App\Models\VisaApplication;
use App\Policies\InvoicePolicy;
use App\Policies\PermitPolicy;
use App\Policies\ReceiptPolicy;
use App\Policies\VisaApplicationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        VisaApplication::class => VisaApplicationPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Permit::class => PermitPolicy::class,
        Receipt::class => ReceiptPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
