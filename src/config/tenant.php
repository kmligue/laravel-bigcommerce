<?php

return [
    'tenant' => \Limonlabs\Bigcommerce\Models\StoreInfo::class,
    'frontend_url' => rtrim(env('BIGCOMMERCE_FRONTEND_URL', ''), '/'),
    'install_redirect' => '/stores/{storeHash}/overview', // This is the route where the user is redirected to after installing the app
    'load_redirect' => '/stores/{storeHash}/overview', // This is the route where the user is redirected to after loading the app
    'staticforms_access_key' => env('STATICFORMS_ACCESS_KEY', null),
    'maintenance' => env('MAINTENANCE', false),
    'maintenance_allowed_stores' => env('MAINTENANCE_ALLOWED_STORES', ''),
];