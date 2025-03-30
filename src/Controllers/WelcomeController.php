<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;

class WelcomeController
{
    public function index(Request $request, $storeHash) {
        $storeHash = 'stores/' . $storeHash;
        
        return view('limonlabs/bigcommerce::welcome.index', compact('storeHash'));
    }

    public function store(Request $request, $storeHash) {
        $storeHash = 'stores/' . $storeHash;
        $tenant = tenant();
        
        $tenant->update([
            'internal_settings' => [
                'welcome' => 1
            ]
        ]);
        
        return redirect(get_load_redirect($storeHash));
    }
}
