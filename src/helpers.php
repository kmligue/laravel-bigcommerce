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
    function get_install_redirect()
    {
        $redirect = config('tenant.install_redirect');
        $redirect = str_replace('{storeHash}', str_replace('stores/', '', tenant()->store_hash), $redirect);

        return $redirect;
    }
}

if (!function_exists('get_load_redirect')) {
    function get_load_redirect()
    {
        $redirect = config('tenant.load_redirect');
        $redirect = str_replace('{storeHash}', str_replace('stores/', '', tenant()->store_hash), $redirect);

        return $redirect;
    }
}