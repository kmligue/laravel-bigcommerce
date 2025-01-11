<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;

class BillingController
{
    public function index(Request $request, $storeHash) {
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        $storeHash = 'stores/' . $storeHash;
        $subscription = $store->subscription('default');

        return view('limonlabs/bigcommerce::billing.index', compact('store', 'storeHash', 'subscription'));
    }

    public function show(Request $request, $storeHash, $plan) {
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        $storeHash = 'stores/' . $storeHash;
        $intent = $store->createSetupIntent();

        return view('limonlabs/bigcommerce::billing.show', compact('store', 'storeHash', 'plan', 'intent'));
    }

    public function store(Request $request, $storeHash, $plan) {
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        try {
            $plans = config('plans');
            $priceId = '';

            if (isset($plans[$plan]) && !empty($plans[$plan])) {
                $priceId = $plans[$plan]['plan_id'];

                $response = $store->newSubscription('default', $priceId)->create($request->paymentMethod, [
                    'email' => $store->user_email
                ]);

                if ($response) {
                    $store->update([
                        'plan' => $plan
                    ]);
                }
            }
        } catch (\Exception $e) {

        }

        $storeHash = 'stores/' . $storeHash;

        return redirect('/' . $storeHash . '/billing');
    }

    public function select(Request $request, $storeHash, $plan) {
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        if ($store->subscription() && $store->subscription()->stripe_status == 'active') {
            $store->subscription('default')->cancelNow();
        }

        $plans = config('plans');
        $priceId = '';

        if (isset($plans[$plan]) && !empty($plans[$plan])) {
            $priceId = $plans[$plan]['plan_id'];

            if ($store->subscription() && $store->hasPaymentMethod()) {
                $paymentMethod = $store->defaultPaymentMethod();

                if (!$paymentMethod) {
                    $paymentMethod = $store->paymentMethods()->first();
                }

                $store->newSubscription('default', $priceId)->create($paymentMethod->id);

                return response()->json([
                    'success' => true
                ]);
            }

            $checkout = $store->newSubscription('default', $priceId)->checkout([
                'cancel_url' => 'https://store-'. $storeHash .'.mybigcommerce.com/manage/app/' . env('BC_APP_ID') . '?action=upgrade&success=false',
                'success_url' => 'https://store-'. $storeHash .'.mybigcommerce.com/manage/app/' . env('BC_APP_ID') . '?action=upgrade&success=true'
            ]);

            return response()->json([
                'success' => true,
                'url' => $checkout->url
            ]);
        }
    }

    public function history(Request $request, $storeHash) {
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        $invoices = $store->invoices();
        $storeHash = 'stores/' . $storeHash;

        return view('limonlabs/bigcommerce::billing.history', compact('store', 'storeHash', 'invoices'));
    }
}
