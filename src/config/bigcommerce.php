<?php

return [
    'bc_app_id' => env('BC_APP_ID'),
    'bc_local_client_id' => env('BC_LOCAL_CLIENT_ID'),
    'bc_app_client_id' => env('BC_APP_CLIENT_ID'),
    'bc_local_secret' => env('BC_LOCAL_SECRET'),
    'bc_app_secret' => env('BC_APP_SECRET'),
    'bc_local_access_token' => env('BC_LOCAL_ACCESS_TOKEN'),
    'bc_local_store_hash' => env('BC_LOCAL_STORE_HASH'),
    
    /*
    |--------------------------------------------------------------------------
    | API Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable logging for BigCommerce API requests and responses.
    | This can be useful for debugging in development or monitoring in production.
    |
    */
    'enable_logging' => env('BC_ENABLE_LOGGING', true),
    
    /*
    |--------------------------------------------------------------------------
    | Telescope Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Enable or disable Laravel Telescope integration for BigCommerce operations.
    | This provides comprehensive debugging and monitoring capabilities.
    |
    */
    'enable_telescope' => env('BIGCOMMERCE_ENABLE_TELESCOPE', false),

    /*
    |--------------------------------------------------------------------------
    | Telescope Specific Settings
    |--------------------------------------------------------------------------
    |
    | Configure individual Telescope watchers and performance settings.
    |
    */
    'telescope' => [
        // Enable BigCommerce specific watchers
        'enable_bigcommerce_watcher' => env('TELESCOPE_BIGCOMMERCE_WATCHER', true),
        'enable_bigcommerce_db_watcher' => env('TELESCOPE_BIGCOMMERCE_DB_WATCHER', true),
        
        // Enable standard Telescope watchers
        'enable_query_watcher' => env('TELESCOPE_QUERY_WATCHER', true),
        'enable_model_watcher' => env('TELESCOPE_MODEL_WATCHER', true),
        'enable_request_watcher' => env('TELESCOPE_REQUEST_WATCHER', true),
        'enable_mail_watcher' => env('TELESCOPE_MAIL_WATCHER', true),
        'enable_job_watcher' => env('TELESCOPE_JOB_WATCHER', true),
        'enable_exception_watcher' => env('TELESCOPE_EXCEPTION_WATCHER', true),
        
        // Database monitoring settings
        'log_all_queries' => env('TELESCOPE_LOG_ALL_QUERIES', true), // Log every database query
        'log_slow_queries' => env('TELESCOPE_LOG_SLOW_QUERIES', true), // Log queries above threshold
        'query_slow_threshold' => env('TELESCOPE_QUERY_SLOW', 100), // milliseconds
        
        // Request watcher settings
        'request_size_limit' => env('TELESCOPE_REQUEST_SIZE_LIMIT', 64), // kilobytes
    ],

    /*
    |--------------------------------------------------------------------------
    | BigCommerce API Settings
    |--------------------------------------------------------------------------
    |
    | Configure API timeouts, retry attempts, and other connection settings.
    |
    */
    'api' => [
        'timeout' => env('BIGCOMMERCE_API_TIMEOUT', 30),
        'retry_attempts' => env('BIGCOMMERCE_API_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('BIGCOMMERCE_API_RETRY_DELAY', 1000), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | Configure webhook processing timeouts and retry settings.
    |
    */
    'webhooks' => [
        'timeout' => env('BIGCOMMERCE_WEBHOOK_TIMEOUT', 30),
        'max_retries' => env('BIGCOMMERCE_WEBHOOK_MAX_RETRIES', 3),
        'retry_delay' => env('BIGCOMMERCE_WEBHOOK_RETRY_DELAY', 5000), // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription Settings
    |--------------------------------------------------------------------------
    |
    | Configure subscription trial periods and grace periods.
    |
    */
    'subscriptions' => [
        'trial_days' => env('BIGCOMMERCE_TRIAL_DAYS', 14),
        'grace_period_days' => env('BIGCOMMERCE_GRACE_PERIOD_DAYS', 7),
        'auto_cancel_after_grace' => env('BIGCOMMERCE_AUTO_CANCEL_AFTER_GRACE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-tenant Settings
    |--------------------------------------------------------------------------
    |
    | Configure multi-tenant database settings and cleanup policies.
    |
    */
    'tenant' => [
        'database_prefix' => env('BIGCOMMERCE_TENANT_DB_PREFIX', 'tenant_'),
        'cleanup_old_tables' => env('BIGCOMMERCE_CLEANUP_OLD_TABLES', true),
        'old_table_threshold_days' => env('BIGCOMMERCE_OLD_TABLE_THRESHOLD_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Settings
    |--------------------------------------------------------------------------
    |
    | Configure admin notifications and email settings.
    |
    */
    'admin' => [
        'email' => env('BIGCOMMERCE_ADMIN_EMAIL', 'admin@example.com'),
        'notify_on_install' => env('BIGCOMMERCE_NOTIFY_ON_INSTALL', true),
        'notify_on_uninstall' => env('BIGCOMMERCE_NOTIFY_ON_UNINSTALL', true),
        'notify_on_subscription_change' => env('BIGCOMMERCE_NOTIFY_ON_SUBSCRIPTION_CHANGE', true),
    ],
];
