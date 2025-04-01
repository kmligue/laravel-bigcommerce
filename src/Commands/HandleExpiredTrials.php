<?php

namespace Limonlabs\Bigcommerce\Commands;

use Illuminate\Console\Command;

class HandleExpiredTrials extends Command
{
    protected $signature = 'trials:expired';
    protected $description = 'Handle users whose trials have expired';

    public function handle()
    {
        // Find users whose trials have ended and haven't subscribed yet
        $users = tenant_class()::whereNotNull('trial_ends_at')
                     ->where('trial_ends_at', '<', now())
                     ->whereDoesntHave('subscriptions')
                     ->get();
        
        foreach ($users as $user) {
            // You might want to set has_advanced_during_trial to false
            $user->has_advanced_during_trial = false;
            $user->save();
        }
        
        return Command::SUCCESS;
    }
}