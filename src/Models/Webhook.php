<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    use HasFactory;

    protected $table = 'webhooks';

    protected $fillable = [
        'store_id',
        'webhook_id',
        'client_id',
        'store_hash',
        'webhook_created_at',
        'webhook_updated_at',
        'scope',
        'destination',
        'is_active',
        'headers'
    ];

    protected $casts = [
        'headers' => 'array'
    ];
}
