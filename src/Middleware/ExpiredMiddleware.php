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
        $planStatus = $store->getPlanStatus();

        if (!$planStatus['is_on_trial'] && !$planStatus['is_subscribed'] && $planStatus['post_trial_plan'] != 'free') {
            return redirect('/' . $store->store_hash . '/expired');
        }

        return $next($request);
    }
}
