<?php

namespace Limonlabs\Bigcommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Cashier\Billable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoreInfo extends Authenticatable
{
    use HasFactory, Billable, SoftDeletes;

    protected $table = 'store_info';

    protected $fillable = [
        'store_hash',
        'access_token',
        'user_id',
        'name',
        'first_name',
        'last_name',
        'user_email',
        'timezone',
        'secure_url',
        'status',
        'country',
        'plan_level',
        'multi_storefront_enabled',
        'internal_settings',
        'settings',
        'trial_ends_at',
        'has_advanced_during_trial',
        'post_trial_plan'
    ];

    protected $casts = [
        'settings' => 'array',
        'internal_settings' => 'array',
        'trial_ends_at' => 'datetime'
    ];

    public function webhooks() {
        return $this->hasMany(\Limonlabs\Bigcommerce\Models\Webhook::class, 'store_id');
    }

    public function getPlanAttribute() {
        $plans = Config::get('plans');
        $_plan = [];

        foreach ($plans as $key => $plan) {
            if ($this->subscribedToPrice($plan['plan_id'])) {
                $_plan = $plan;

                break;
            }
        }

        if (empty($_plan) && isset($plans['free'])) {
            $_plan = $plans['free'];
        }

        return $_plan;
    }

    public function getChannelsAttribute() {
        $channels = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->access_token
        ])->get('https://api.bigcommerce.com/' . $this->store_hash . '/v3/channels');
        
        if ($channels->successful()) {
            $json = $channels->json();

            if (isset($json['data'])) {
                return $json['data'];
            }
        }

        return [];
    }

    /**
     * Get the user's current plan status
     *
     * @return array Contains plan information and trial status
     */
    public function getPlanStatus()
    {
        $result = [
            'is_subscribed' => false,
            'is_on_trial' => false,
            'current_plan' => null,
            'trial_ends_at' => null,
            'plan_details' => null,
            'has_advanced_during_trial' => $this->has_advanced_during_trial ?? false,
            'post_trial_plan' => $this->post_trial_plan ?? null,
        ];

        // Check if user is on trial
        if ($this->onTrial()) {
            $result['is_on_trial'] = true;
            $result['trial_ends_at'] = $this->trial_ends_at;
            
            // If user has advanced access during trial, set their current plan
            // to the highest plan or a specific trial plan
            if ($this->has_advanced_during_trial ?? false) {
                $plans = config('plans');
                // Find the highest plan by sorting
                uasort($plans, function($a, $b) {
                    return ($b['price'] ?? 0) <=> ($a['price'] ?? 0);
                });
                
                // Get the highest plan key (first after sorting)
                $highestPlanKey = array_key_first($plans);
                $result['current_plan'] = $highestPlanKey; // Typically 'gold' based on your config
                $result['plan_details'] = $plans[$highestPlanKey];
            }
        } 
        // Check if user has an active subscription
        elseif ($this->subscribed('default')) {
            $result['is_subscribed'] = true;
            
            $subscription = $this->subscription('default');
            $stripePriceId = $subscription->stripe_price;
            
            // Match the stripe_price to a plan in the config
            $plans = config('plans');
            foreach ($plans as $planKey => $planDetails) {
                if (isset($planDetails['plan_id']) && $planDetails['plan_id'] === $stripePriceId) {
                    $result['current_plan'] = $planKey;
                    $result['plan_details'] = $planDetails;
                    break;
                }
            }
        }
        
        return $result;
    }

    /**
     * Check if user has access to a specific plan's features
     *
     * @param string $planKey The plan key to check access for (e.g., 'bronze', 'silver', 'gold')
     * @return bool Whether user has access to the specified plan's features
     */
    public function hasPlanAccess($planKey)
    {
        $status = $this->getPlanStatus();
        
        // If not subscribed and not on trial, no access
        if (!$status['is_subscribed'] && !$status['is_on_trial']) {
            return false;
        }
        
        $plans = config('plans');
        
        // If the requested plan doesn't exist in our config, deny access
        if (!isset($plans[$planKey])) {
            return false;
        }
        
        // Get the requested plan's price/level
        $requestedPlanPrice = $plans[$planKey]['price'] ?? PHP_INT_MAX;
        
        // If user is on trial with advanced access
        if ($status['is_on_trial'] && $status['has_advanced_during_trial']) {
            // During trial with advanced access, give access to all plans
            return true;
        }
        
        // If user is subscribed to a plan
        if ($status['is_subscribed'] && isset($status['current_plan'])) {
            $currentPlanPrice = $plans[$status['current_plan']]['price'] ?? 0;
            
            // User has access if their plan price is >= the requested plan price
            return $currentPlanPrice >= $requestedPlanPrice;
        }
        
        return false;
    }

    /**
     * Get all features the user has access to
     *
     * @return array Array of feature strings the user has access to
     */
    public function getAccessibleFeatures()
    {
        $status = $this->getPlanStatus();
        $allFeatures = [];
        $plans = config('plans');
        
        // If on trial with advanced access, merge all features
        if ($status['is_on_trial'] && $status['has_advanced_during_trial']) {
            foreach ($plans as $plan) {
                if (isset($plan['features']) && is_array($plan['features'])) {
                    $allFeatures = array_merge($allFeatures, $plan['features']);
                }
            }
        } 
        // If subscribed, get features of current plan and lower tiers
        elseif ($status['is_subscribed'] && isset($status['current_plan'])) {
            $currentPlanPrice = $plans[$status['current_plan']]['price'] ?? 0;
            
            foreach ($plans as $plan) {
                if (isset($plan['price']) && $plan['price'] <= $currentPlanPrice) {
                    if (isset($plan['features']) && is_array($plan['features'])) {
                        $allFeatures = array_merge($allFeatures, $plan['features']);
                    }
                }
            }
        }
        
        // Remove duplicate features
        return array_unique($allFeatures);
    }

    protected static function booted()
    {
        static::created(function ($storeInfo) {
            $oldPrefix = Config::get('database.connections.tenant.prefix');
            $prefix = $oldPrefix;

            if (!empty($prefix)) {
                $prefix = $prefix . '_' . str_replace('stores/', '', $storeInfo->store_hash) . '_';
            } else {
                $prefix = str_replace('stores/', '', $storeInfo->store_hash) . '_';
            }

            DB::setTablePrefix($prefix);

            Artisan::call('migrate', ['--path' => 'database/migrations/tenant', '--force' => true]);

            DB::setTablePrefix($oldPrefix);
        });
    }
}
