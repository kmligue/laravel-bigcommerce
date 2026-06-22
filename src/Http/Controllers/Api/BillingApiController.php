<?php

namespace Limonlabs\Bigcommerce\Http\Controllers\Api;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Services\BillingService;

class BillingApiController
{
    public function __construct(protected BillingService $billingService)
    {
    }

    public function index(Request $request, string $storeHash)
    {
        return response()->json($this->billingService->buildBillingIndexData(tenant()));
    }

    public function history(Request $request, string $storeHash)
    {
        $invoices = tenant()->invoices();

        $items = collect($invoices)->map(function ($invoice) {
            $description = '';

            foreach ($invoice->subscriptions() as $subscription) {
                $details = $subscription->toArray();
                $description = $details['description'] ?? '';
            }

            return [
                'date' => $invoice->date()->toISOString(),
                'description' => $description,
            ];
        })->values();

        return response()->json(['invoices' => $items]);
    }

    public function setupIntent(Request $request, string $storeHash, string $plan)
    {
        $intent = tenant()->createSetupIntent();

        return response()->json([
            'client_secret' => $intent->client_secret,
            'plan' => $plan,
        ]);
    }
}
