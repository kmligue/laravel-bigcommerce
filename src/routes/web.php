<?php

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::get('/error', function () {
    return view('error');
});

Route::group(['prefix' => 'auth'], function () {
    Route::get('install', [\Limonlabs\Bigcommerce\Controllers\BigcommerceController::class, 'install']);

    Route::get('load', [\Limonlabs\Bigcommerce\Controllers\BigcommerceController::class, 'load']);

    Route::get('uninstall', [\Limonlabs\Bigcommerce\Controllers\BigcommerceController::class, 'uninstall']);

    Route::get('remove-user', function () {
        echo 'remove-user';
        return app()->version();
    });
});

Route::any('/bc-api/{endpoint}', [\Limonlabs\Bigcommerce\Controllers\BigcommerceController::class, 'proxyBigCommerceAPIRequest'])
    ->where('endpoint', 'v2\/.*|v3\/.*');

Route::group([
    'prefix' => '/{tenant}',
    'middleware' => [
        'web',
        InitializeTenancyByPath::class,
    ]
    ], function() {
        Route::get('overview', [\Limonlabs\Bigcommerce\Controllers\OverviewController::class, 'index'])->name('overview');

        Route::get('help', [\Limonlabs\Bigcommerce\Controllers\HelpController::class, 'index']);

        Route::get('billing', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'index'])->name('billing');
        Route::get('billing/history', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'history']);
        Route::get('billing/{plan}', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'show']);
    });

// Route::middleware(['bigcommerce.store.auth'])->group(function() {
//     Route::get('stores/{storeHash}/overview', [\Limonlabs\Bigcommerce\Controllers\OverviewController::class, 'index']);

//     Route::get('stores/{storeHash}/help', [\Limonlabs\Bigcommerce\Controllers\HelpController::class, 'index']);

//     Route::get('stores/{storeHash}/billing', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'index'])->name('billing');
//     Route::get('stores/{storeHash}/billing/history', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'history']);
//     Route::get('stores/{storeHash}/billing/{plan}', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'show']);
// });