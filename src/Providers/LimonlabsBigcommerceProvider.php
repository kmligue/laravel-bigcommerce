<?php

namespace Limonlabs\Bigcommerce\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Limonlabs\Bigcommerce\Mail\Transports\SendgridHttp;
use Psr\Log\LoggerInterface;
use Laravel\Cashier\Events\WebhookReceived;
use Limonlabs\Bigcommerce\Mail\Admin\NewSitePaidPlan;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Client\Events\RequestSending;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Support\Facades\Log;

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

        // Register your schedule service provider
        $this->app->register(ScheduleServiceProvider::class);
        
        // Conditionally register Telescope if enabled
        if (config('bigcommerce.enable_telescope', false)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
        }
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
        $this->app['router']->aliasMiddleware('admin.or.store.auth', \Limonlabs\Bigcommerce\Middleware\AdminOrStoreAuth::class);

        $this->app['router']->aliasMiddleware('adminer', \Illuminate\Cookie\Middleware\EncryptCookies::class);
        $this->app['router']->pushMiddlewareToGroup('adminer', \Illuminate\Session\Middleware\StartSession::class);


        // register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Limonlabs\Bigcommerce\Commands\TenantMigration::class,
                \Limonlabs\Bigcommerce\Commands\DeleteOldTenantTables::class,
                \Limonlabs\Bigcommerce\Commands\HandleExpiredTrials::class,
                \Limonlabs\Bigcommerce\Commands\InstallTelescope::class,
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
                __DIR__.'/../config/telescope.php' => config_path('telescope.php'),
            ],
        'limonlabs-bigcommerce-config');

        $this->publishes([
            __DIR__.'/../images' => public_path('images/limonlabs'),
        ], 'limonlabs-bigcommerce-images');

        $this->publishes([
            __DIR__.'/../../dist' => public_path('vendor/limonlabs/bigcommerce'),
        ], 'limonlabs-bigcommerce-assets');

        $this->mergeConfigFrom(__DIR__.'/../config/auth-guards.php', 'auth.guards');
        $this->mergeConfigFrom(__DIR__.'/../config/auth-providers.php', 'auth.providers');
        $this->mergeConfigFrom(__DIR__.'/../config/database.php', 'database.connections');
        $this->mergeConfigFrom(__DIR__.'/../config/sendgrid.php', 'services');
        $this->mergeConfigFrom(__DIR__.'/../config/adminer.php', 'adminer');
        $this->mergeConfigFrom(__DIR__.'/../config/mail.php', 'mail.from');
        $this->mergeConfigFrom(__DIR__.'/../config/tenant.php', 'tenant');
        $this->mergeConfigFrom(__DIR__.'/../config/limonadmin.php', 'limonadmin');
        $this->mergeConfigFrom(__DIR__.'/../config/mail-mailers.php', 'mail.mailers');
        $this->mergeConfigFrom(__DIR__.'/../config/services-stripe.php', 'services');
        $this->mergeConfigFrom(__DIR__.'/../config/services-bigcommerce.php', 'services');
        $this->mergeConfigFrom(__DIR__.'/../config/logging-bigcommerce.php', 'logging.channels');
        
        // Merge Telescope configuration if enabled
        if (config('bigcommerce.enable_telescope', false)) {
            $this->mergeConfigFrom(__DIR__.'/../config/telescope.php', 'telescope');
            
            // Set up Telescope authorization
            $this->setupTelescopeAuthorization();
        }

        Cashier::useCustomerModel(Config::get('tenant.tenant'));

        Mail::extend('sendgrid-http', function ($app) {
            return new SendgridHttp(
                new Client(),
                config('mail.mailers.sendgrid-http.api_url'),
                config('mail.mailers.sendgrid-http.api_key'),
                app(LoggerInterface::class)
            );
        });

        Event::listen(WebhookReceived::class, function (WebhookReceived $event) {
            if ($event->payload['type'] === 'customer.subscription.created') {
                $stripeCustomerId = $event->payload['data']['object']['customer'];
    
                $user = \Limonlabs\Bigcommerce\Models\StoreInfo::where('stripe_id', $stripeCustomerId)->first();
                if ($user) {
                    try {
                        // Send email to the dev
                        Mail::to(array_map('trim', explode(',', config('mail.from.admin_address'))))
                            ->send(new NewSitePaidPlan($user));
                    } catch (\Throwable $th) {
                        //throw $th;
                    }
                }
            }
        });

        $loggingEnabled = config('bigcommerce.enable_logging', false);

        if ($loggingEnabled) {
            // Log outgoing requests
            $this->app['events']->listen(RequestSending::class, function (RequestSending $event) {
                $url = (string) $event->request->url();
                if (str_contains($url, 'bigcommerce.com')) {
                    Log::channel('bigcommerce')->info('BigCommerce Request', [
                        'url' => $url,
                        'method' => $event->request->method(),
                        'body' => $event->request->body(),
                        'headers' => $event->request->headers(),
                    ]);
                }
            });

            // Log incoming responses
            $this->app['events']->listen(ResponseReceived::class, function (ResponseReceived $event) {
                $url = (string) $event->request->url();
                if (str_contains($url, 'bigcommerce.com')) {
                    Log::channel('bigcommerce')->info('BigCommerce Response', [
                        'url' => $url,
                        'status' => $event->response->status(),
                        'body' => $event->response->body(),
                    ]);
                }
            });

            // Log on connection failure
            $this->app['events']->listen(ConnectionFailed::class, function (ConnectionFailed $event) {
                $url = (string) $event->request->url();
                if (str_contains($url, 'bigcommerce.com')) {
                    Log::channel('bigcommerce')->error('BigCommerce Connection Failed', [
                        'url' => $url,
                        'error' => $event->exception->getMessage(),
                    ]);
                }
            });
        }
    }

    /**
     * Set up Telescope authorization for BigCommerce package
     */
    protected function setupTelescopeAuthorization()
    {
        // Only set up authorization if Telescope is available
        if (!class_exists(\Laravel\Telescope\Telescope::class)) {
            return;
        }

        // Override Telescope's default authorization
        \Laravel\Telescope\Telescope::auth(function ($request) {
            $authorizer = new \Limonlabs\Bigcommerce\Telescope\TelescopeAuthorization();
            return $authorizer->authorize($request);
        });
    }
}