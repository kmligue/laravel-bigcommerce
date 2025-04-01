<?php

namespace Limonlabs\Bigcommerce\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use Limonlabs\Bigcommerce\Mail\Transports\SendgridHttp;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
        $this->app['router']->aliasMiddleware('bigcommerce.store.expired', \Limonlabs\Bigcommerce\Middleware\ExpiredMiddleware::class);
        $this->app['router']->aliasMiddleware('limonadmin.auth', \Limonlabs\Bigcommerce\Middleware\LimonAdminAuth::class);
        $this->app['router']->aliasMiddleware('limonadmin.guest', \Limonlabs\Bigcommerce\Middleware\LimonAdminGuest::class);
        $this->app['router']->aliasMiddleware('welcome.auth', \Limonlabs\Bigcommerce\Middleware\WelcomeAuth::class);

        $this->app['router']->aliasMiddleware('adminer', \Illuminate\Cookie\Middleware\EncryptCookies::class);
        $this->app['router']->pushMiddlewareToGroup('adminer', \Illuminate\Session\Middleware\StartSession::class);


        // register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Limonlabs\Bigcommerce\Commands\TenantMigration::class,
                \Limonlabs\Bigcommerce\Commands\DeleteOldTenantTables::class,
            ]);
        }

        $this->publishesMigrations([
            __DIR__.'/../Database/migrations' => database_path('migrations'),
        ], 'limonlabs-bigcommerce-migrations');

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/console.php');

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
                __DIR__.'/../config/limonadmin.php' => config_path('limonadmin.php'),
            ],
        'limonlabs-bigcommerce-config');

        $this->publishes([
            __DIR__.'/../images' => public_path('images/limonlabs'),
        ], 'limonlabs-bigcommerce-images');

        $this->mergeConfigFrom(__DIR__.'/../config/auth-guards.php', 'auth.guards');
        $this->mergeConfigFrom(__DIR__.'/../config/auth-providers.php', 'auth.providers');
        $this->mergeConfigFrom(__DIR__.'/../config/database.php', 'database.connections');
        $this->mergeConfigFrom(__DIR__.'/../config/sendgrid.php', 'services');
        $this->mergeConfigFrom(__DIR__.'/../config/adminer.php', 'adminer');
        $this->mergeConfigFrom(__DIR__.'/../config/mail.php', 'mail.from');
        $this->mergeConfigFrom(__DIR__.'/../config/tenant.php', 'tenant');
        $this->mergeConfigFrom(__DIR__.'/../config/limonadmin.php', 'limonadmin');
        $this->mergeConfigFrom(__DIR__.'/../config/mail-mailers.php', 'mail.mailers');

        Cashier::useCustomerModel(Config::get('tenant.tenant'));

        Mail::extend('sendgrid-http', function ($app) {
            return new SendgridHttp(
                new Client(),
                config('mail.mailers.sendgrid-http.api_url'),
                config('mail.mailers.sendgrid-http.api_key'),
                app(LoggerInterface::class)
            );
        });
    }
}