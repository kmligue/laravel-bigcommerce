<?php

namespace Limonlabs\Bigcommerce\Controllers\Admin;

use Illuminate\Http\Request;

class MaintenanceController
{
    public function index(Request $request) {
        if (!is_maintenance()) {
            abort(404);
        }

        return view('limonlabs/bigcommerce::admin.maintenance.index');
    }
}
