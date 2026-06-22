<?php

namespace Limonlabs\Bigcommerce\Http\Controllers\Api;

use Illuminate\Http\Request;

class WelcomeApiController
{
    public function store(Request $request, string $storeHash)
    {
        $storeHash = 'stores/' . $storeHash;
        $tenant = tenant();

        $tenant->update([
            'internal_settings' => [
                'welcome' => 1,
            ],
        ]);

        return response()->json([
            'success' => true,
            'redirect' => get_load_redirect($storeHash),
        ]);
    }
}
