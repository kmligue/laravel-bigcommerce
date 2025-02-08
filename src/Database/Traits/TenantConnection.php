<?php

namespace Limonlabs\Bigcommerce\Database\Traits;

trait TenantConnection
{
    public function getConnectionName()
    {
        return 'tenant';
    }
}