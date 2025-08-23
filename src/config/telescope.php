<?php

use Laravel\Telescope\Http\Middleware\Authorize;
use Laravel\Telescope\Watchers;

return [

    /*
    |--------------------------------------------------------------------------
    | Telescope Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Telescope will be accessible from. If the
    | setting is null, Telescope will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => env('TELESCOPE_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Telescope Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Telescope will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of the API.
    |
    */

    'path' => env('TELESCOPE_PATH', 'telescope'),

    /*
    |--------------------------------------------------------------------------
    | Telescope Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration options determines the storage driver that will
    | be used to store Telescope's data. In addition, you may set any
    | custom options as needed by the particular driver you choose.
    |
    */

    'driver' => env('TELESCOPE_DRIVER', 'database'),

    'storage' => [
        'database' => [
            'connection' => env('DB_CONNECTION', 'mysql'),
            'chunk' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all Telescope watchers regardless
    | of their individual configuration, which simply provides a single
    | and convenient way to enable or disable Telescope data storage.
    |
    */

    'enabled' => env('TELESCOPE_ENABLED', env('BIGCOMMERCE_ENABLE_TELESCOPE', false)),

    /*
    |--------------------------------------------------------------------------
    | Telescope Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Telescope route - giving you
    | the chance to add your own middleware to this stack or override any
    | of the existing middleware. Or, you can just stick with this stack.
    |
    */

    'middleware' => [
        'web',
        // Custom authorization handled by BigCommerce package
        Authorize::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed / Ignored Paths & Commands
    |--------------------------------------------------------------------------
    |
    | The following array lists the URI paths and Artisan commands that will
    | not be watched by Telescope. In addition to this list, some Laravel
    | commands, like migrations and queue commands, are always ignored.
    |
    */

    'ignore_paths' => [
        'nova-api*',
        'horizon*',
        'telescope*',
        'adminer*',
    ],

    'ignore_commands' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Watchers
    |--------------------------------------------------------------------------
    |
    | The following array lists the "watchers" that will be registered with
    | Telescope. The watchers gather the application's profile data and
    | can display it in the Telescope UI.
    |
    */

    'watchers' => [
        Watchers\CacheWatcher::class => [
            'enabled' => env('TELESCOPE_CACHE_WATCHER', true),
            'ignore' => [],
        ],

        Watchers\CommandWatcher::class => [
            'enabled' => env('TELESCOPE_COMMAND_WATCHER', true),
            'ignore' => [
                'migrate',
                'migrate:fresh',
                'migrate:refresh',
                'migrate:rollback',
                'migrate:status',
                'queue:work',
                'queue:listen',
                'queue:restart',
                'schedule:run',
                'schedule:list',
                'schedule:test',
            ],
        ],

        Watchers\DumpWatcher::class => [
            'enabled' => env('TELESCOPE_DUMP_WATCHER', true),
        ],

        Watchers\EventWatcher::class => [
            'enabled' => env('TELESCOPE_EVENT_WATCHER', true),
        ],

        Watchers\ExceptionWatcher::class => [
            'enabled' => env('TELESCOPE_EXCEPTION_WATCHER', true),
        ],

        Watchers\GateWatcher::class => [
            'enabled' => env('TELESCOPE_GATE_WATCHER', true),
        ],

        Watchers\JobWatcher::class => [
            'enabled' => env('TELESCOPE_JOB_WATCHER', true),
        ],

        Watchers\LogWatcher::class => [
            'enabled' => env('TELESCOPE_LOG_WATCHER', true),
        ],

        Watchers\MailWatcher::class => [
            'enabled' => env('TELESCOPE_MAIL_WATCHER', true),
        ],

        Watchers\ModelWatcher::class => [
            'enabled' => env('TELESCOPE_MODEL_WATCHER', true),
            'ignore' => [
                // Ignore sensitive BigCommerce data
                'password', 'token', 'secret', 'api_key', 'webhook_secret',
                'stripe_id', 'stripe_secret', 'stripe_webhook_secret',
            ],
        ],

        Watchers\NotificationWatcher::class => [
            'enabled' => env('TELESCOPE_NOTIFICATION_WATCHER', true),
        ],

        Watchers\QueryWatcher::class => [
            'enabled' => env('TELESCOPE_QUERY_WATCHER', true),
            'slow' => env('TELESCOPE_QUERY_SLOW', 100),
            'ignore' => [
                // Ignore sensitive BigCommerce data
                'password', 'token', 'secret', 'api_key', 'webhook_secret',
                'stripe_id', 'stripe_secret', 'stripe_webhook_secret',
            ],
        ],

        Watchers\RedisWatcher::class => [
            'enabled' => env('TELESCOPE_REDIS_WATCHER', true),
        ],

        Watchers\RequestWatcher::class => [
            'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
            'size_limit' => env('TELESCOPE_REQUEST_SIZE_LIMIT', 64),
        ],

        Watchers\ClientRequestWatcher::class => [
            'enabled' => env('TELESCOPE_HTTP_CLIENT_WATCHER', true),
            'ignore_status_codes' => [404, 422, 429, 500, 502, 503, 504, 505],
            'ignore_methods' => ['GET', 'HEAD'],
            'ignore_paths' => [
                'telescope*',
                'horizon*',
                'api/user',
            ],
        ],

        Watchers\ScheduleWatcher::class => [
            'enabled' => env('TELESCOPE_SCHEDULE_WATCHER', true),
        ],

        Watchers\ViewWatcher::class => [
            'enabled' => env('TELESCOPE_VIEW_WATCHER', true),
        ],

        // Custom BigCommerce watchers



    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Markers
    |--------------------------------------------------------------------------
    |
    | The following array lists the "markers" that will be registered with
    | Telescope. Markers are used to identify specific points in time
    | within a request lifecycle.
    |
    */

    'markers' => [
        'created' => 'Laravel\Telescope\Watchers\Markers\CreatedMarker',
        'updated' => 'Laravel\Telescope\Watchers\Markers\UpdatedMarker',
        'deleted' => 'Laravel\Telescope\Watchers\Markers\DeletedMarker',
    ],

    /*
    |--------------------------------------------------------------------------
    | Register Commands
    |--------------------------------------------------------------------------
    |
    | The following array lists the Artisan commands that will be registered
    | with Telescope. These commands will be available in the Telescope UI.
    |
    */

    'commands' => [
        'Laravel\Telescope\Console\ClearCommand',
        'Laravel\Telescope\Console\InstallCommand',
        'Laravel\Telescope\Console\PublishCommand',
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope Tags
    |--------------------------------------------------------------------------
    |
    | The following array lists the "tags" that will be registered with
    | Telescope. Tags are used to categorize and filter Telescope data.
    |
    */

    'tags' => [
        'bigcommerce' => 'BigCommerce Operations',
        'webhook' => 'Webhook Processing',
        'subscription' => 'Subscription Management',
        'tenant' => 'Multi-tenant Operations',
        'billing' => 'Billing Operations',
    ],

];
