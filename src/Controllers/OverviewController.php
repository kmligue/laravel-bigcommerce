<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;

class OverviewController
{
    public function index(Request $request, $storeHash) {
        $store = StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        $storeHash = 'stores/' . $storeHash;

        return view('limonlabs/bigcommerce::overview.index', compact('store', 'storeHash'));
    }
}
