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
