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