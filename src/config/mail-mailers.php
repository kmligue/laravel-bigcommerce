<?php

return [
    'sendgrid-http' => [
        'transport' => 'sendgrid-http',
        'api_url' => env('MAIL_API_URL'),
        'api_key' => env('MAIL_PASSWORD'),
    ],
];