<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

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
            DB::setTablePrefix(str_replace('stores/', '', $storeInfo->store_hash) . '_');

            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);
        });
    }
}
