<?php

$frontendUrl = rtrim(env('BIGCOMMERCE_FRONTEND_URL', ''), '/');

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $frontendUrl !== '' ? [$frontendUrl] : [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
