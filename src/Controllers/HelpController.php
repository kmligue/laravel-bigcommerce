<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;

class HelpController
{
    public function index(Request $request, $storeHash) {
        $storeHash = 'stores/' . $storeHash;

        return view('limonlabs/bigcommerce::help.index', compact('storeHash'));
    }
}
