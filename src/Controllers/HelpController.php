<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;

class HelpController
{
    public function index(Request $request) {
        return view('limonlabs/bigcommerce::help.index');
    }
}
