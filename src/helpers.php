<?php

if (!function_exists('tenant')) {
    function tenant()
    {
        // get the tenant from the request
        return request()->tenant;
    }
}