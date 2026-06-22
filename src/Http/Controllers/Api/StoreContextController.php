<?php

namespace Limonlabs\Bigcommerce\Http\Controllers\Api;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Services\BillingService;

class StoreContextController
{
    public function __construct(protected BillingService $billingService)
    {
    }

    public function show(Request $request, string $storeHash)
    {
        $tenant = tenant();

        return response()->json([
            'app_name' => config('app.name'),
            'store_hash' => $tenant->store_hash,
            'store_hash_short' => str_replace('stores/', '', $tenant->store_hash),
            'plan_status' => $this->billingService->serializePlanStatus($tenant->getPlanStatus()),
            'trial_notice' => $this->billingService->buildTrialNoticeData($tenant),
            'highest_plan' => get_highest_available_plan(),
            'lowest_plan' => get_lowest_available_plan(),
        ]);
    }
}
