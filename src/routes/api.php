<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('api/csrf-cookie', fn () => response()->noContent());

    Route::get('api/bootstrap', function () {
        if (!auth('store_info')->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $store = auth('store_info')->user();
        $storeHashShort = store_hash_short($store->store_hash);
        $welcomeComplete = $store->internal_settings
            && isset($store->internal_settings['welcome'])
            && $store->internal_settings['welcome'] == 1;

        $path = $welcomeComplete
            ? "/stores/{$storeHashShort}/overview"
            : "/stores/{$storeHashShort}/welcome";

        return response()->json(['redirect' => $path]);
    });
});

Route::middleware(['web', 'bigcommerce.store.auth'])->group(function () {
    Route::post('api/stores/{storeHash}/welcome', [\Limonlabs\Bigcommerce\Http\Controllers\Api\WelcomeApiController::class, 'store']);
    Route::post('api/stores/{storeHash}/billing/cancel', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'cancel']);
    Route::post('api/stores/{storeHash}/billing/trial/change', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'trialChange']);
    Route::post('api/stores/{storeHash}/billing/{plan}/select', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'select']);
    Route::post('api/stores/{storeHash}/billing/{plan}', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'store']);
});

Route::middleware(['web', 'bigcommerce.store.auth', 'welcome.auth'])->group(function () {
    Route::get('api/stores/{storeHash}/context', [\Limonlabs\Bigcommerce\Http\Controllers\Api\StoreContextController::class, 'show']);
    Route::post('api/stores/{storeHash}/help', [\Limonlabs\Bigcommerce\Http\Controllers\Api\HelpApiController::class, 'store']);
    Route::get('api/stores/{storeHash}/billing', [\Limonlabs\Bigcommerce\Http\Controllers\Api\BillingApiController::class, 'index']);
    Route::get('api/stores/{storeHash}/billing/history', [\Limonlabs\Bigcommerce\Http\Controllers\Api\BillingApiController::class, 'history']);
    Route::get('api/stores/{storeHash}/billing/{plan}/setup-intent', [\Limonlabs\Bigcommerce\Http\Controllers\Api\BillingApiController::class, 'setupIntent']);
});

Route::middleware(['web'])->group(function () {
    Route::post('api/limonadmin/login', [\Limonlabs\Bigcommerce\Http\Controllers\Api\LimonAdminApiController::class, 'login']);
});

Route::middleware(['web', 'limonadmin.auth'])->group(function () {
    Route::get('api/limonadmin/installs', [\Limonlabs\Bigcommerce\Http\Controllers\Api\LimonAdminApiController::class, 'installs']);
    Route::post('api/limonadmin/unified-billing/create', [\Limonlabs\Bigcommerce\Http\Controllers\Api\LimonAdminApiController::class, 'createUnifiedBilling']);
});
