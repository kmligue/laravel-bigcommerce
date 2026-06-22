<?php

use Illuminate\Support\Facades\Route;

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

Route::middleware(['bigcommerce.store.auth'])->group(function () {
    Route::get('stores/{storeHash}/welcome', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'store']);
});

Route::middleware(['bigcommerce.store.auth', 'welcome.auth'])->group(function () {
    Route::get('stores/{storeHash}/overview', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'store']);
    Route::get('stores/{storeHash}/help', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'store']);
    Route::get('stores/{storeHash}/billing', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'store']);
    Route::get('stores/{storeHash}/billing/history', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'store']);
    Route::get('stores/{storeHash}/billing/{plan}', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'store']);

    Route::get('stores/{storeHash}/expired', function () {
        $storeHash = 'stores/' . request()->route('storeHash');

        return view('limonlabs/bigcommerce::expired', compact('storeHash'));
    });
});

Route::middleware(['limonadmin.guest'])->group(function () {
    Route::get('limonadmin', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'admin']);
});

Route::middleware(['limonadmin.auth'])->group(function () {
    Route::get('limonadmin/installs', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'admin']);
    Route::get('limonadmin/unified-billing', [\Limonlabs\Bigcommerce\Controllers\SpaController::class, 'admin']);
});

Route::get('maintenance', [\Limonlabs\Bigcommerce\Controllers\Admin\MaintenanceController::class, 'index']);
