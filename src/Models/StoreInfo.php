<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreInfo extends Authenticatable
{
    use HasFactory, Billable, SoftDeletes;

    protected $table = 'store_info';

    protected $fillable = [
        'store_hash',
        'access_token',
        'user_id',
        'name',
        'first_name',
        'last_name',
        'user_email',
        'timezone',
        'secure_url',
        'status',
        'country',
        'plan_level',
        'multi_storefront_enabled',
        'internal_settings',
        'settings',
        'trial_ends_at'
    ];

    protected $casts = [
        'settings' => 'array',
        'internal_settings' => 'array',
        'trial_ends_at' => 'datetime'
    ];

    public function webhooks() {
        return $this->hasMany(\Limonlabs\Bigcommerce\Models\Webhook::class, 'store_id');
    }

    public function getPlanAttribute() {
        $plans = Config::get('plans');
        $_plan = [];

        foreach ($plans as $key => $plan) {
            if ($this->subscribedToPrice($plan['plan_id'])) {
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
