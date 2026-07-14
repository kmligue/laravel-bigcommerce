<?php

namespace Limonlabs\Bigcommerce\Controllers\Admin;

class ApiTesterController
{
    public function index()
    {
        return view('limonlabs/bigcommerce::admin.api-tester.index', [
            'appName' => config('app.name'),
        ]);
    }
}
