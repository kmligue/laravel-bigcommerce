<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;

class BillingController
{
    public function store(Request $request, $storeHash, $plan) {
        try {
            $plans = config('plans');
            $priceId = '';

            if (isset($plans[$plan]) && !empty($plans[$plan])) {
                $planId = $plans[$plan]['plan_id'];

                if (strpos($planId, 'prod_') === 0) {
                    $stripe = new StripeClient(config('services.stripe.secret'));
                    $prices = $stripe->prices->all(['product' => $planId, 'active' => true, 'limit' => 1]);

                    if (count($prices->data) > 0) {
                        $priceId = $prices->data[0]->id;
                    } else {
                        throw new \Exception('No active prices found for this product.');
                    }
                } else {
                    $priceId = $planId;
                }

                tenant()->newSubscription('default', $priceId)->create($request->paymentMethod, [
                    'email' => tenant()->user_email,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json(['success' => true]);
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
            $planId = $plans[$plan]['plan_id'];
            
            // Check if this is a product ID (starts with prod_) or price ID (starts with price_)
            if (strpos($planId, 'prod_') === 0) {
                // It's a product ID, we need to get the first price
                $stripe = new StripeClient(config('services.stripe.secret'));
                $prices = $stripe->prices->all(['product' => $planId, 'active' => true, 'limit' => 1]);
                
                if (count($prices->data) > 0) {
                    $priceId = $prices->data[0]->id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active prices found for this product.'
                    ]);
                }
            } else {
                // It's already a price ID
                $priceId = $planId;
            }

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
            'success' => true,
            'message' => 'Trial updated successfully',
        ]);
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
