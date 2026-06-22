<?php

namespace Limonlabs\Bigcommerce\Services;

class BillingService
{
    public function getPlansForDisplay(): array
    {
        $plans = config('plans', []);
        $result = [];

        foreach ($plans as $key => $plan) {
            if (!($plan['show'] ?? false)) {
                continue;
            }

            $result[$key] = [
                'key' => $key,
                'name' => ucfirst($key),
                'plan_id' => $plan['plan_id'] ?? '',
                'price' => $plan['price'] ?? 0,
                'features' => $plan['features'] ?? [],
            ];
        }

        return $result;
    }

    public function isPlanCurrent(string $planKey, array $plan, ?array $currentPlan, bool $isOnTrial, array $userStatus): bool
    {
        $isPlanCurrent = false;

        if ($currentPlan && isset($currentPlan['plan_id'])) {
            $planId = $plan['plan_id'] ?? '';
            $currentPlanId = $currentPlan['plan_id'] ?? '';

            if ($planId == $currentPlanId) {
                $isPlanCurrent = true;
            } elseif (
                (str_starts_with($planId, 'prod_') && str_starts_with($currentPlanId, 'price_')) ||
                (str_starts_with($planId, 'price_') && str_starts_with($currentPlanId, 'prod_'))
            ) {
                $isPlanCurrent = true;
            }
        }

        if ($isOnTrial) {
            $highestPlan = get_highest_available_plan();
            if ($planKey === $highestPlan) {
                $isPlanCurrent = true;
            }
        }

        return $isPlanCurrent;
    }

    public function getButtonText(string $planKey, ?array $currentPlan, bool $isOnTrial, array $userStatus, bool $isPlanCurrent): string
    {
        if (!$isOnTrial && $isPlanCurrent) {
            return 'Current';
        }

        $buttonText = 'Sign Up';
        $freePlanId = config('plans.free.plan_id', '');

        if ($currentPlan && isset($currentPlan['plan_id']) && $currentPlan['plan_id'] != $freePlanId) {
            $buttonText = 'Change';
        }

        if ($isOnTrial && ($userStatus['current_plan'] ?? '') == $planKey) {
            $buttonText = 'Sign Up';
        }

        return $buttonText;
    }

    public function buildBillingIndexData($tenant): array
    {
        $plans = $this->getPlansForDisplay();
        $subscription = $tenant->subscription('default');
        $hasActiveSubscription = $subscription && $subscription->active();
        $currentPlan = $tenant->plan;
        $userStatus = $tenant->getPlanStatus();
        $isOnTrial = $userStatus['is_on_trial'] ?? false;
        $freePlanId = config('plans.free.plan_id', '');

        $planCards = [];

        foreach ($plans as $key => $plan) {
            $isPlanCurrent = $this->isPlanCurrent($key, $plan, $currentPlan, $isOnTrial, $userStatus);

            $planCards[] = [
                'key' => $key,
                'name' => $plan['name'],
                'price' => $plan['price'],
                'features' => $plan['features'],
                'is_current' => $isPlanCurrent,
                'is_trial_plan' => $isOnTrial && ($userStatus['current_plan'] ?? '') == $key,
                'button_text' => $this->getButtonText($key, $currentPlan, $isOnTrial, $userStatus, $isPlanCurrent),
                'show_cancel' => !$isOnTrial && $isPlanCurrent && $hasActiveSubscription,
            ];
        }

        return [
            'plans' => $planCards,
            'has_active_subscription' => $hasActiveSubscription,
            'is_on_trial' => $isOnTrial,
            'user_status' => $this->serializePlanStatus($userStatus),
            'current_plan' => $currentPlan,
            'free_plan_id' => $freePlanId,
        ];
    }

    public function serializePlanStatus(array $status): array
    {
        return [
            'is_subscribed' => $status['is_subscribed'] ?? false,
            'is_on_trial' => $status['is_on_trial'] ?? false,
            'current_plan' => $status['current_plan'] ?? null,
            'trial_ends_at' => isset($status['trial_ends_at']) ? $status['trial_ends_at']->toISOString() : null,
            'has_advanced_during_trial' => $status['has_advanced_during_trial'] ?? false,
            'post_trial_plan' => $status['post_trial_plan'] ?? null,
            'plan_details' => $status['plan_details'] ?? null,
        ];
    }

    public function buildTrialNoticeData($tenant): array
    {
        $status = $tenant->getPlanStatus();
        $daysLeft = 0;

        if ($status['is_on_trial'] && $status['trial_ends_at']) {
            $daysLeft = max(0, (int) now()->diffInDays($status['trial_ends_at'], false));
        }

        $trialEnded = ($status['is_on_trial'] ?? false) && $status['trial_ends_at'] && now()->greaterThanOrEqualTo($status['trial_ends_at']);
        $planExpired = !($status['is_on_trial'] ?? false)
            && !($status['is_subscribed'] ?? false)
            && ($status['post_trial_plan'] ?? '') != 'free';

        return [
            'show' => ($status['is_on_trial'] ?? false) || $trialEnded || $planExpired,
            'is_on_trial' => $status['is_on_trial'] ?? false,
            'trial_ended' => $trialEnded,
            'plan_expired' => $planExpired,
            'days_left' => $daysLeft,
            'trial_ends_at' => isset($status['trial_ends_at']) ? $status['trial_ends_at']->toISOString() : null,
            'post_trial_plan' => $status['post_trial_plan'] ? ucfirst($status['post_trial_plan']) : ucfirst(get_lowest_available_plan() ?? ''),
            'highest_plan' => ucfirst(get_highest_available_plan() ?? ''),
            'store_hash' => $tenant->store_hash,
        ];
    }
}
