<?php

namespace Limonlabs\Bigcommerce\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Limonlabs\Bigcommerce\Mail\Admin\Help as AdminHelp;
use Limonlabs\Bigcommerce\Mail\User\HelpConfirmation;

class HelpApiController
{
    public function store(Request $request, string $storeHash)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
        ]);

        $storeInfo = tenant();

        Mail::to(array_map('trim', explode(',', config('mail.from.admin_address'))))
            ->send(new AdminHelp($storeInfo, $data));

        Mail::to($data['email'])
            ->send(new HelpConfirmation($storeInfo, $data));

        return response()->json([
            'success' => true,
            'message' => 'Your message has been sent successfully.',
        ]);
    }
}
