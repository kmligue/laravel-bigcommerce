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
