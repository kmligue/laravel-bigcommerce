<?php

namespace Limonlabs\Bigcommerce\Controllers;

use Illuminate\Http\Request;

class SpaController
{
    public function store(Request $request, ?string $storeHash = null)
    {
        return $this->render($request, $storeHash ? 'stores/' . $storeHash : null);
    }

    public function admin(Request $request)
    {
        if ($request->has('p')) {
            $adminPassword = config('limonadmin.password');

            if (base64_decode($request->p) === $adminPassword) {
                $request->session()->put('limonadmin', true);

                return redirect('/limonadmin/installs');
            }
        }

        return $this->render($request);
    }

    protected function render(Request $request, ?string $storeHash = null)
    {
        return view('limonlabs/bigcommerce::spa', [
            'appName' => config('app.name'),
            'stripeKey' => config('services.stripe.key'),
            'storeHash' => $storeHash,
        ]);
    }
}
