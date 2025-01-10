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

Route::middleware(['bigcommerce.store.auth'])->group(function() {
    Route::get('stores/{storeHash}/overview', [\Limonlabs\Bigcommerce\Controllers\OverviewController::class, 'index']);

    Route::get('stores/{storeHash}/help', [\Limonlabs\Bigcommerce\Controllers\HelpController::class, 'index']);
});