<?php

namespace Limonlabs\Bigcommerce\Http\Controllers\Api;

use Illuminate\Http\Request;

class LimonAdminApiController
{
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $adminPassword = config('limonadmin.password');

        if ($request->password === $adminPassword) {
            $request->session()->put('limonadmin', true);

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid password',
        ], 422);
    }

    public function installs(Request $request)
    {
        $plans = config('plans', []);
        $stores = tenant_class()::get();

        $items = $stores->map(function ($store) use ($plans) {
            $planStatus = $store->getPlanStatus();
            $subscription = $store->subscription('default');
            $planLabel = '-';
            $planPrice = null;

            if ($store->plan) {
                foreach ($plans as $key => $plan) {
                    if (($store->plan['plan_id'] ?? '') == ($plan['plan_id'] ?? '')) {
                        $planLabel = ucfirst($key);
                        $planPrice = $plan['price'] ?? 0;
                        break;
                    }
                }
            }

            $discount = 'None';
            if (($planStatus['is_subscribed'] ?? false) && ($planStatus['current_plan'] ?? '') !== 'free') {
                if ($subscription && method_exists($subscription, 'discount') && $subscription->discount()) {
                    $discount = ($subscription->discount()->amount_off / 100) . '% off';
                }
            }

            $name = trim(($store->first_name ?? '') . ' ' . ($store->last_name ?? ''));
            if (empty($name)) {
                $name = $store->user_email;
            }

            return [
                'store_hash' => $store->store_hash,
                'store_hash_short' => str_replace('stores/', '', $store->store_hash),
                'load_url' => get_load_redirect($store->store_hash),
                'name' => $store->name,
                'user_name' => $name,
                'user_email' => $store->user_email,
                'plan_label' => $planLabel,
                'plan_price' => $planPrice,
                'plan_id' => $store->plan['plan_id'] ?? null,
                'install_date' => $store->created_at?->toISOString(),
                'trial_ends_at' => $store->trial_ends_at?->toISOString(),
                'on_trial' => $store->onTrial(),
                'metadata' => [
                    'name' => $store->name,
                    'user_id' => $store->user_id,
                    'secure_url' => $store->secure_url,
                    'user_email' => $store->user_email,
                    'timezone' => $store->timezone,
                    'status' => $store->status,
                    'country' => $store->country,
                    'plan_level' => $store->plan_level,
                    'multi_storefront_enabled' => (bool) $store->multi_storefront_enabled,
                    'discount' => $discount,
                ],
                'plan_options' => collect($plans)->map(fn ($plan, $key) => [
                    'key' => $key,
                    'label' => ucfirst($key),
                    'plan_id' => $plan['plan_id'] ?? '',
                ])->values(),
            ];
        })->values();

        return response()->json([
            'app_name' => config('app.name'),
            'stores' => $items,
            'plans' => collect($plans)->map(fn ($plan, $key) => [
                'key' => $key,
                'label' => ucfirst($key),
                'plan_id' => $plan['plan_id'] ?? '',
            ])->values(),
        ]);
    }

    public function createUnifiedBilling(Request $request)
    {
        $controller = new \Limonlabs\Bigcommerce\Controllers\Admin\UnifiedBillingController();

        return response()->json($controller->store($request));
    }
}
