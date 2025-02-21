<?php

namespace Limonlabs\Bigcommerce\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Config;

class LimonlabsBigcommerceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // register the helper function
        require_once __DIR__.'/../helpers.php';
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // register middleware
        $this->app['router']->aliasMiddleware('bigcommerce.store.auth', \Limonlabs\Bigcommerce\Middleware\BigcommerceStoreAuth::class);

        // register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Limonlabs\Bigcommerce\Commands\TenantMigration::class,
            ]);
        }

        $this->publishesMigrations([
            __DIR__.'/../Database/migrations' => database_path('migrations'),
        ], 'limonlabs-bigcommerce-migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Views
        $this->loadViewsFrom(__DIR__.'/../views', 'limonlabs/bigcommerce');
        // $this->publishes([
        //     __DIR__.'/../views' => resource_path('views/vendor/limonlabs/bigcommerce'),
        // ], 'limonlabs-bigcommerce-views');
        
        $this->publishes(
            [
                __DIR__.'/../config/bigcommerce.php' => config_path('bigcommerce.php'),
                __DIR__.'/../config/plans.php' => config_path('plans.php'),
                __DIR__.'/../config/scripts.php' => config_path('scripts.php'),
                __DIR__.'/../config/webhooks.php' => config_path('webhooks.php'),
                __DIR__.'/../config/tenant.php' => config_path('tenant.php'),
            ],
        'limonlabs-bigcommerce-config');
        $this->mergeConfigFrom(__DIR__.'/../config/auth-guards.php', 'auth.guards');
        $this->mergeConfigFrom(__DIR__.'/../config/auth-providers.php', 'auth.providers');
        $this->mergeConfigFrom(__DIR__.'/../config/database.php', 'database.connections');

        Cashier::useCustomerModel(Config::get('tenant.tenant'));
    }
}