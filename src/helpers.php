<?php

if (!function_exists('tenant')) {
    function tenant()
    {
        // get the tenant from the request
        return request()->tenant;
    }
}

if (!function_exists('tenant_class')) {
    function tenant_class()
    {
        return config('tenant.tenant');
    }
}

if (!function_exists('get_install_redirect')) {
    function get_install_redirect($storeHash = '')
    {
        $redirect = config('tenant.install_redirect');

        if (empty($storeHash)) {
            $storeHash = str_replace('stores/', '', tenant()->store_hash);
        } else {
            $storeHash = str_replace('stores/', '', $storeHash);
        }

        $redirect = str_replace('{storeHash}', $storeHash, $redirect);

        return $redirect;
    }
}

if (!function_exists('get_load_redirect')) {
    function get_load_redirect($storeHash = '')
    {
        $redirect = config('tenant.load_redirect');

        if (empty($storeHash)) {
            $storeHash = str_replace('stores/', '', tenant()->store_hash);
        } else {
            $storeHash = str_replace('stores/', '', $storeHash);
        }

        $redirect = str_replace('{storeHash}', $storeHash, $redirect);

        return $redirect;
    }
}

if (!function_exists('is_maintenance')) {
    function is_maintenance() {
        return config('tenant.maintenance');
    }
}

if (!function_exists('is_maintenance_allowed')) {
    function is_maintenance_allowed($storeHash) {
        $allowed_stores = config('tenant.maintenance_allowed_stores');
        $allowed_stores = array_map('trim', explode(',', $allowed_stores));

        return in_array(str_replace('stores/', '', $storeHash), $allowed_stores);
    }
}

if (!function_exists('get_stores_in_trial_period')) {
    function get_stores_in_trial_period() {
        return tenant_class()::where('trial_ends_at', '>=', now())->get();
    }
}

if (!function_exists('get_stores_in_expired_trial')) {
    function get_stores_in_expired_trial($days = 1) {
        return tenant_class()::where('trial_ends_at', '<=', now()->subDays($days))->get();
    }
}

if (!function_exists('get_stores_uninstalled_app')) {
    function get_stores_uninstalled_app($days = 1) {
        return tenant_class()::where('deleted_at', '<=', now()->subDays($days))
                            ->onlyTrashed()
                            ->orderBy('deleted_at', 'desc')
                            ->get();
    }
}

if (!function_exists('get_subscribed_stores')) {
    function get_subscribed_stores() {
        return tenant_class()::query()
                            ->whereHas('subscriptions', function($query) {
                                $query->active();
                            })
                            ->get();
    }
}

if (!function_exists('get_lowest_available_plan')) {
    function get_lowest_available_plan() {
        $plans = config('plans');
        
        // Filter only plans that are shown/available
        $availablePlans = array_filter($plans, function($plan) {
            return isset($plan['show']) && $plan['show'] === true;
        });
        
        // If no plans are available, return null
        if (empty($availablePlans)) {
            return null;
        }
        
        // Sort by price
        uasort($availablePlans, function($a, $b) {
            return ($a['price'] ?? PHP_INT_MAX) <=> ($b['price'] ?? PHP_INT_MAX);
        });
        
        // Return the key of the lowest-priced plan
        return key($availablePlans);
    }
}