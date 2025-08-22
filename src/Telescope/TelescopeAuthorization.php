<?php

namespace Limonlabs\Bigcommerce\Telescope;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelescopeAuthorization
{
    /**
     * Check if the current user can access Telescope
     * 
     * @param Request $request
     * @return bool
     * @suppressWarnings PHPMD.UndefinedMethod - Dynamic method checking for compatibility
     */
    public function authorize(Request $request): bool
    {
        // Allow access in local environment
        if (app()->environment('local')) {
            return true;
        }

        // Allow access for authenticated users with specific roles
        if (Auth::check()) {
            $user = Auth::user();
            
            // Check if user is a BigCommerce store owner
            if (method_exists($user, 'storeInfo') && $user->storeInfo) {
                return true;
            }
            
            // Check if user has admin role (common patterns)
            if (method_exists($user, 'hasRole')) {
                try {
                    if ($user->hasRole('admin')) {
                        return true;
                    }
                } catch (\Exception $e) {
                    // Ignore if role system not implemented
                }
            }
            
            // Check if user has specific permissions
            if (method_exists($user, 'can')) {
                try {
                    if ($user->can('view-telescope')) {
                        return true;
                    }
                } catch (\Exception $e) {
                    // Ignore if permission system not implemented
                }
            }
            
            // Check for user ID 1 (common admin pattern)
            if ($user->id === 1) {
                return true;
            }
        }
        
        // Check if user is authenticated in LimonAdmin session
        if (config('bigcommerce.telescope.allow_limonadmin', true) && session()->get('limonadmin') === true) {
            return true;
        }

        // Allow access for specific IP addresses (optional)
        $allowedIPs = config('bigcommerce.telescope.allowed_ips', '');
        if (!empty($allowedIPs)) {
            $ips = array_map('trim', explode(',', $allowedIPs));
            if (in_array($request->ip(), $ips)) {
                return true;
            }
        }

        // Deny access by default
        return false;
    }
}
