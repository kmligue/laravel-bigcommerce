<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class StoreInfo extends Authenticatable
{
    use HasFactory, Billable;

    protected $table = 'store_info';

    protected $fillable = [
        'store_hash',
        'access_token',
        'user_id',
        'user_email',
        'timezone'
    ];

    public function webhooks() {
        return $this->hasMany(\Limonlabs\Bigcommerce\Models\Webhook::class, 'store_id');
    }

    protected static function booted()
    {
        static::created(function ($storeInfo) {
            $prefix = Config::get('database.connections.tenant.prefix');

            if (!empty($prefix)) {
                $prefix = $prefix . '_' . str_replace('stores/', '', $storeInfo->store_hash) . '_';
            } else {
                $prefix = str_replace('stores/', '', $storeInfo->store_hash) . '_';
            }

            DB::setTablePrefix($prefix);

            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            DB::setTablePrefix('');
        });
    }
}
