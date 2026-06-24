<?php

namespace Limonlabs\Bigcommerce\Controllers\Admin;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Http\Concerns\AppliesApiListQuery;

class InstallsController
{
    use AppliesApiListQuery;

    public function index(Request $request) {
        $stores = $this->paginateApiList(
            tenant_class()::query(),
            $request,
            sortableFields: ['id', 'store_hash', 'name', 'user_email', 'created_at', 'trial_ends_at'],
            selectableFields: ['id', 'store_hash', 'name', 'first_name', 'last_name', 'user_email', 'timezone', 'secure_url', 'status', 'country', 'created_at', 'trial_ends_at'],
            includableRelations: ['webhooks'],
            defaultSort: 'created_at-desc',
        );

        return view('limonlabs/bigcommerce::admin.installs.index', compact('stores'));
    }
}
