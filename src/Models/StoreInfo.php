<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
}
