<?php

namespace Limonlabs\Bigcommerce\Middleware;

use Closure;
use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;
use Illuminate\Support\Facades\Config;

class StoreHashChecker
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
        $storeHash = 'stores/' . $request->route()->parameter('storeHash');
        
        if (empty($storeHash)) {
            abort(404);
        }

        return $next($request);
    }
}
