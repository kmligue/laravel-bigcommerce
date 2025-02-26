<?php

return [
    'enabled' => env('ADMINER_ENABLED', true),
    'autologin' => env('ADMINER_AUTO_LOGIN', true),
    'route_prefix' => env('ADMINER_ROUTE_PREFIX', 'adminer'),
    'middleware' => 'adminer',
    'plugins' => [],
];