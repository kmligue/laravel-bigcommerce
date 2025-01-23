<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;

class OverviewController
{
    public function index(Request $request) {
        return view('limonlabs/bigcommerce::overview.index');
    }
}
