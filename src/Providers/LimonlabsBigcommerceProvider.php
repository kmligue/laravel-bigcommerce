<?php

namespace Limonlabs\Bigcommerce\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class LimonlabsBigcommerceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // register middleware
        $this->app['router']->aliasMiddleware('bigcommerce.store.auth', \Limonlabs\Bigcommerce\Middleware\BigcommerceStoreAuth::class);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'limonlabs-bigcommerce-migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../views', 'limonlabs/bigcommerce');
        $this->publishes([
            __DIR__.'/../config/bigcommerce.php' => config_path('bigcommerce.php'),
        ], 'limonlabs-bigcommerce-config');
        $this->mergeConfigFrom(__DIR__.'/../config/auth-guards.php', 'auth.guards');
        $this->mergeConfigFrom(__DIR__.'/../config/auth-providers.php', 'auth.providers');

        Cashier::useCustomerModel(\Limonlabs\Bigcommerce\Models\StoreInfo::class);
    }
}