<?php

namespace Limonlabs\Bigcommerce\Middleware;

use Closure;
use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;
use Illuminate\Support\Facades\Config;

class ExpiredMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $store = tenant();
        
        if (empty($store->plan) && $store->trial_ends_at && $store->trial_ends_at->isPast()) {
            return redirect('/' . $store->store_hash . '/extensions/expired');
        }

        return $next($request);
    }
}
