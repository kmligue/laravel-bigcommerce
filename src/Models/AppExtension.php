<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppExtension extends Model
{
    use HasFactory;

    protected $table = 'app_extensions';

    protected $fillable = [
        'app_extension_id'
    ];
}
