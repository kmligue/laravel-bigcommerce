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
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Views
        $this->loadViewsFrom(__DIR__.'/../views', 'limonlabs/bigcommerce');
        $this->publishes([
            __DIR__.'/../views' => resource_path('views/vendor/limonlabs/bigcommerce'),
        ], 'limonlabs-bigcommerce-views');
        
        $this->publishes(
            [
            __DIR__.'/../config/bigcommerce.php' => config_path('bigcommerce.php'),
            __DIR__.'/../config/plans.php' => config_path('plans.php'),
            __DIR__.'/../config/scripts.php' => config_path('scripts.php'),
            ],
        'limonlabs-bigcommerce-config');
        $this->mergeConfigFrom(__DIR__.'/../config/auth-guards.php', 'auth.guards');
        $this->mergeConfigFrom(__DIR__.'/../config/auth-providers.php', 'auth.providers');

        Cashier::useCustomerModel(\Limonlabs\Bigcommerce\Models\StoreInfo::class);
    }
}