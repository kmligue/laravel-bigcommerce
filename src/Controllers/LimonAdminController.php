<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;

class LimonAdminController
{
    public function index(Request $request) {
        $stores = tenant_class()::get();

        return view('limonlabs/bigcommerce::admin.index', compact('stores'));
    }
}
