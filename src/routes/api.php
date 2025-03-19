<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['bigcommerce.store.auth'])->group(function() {
    Route::post('api/stores/{storeHash}/billing/{plan}', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'store']);
    Route::post('api/stores/{storeHash}/billing/{plan}/select', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'select']);
    Route::post('api/stores/{storeHash}/billing/trial/change', [\Limonlabs\Bigcommerce\Controllers\BillingController::class, 'trialChange']);
});