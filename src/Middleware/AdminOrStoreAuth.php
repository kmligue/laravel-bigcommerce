<?php

namespace Limonlabs\Bigcommerce\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOrStoreAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as admin via session
        if ($request->session()->get('limonadmin') === true) {
            // Admin user - allow access by temporarily authenticating as the store
            $storeHash = $request->route('storeHash');
            
            // Find the store by store_hash
            $store = \Limonlabs\Bigcommerce\Models\StoreInfo::where('store_hash', 'stores/' . $storeHash)->first();
            
            if ($store) {
                // Temporarily authenticate as this store for admin access
                auth('store_info')->login($store);
                return $next($request);
            }
        }
        
        // Check if user is already authenticated as a store
        if (auth('store_info')->check()) {
            // User is authenticated as a store - allow access
            return $next($request);
        }
        
        // Neither admin nor store authenticated - return 404
        abort(404, 'Store not found or access denied');
    }
}
