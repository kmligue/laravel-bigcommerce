<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::post('api/billing/{plan}', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'store']);
    Route::post('api/billing/{plan}/select', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'select']);
});