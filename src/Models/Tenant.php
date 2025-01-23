<?php

namespace Limonlabs\Bigcommerce\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Cashier\Billable;

class Tenant extends BaseTenant implements 
    TenantWithDatabase,
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use HasDatabase, HasDomains, Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, Billable;
}