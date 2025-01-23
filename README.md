# Installation
&bullet; Add into composer.json
```
...
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/kmligue/laravel-bigcommerce"
    }
],
...
```
&bullet; Run composer require command
```
composer require limonlabs/bigcommerce:@dev
```
&bullet; Publish config and migrations
```
php artisan vendor:publish --tag=limonlabs-bigcommerce-migrations
php artisan vendor:publish --tag=limonlabs-bigcommerce-config
```
&bullet; Register **StartSession** middleware. (https://dev.to/abdulwahidkahar/how-to-fix-session-store-not-set-on-request-laravel-11-2d4p)
```
use Illuminate\Session\Middleware\StartSession;

...
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(StartSession::class);
})
...
```
&bullet; Set tenant_model in config/tenancy.php
```
'tenant_model' => Limonlabs\Bigcommerce\Models\Tenant::class,
```
&bullet; Add your app url to central_domains in config/tenancy.php
```
'central_domains' => [
    '127.0.0.1',
    'localhost',
    'laravel-package.test', // Example
],
```
&bullet; Add the TenancyServiceProvider class in app/bootstrap/providers.php
```
...
App\Providers\TenancyServiceProvider::class,
...
```
