<?php

namespace Limonlabs\Bigcommerce\Controllers\Admin;

use Illuminate\Http\Request;

class MaintenanceController
{
    public function index(Request $request) {
        if (!is_maintenance()) {
            if ($request->session()->has('storeHash')) {
                $storeHash = $request->session()->get('storeHash');

                return redirect(get_load_redirect($storeHash));
            }

            abort(404);
        }

        return view('limonlabs/bigcommerce::admin.maintenance.index');
    }
}
