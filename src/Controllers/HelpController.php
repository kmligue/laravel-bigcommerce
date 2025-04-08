<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Mail\Admin\Help;
use Limonlabs\Bigcommerce\Mail\User\HelpConfirmation;
use Illuminate\Support\Facades\Mail;

class HelpController
{
    public function index(Request $request, $storeHash) {
        $storeHash = 'stores/' . $storeHash;

        return view('limonlabs/bigcommerce::help.index', compact('storeHash'));
    }

    public function store(Request $request, $storeHash) {
        $storeHash = 'stores/' . $storeHash;

        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ]);

        $storeInfo = tenant();
        
        // Send notification to admin
        Mail::to(array_map('trim', explode(',', config('mail.from.admin_address'))))
            ->send(new Help($storeInfo, $data));
            
        // Send confirmation to the user
        Mail::to($data['email'])
            ->send(new HelpConfirmation($storeInfo, $data));

        return redirect()->back()->with('success', 'Your message has been sent successfully.');
    }
}
