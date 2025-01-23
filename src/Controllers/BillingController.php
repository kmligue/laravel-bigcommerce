<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;

class BillingController
{
    public function index(Request $request) {
        $subscription = tenant()->subscription('default');

        return view('limonlabs/bigcommerce::billing.index', compact('subscription'));
    }

    public function show(Request $request, $plan) {
        $intent = tenant()->createSetupIntent();

        return view('limonlabs/bigcommerce::billing.show', compact('plan', 'intent'));
    }

    public function store(Request $request, $storeHash, $plan) {
        try {
            $plans = config('plans');
            $priceId = '';

            if (isset($plans[$plan]) && !empty($plans[$plan])) {
                $priceId = $plans[$plan]['plan_id'];

                $response = tenant()->newSubscription('default', $priceId)->create($request->paymentMethod, [
                    'email' => tenant()->user_email
                ]);

                if ($response) {
                    tenant()->update([
                        'plan' => $plan
                    ]);
                }
            }
        } catch (\Exception $e) {

        }

        return redirect('/billing');
    }

    public function select(Request $request, $plan) {
        if (tenant()->subscription() && tenant()->subscription()->stripe_status == 'active') {
            tenant()->subscription('default')->cancelNow();
        }

        $plans = config('plans');
        $priceId = '';

        if (isset($plans[$plan]) && !empty($plans[$plan])) {
            $priceId = $plans[$plan]['plan_id'];

            if (tenant()->subscription() && tenant()->hasPaymentMethod()) {
                $paymentMethod = tenant()->defaultPaymentMethod();

                if (!$paymentMethod) {
                    $paymentMethod = tenant()->paymentMethods()->first();
                }

                tenant()->newSubscription('default', $priceId)->create($paymentMethod->id);

                return response()->json([
                    'success' => true
                ]);
            }

            $checkout = tenant()->newSubscription('default', $priceId)->checkout([
                'cancel_url' => 'https://store-'. str_replace('stores/', '', tenant('store_hash')) .'.mybigcommerce.com/manage/app/' . env('BC_APP_ID') . '?action=upgrade&success=false',
                'success_url' => 'https://store-'. str_replace('stores/', '', tenant('store_hash')) .'.mybigcommerce.com/manage/app/' . env('BC_APP_ID') . '?action=upgrade&success=true'
            ]);

            return response()->json([
                'success' => true,
                'url' => $checkout->url
            ]);
        }
    }

    public function history(Request $request) {
        $invoices = tenant()->invoices();

        return view('limonlabs/bigcommerce::billing.history', compact('invoices'));
    }
}
