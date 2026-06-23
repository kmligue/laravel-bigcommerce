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

if (!function_exists('frontend_url')) {
    function frontend_url(string $path = ''): string
    {
        $base = rtrim(config('tenant.frontend_url'), '/');

        if ($path === '') {
            return $base;
        }

        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('api_or_frontend_redirect')) {
    /**
     * Return JSON with a client-side redirect path for API requests, or an HTTP redirect for web.
     */
    function api_or_frontend_redirect(\Illuminate\Http\Request $request, string $storeHash, string $page)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            $path = '/stores/' . store_hash_short($storeHash) . '/' . ltrim($page, '/');

            return response()->json(['redirect' => $path], 403);
        }

        return redirect(store_frontend_url($storeHash, $page));
    }
}

if (!function_exists('store_hash_short')) {
    function store_hash_short(string $storeHash): string
    {
        return str_replace('stores/', '', $storeHash);
    }
}

if (!function_exists('store_frontend_url')) {
    function store_frontend_url(string $storeHash, string $path): string
    {
        return frontend_url('stores/' . store_hash_short($storeHash) . '/' . ltrim($path, '/'));
    }
}

if (!function_exists('resolve_load_redirect_url')) {
    /**
     * Map a BigCommerce JWT url (or "/") to an absolute frontend URL.
     */
    function resolve_load_redirect_url(string $url, string $storeHash, array $params = [], $storeInfo = null): string
    {
        if ($storeInfo === null) {
            $storeInfo = tenant_class()::where('store_hash', $storeHash)->first();
        }

        $url = $url === '' ? '/' : $url;
        $frontendBase = rtrim(config('tenant.frontend_url'), '/');

        if (str_starts_with($url, 'http')) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $url = $path;
        }

        if ($url === '/') {
            $welcomeComplete = $storeInfo
                && isset($storeInfo->internal_settings['welcome'])
                && $storeInfo->internal_settings['welcome'] == 1;

            $target = $welcomeComplete
                ? get_load_redirect($storeHash)
                : store_frontend_url($storeHash, 'welcome');
        } elseif (str_starts_with($url, $frontendBase)) {
            $target = $url;
        } elseif (str_starts_with($url, '/stores/')) {
            $target = frontend_url(ltrim($url, '/'));
        } elseif (str_starts_with($url, '/')) {
            $segment = ltrim($url, '/');
            $known = ['welcome', 'expired', 'overview', 'help', 'billing'];
            $isKnown = in_array($segment, $known, true) || str_starts_with($segment, 'billing/');
            $target = $isKnown ? store_frontend_url($storeHash, $segment) : get_load_redirect($storeHash);
        } else {
            $target = get_load_redirect($storeHash);
        }

        if (!empty($params)) {
            $target .= (str_contains($target, '?') ? '&' : '?') . http_build_query($params);
        }

        return $target;
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

        return frontend_url($redirect);
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

        return frontend_url($redirect);
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

if (!function_exists('get_highest_available_plan')) {
    function get_highest_available_plan() {
        $plans = config('plans');
        
        // Filter only plans that are shown/available
        $availablePlans = array_filter($plans, function($plan) {
            return isset($plan['show']) && $plan['show'] === true;
        });
        
        // If no plans are available, return null
        if (empty($availablePlans)) {
            return null;
        }
        
        // Sort by price in descending order
        uasort($availablePlans, function($a, $b) {
            return ($b['price'] ?? PHP_INT_MIN) <=> ($a['price'] ?? PHP_INT_MIN);
        });
        
        // Return the key of the highest-priced plan
        return key($availablePlans);
    }
}