<?php

namespace Limonlabs\Bigcommerce\Controllers\Admin;

use Illuminate\Http\Request;

class InstallsController
{
    public function index(Request $request) {
        $stores = tenant_class()::get();
        
        return view('limonlabs/bigcommerce::admin.installs.index', compact('stores'));
    }
}
