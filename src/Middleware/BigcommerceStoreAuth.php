<?php

namespace Limonlabs\Bigcommerce\Middleware;

use Closure;
use Illuminate\Http\Request;
use Limonlabs\Bigcommerce\Models\StoreInfo;
use Illuminate\Support\Facades\Config;

class BigcommerceStoreAuth
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
        
        $store = tenant_class()::where('store_hash', $storeHash)->first();

        if (!$store) {
            abort(404);
        }

        // set the store in the request
        $request->merge(['tenant' => $store]);
        
        $prefix = Config::get('database.connections.tenant.prefix');

        if (!empty($prefix)) {
            $prefix = $prefix . '_' . str_replace('stores/', '', $storeHash) . '_';
        } else {
            $prefix = str_replace('stores/', '', $storeHash) . '_';
        }

        \Illuminate\Support\Facades\Config::set('database.connections.tenant.prefix', $prefix);

        return $next($request);
    }
}
