<?php

namespace Limonlabs\Bigcommerce\Controllers\Admin;

use Illuminate\Http\Request;

class LimonAdminController
{
    public function index(Request $request) {
        return view('limonlabs/bigcommerce::admin.index');
    }

    public function store(Request $request) {
        $request->validate([
            'password' => 'required'
        ]);

        $adminPassword = config('limonadmin.password');
        
        if ($request->password === $adminPassword) {
            $request->session()->put('limonadmin', true);

            return redirect('limonadmin/installs');
        } else {
            return redirect('limonadmin')->with('error', 'Invalid password');
        }
    }
}
