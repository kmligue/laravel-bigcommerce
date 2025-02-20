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
composer require limonlabs/bigcommerce:dev-multitenancy2
```
&bullet; Publish config and migrations
```
php artisan vendor:publish --tag=limonlabs-bigcommerce-migrations
php artisan vendor:publish --tag=limonlabs-bigcommerce-config
php artisan vendor:publish --tag=limonlabs-bigcommerce-config

or

You can publish all by using the command "php artisan vendor:publish" and select "Limonlabs\Bigcommerce\Providers\LimonlabsBigcommerceProvider" from the list
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

&bullet; set .env SESSION_DRIVER=file

by default this is set to "database". we will set it to "file" so that we will not anymore migrate session table.


# Notes
&bullet; We have a default StoreInfo model that looks like this:
```
<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class StoreInfo extends Authenticatable
{
    use HasFactory, Billable;

    protected $table = 'store_info';

    protected $fillable = [
        'store_hash',
        'access_token',
        'user_id',
        'user_email',
        'timezone',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    public function webhooks() {
        return $this->hasMany(\Limonlabs\Bigcommerce\Models\Webhook::class, 'store_id');
    }

    public function getPlanAttribute() {
        $plans = Config::get('plans');
        $_plan = [];

        foreach ($plans as $key => $plan) {
            if (tenant()->subscribedToPrice($plan['plan_id'])) {
                $_plan = $plan;

                break;
            }
        }

        if (empty($_plan) && isset($plans['free'])) {
            $_plan = $plans['free'];
        }

        return $_plan;
    }

    public function getChannelsAttribute() {
        $channels = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->access_token
        ])->get('https://api.bigcommerce.com/' . $this->store_hash . '/v3/channels');
        
        if ($channels->successful()) {
            $json = $channels->json();

            if (isset($json['data'])) {
                return $json['data'];
            }
        }

        return [];
    }

    protected static function booted()
    {
        static::created(function ($storeInfo) {
            $oldPrefix = Config::get('database.connections.tenant.prefix');
            $prefix = $oldPrefix;

            if (!empty($prefix)) {
                $prefix = $prefix . '_' . str_replace('stores/', '', $storeInfo->store_hash) . '_';
            } else {
                $prefix = str_replace('stores/', '', $storeInfo->store_hash) . '_';
            }

            DB::setTablePrefix($prefix);

            Artisan::call('migrate', ['--path' => 'database/migrations/tenant', '--force' => true]);

            DB::setTablePrefix($oldPrefix);
        });
    }
}

```
You can override this one in config/tenant.php
```
'tenant' => \Limonlabs\Bigcommerce\Models\StoreInfo::class,
```
&bullet; All tenant tables should use the trait ```TenantConnection```

Example:
```
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Limonlabs\Bigcommerce\Database\Traits\TenantConnection;

class Feedback extends Model
{
    use HasFactory, SoftDeletes, TenantConnection;

    protected $table = 'feedbacks';

    protected $fillable = [
        'store_id',
        'name',
        'email',
        'message',
        'page_location',
        'user_agent',
        'sentiment'
    ];

    protected $casts = [
        'user_agent' => 'array',
    ];
}
```
# Stripe Keys
&bullet; Set stripe credentials in .env file. Don't also forget to set the stripe price id's for the plans.
```
STRIPE_KEY=pk_test_nJQKMa1qyBWYURVUZC15LmVB
STRIPE_SECRET=sk_test_D1uu9XXITIS9htvBNeA8xNjt
STRIPE_WEBHOOK_SECRET=whsec_yqfXz53AZWuA1HwJRyIcVyxMO4uxUMKK

STRIPE_BRONZE_PLAN_ID=price_1Qu23jDp8CuNIE4Gtp135KEP
STRIPE_SILVER_PLAN_ID=price_1Qu246Dp8CuNIE4GcG216veP
STRIPE_GOLD_PLAN_ID=price_1Qu24HDp8CuNIE4GbY3U4mCF
```
# Help Form
&bullet; Form uses https://www.staticforms.xyz/. Add the api key in .env file.
```
STATICFORMS_ACCESS_KEY=b9c3f48b-3e4e-4d60-8295-cb7211501eec
```
