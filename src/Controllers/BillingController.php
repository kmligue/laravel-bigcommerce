<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;

class BillingController
{
    public function index(Request $request, $storeHash) {
        $storeHash = 'stores/' . $storeHash;
        $subscription = tenant()->subscription('default');

        return view('limonlabs/bigcommerce::billing.index', compact('storeHash', 'subscription'));
    }

    public function show(Request $request, $storeHash, $plan) {
        $storeHash = 'stores/' . $storeHash;
        $intent = tenant()->createSetupIntent();

        return view('limonlabs/bigcommerce::billing.show', compact('storeHash', 'plan', 'intent'));
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

                // if ($response) {
                //     tenant()->update([
                //         'plan' => $plan
                //     ]);
                // }
            }
        } catch (\Exception $e) {

        }

        $storeHash = 'stores/' . $storeHash;

        return redirect('/' . $storeHash . '/billing');
    }

    public function select(Request $request, $storeHash, $plan) {
        if (tenant()->subscription() && tenant()->subscription()->stripe_status == 'active') {
            tenant()->subscription('default')->cancelNow();
        }

        if ($plan == 'free' || $plan == '') {
            return response()->json([
                'success' => true
            ]);
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
                'cancel_url' => 'https://store-'. $storeHash .'.mybigcommerce.com/manage/app/' . config('bigcommerce.bc_app_id') . '?action=upgrade&success=false',
                'success_url' => 'https://store-'. $storeHash .'.mybigcommerce.com/manage/app/' . config('bigcommerce.bc_app_id') . '?action=upgrade&success=true',
                'allow_promotion_codes' => true
            ]);

            return response()->json([
                'success' => true,
                'url' => $checkout->url
            ]);
        }
    }

    public function trialChange(Request $request, $storeHash) {
        $storeHash = 'stores/' . $storeHash;
        $store = tenant_class()::where('store_hash', $storeHash)->first();

        if ($store) {
            $date = $request->trial;
            
            $store->update([
                'trial_ends_at' => $date
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }

    public function history(Request $request, $storeHash) {
        $invoices = tenant()->invoices();
        $storeHash = 'stores/' . $storeHash;

        return view('limonlabs/bigcommerce::billing.history', compact('storeHash', 'invoices'));
    }

    /**
     * Cancel the user's active subscription
     *
     * @param Request $request
     * @param string $storeHash
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request, $storeHash) {
        try {
            // Get active subscription
            $subscription = tenant()->subscription('default');
            
            if ($subscription && $subscription->active()) {
                // Cancel at period end to allow usage until current billing period ends
                if ($request->has('end_of_period') && $request->end_of_period) {
                    $subscription->cancelAtEndOfPeriod();
                } else {
                    // Cancel immediately
                    $subscription->cancelNow();
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription canceled successfully',
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription: ' . $e->getMessage(),
            ]);
        }
    }
}
