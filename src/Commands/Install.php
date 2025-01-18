<?php

namespace Limonlabs\Bigcommerce\Commands;

use Illuminate\Console\Command;

class Install extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bigcommerce:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install limonlabs/bigcommerce.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->comment('Installing limonlabs/bigcommerce...');
        $this->callSilent('vendor:publish', [
            '--provider' => 'Limonlabs\Bigcommerce\Providers\LimonlabsBigcommerceProvider',
            '--tag' => 'limonlabs-bigcommerce-migrations',
        ]);
        $this->info('✔️  Created migrations.');

        $this->callSilent('vendor:publish', [
            '--provider' => 'Limonlabs\Bigcommerce\Providers\LimonlabsBigcommerceProvider',
            '--tag' => 'limonlabs-bigcommerce-config',
        ]);
        $this->info('✔️  Created config/bigcommerce.php.');
        $this->info('✔️  Created config/plans.php.');
        $this->info('✔️  Created config/scripts.php.');

        $this->call('tenancy:install');

        $this->comment('✨️ limonlabs/bigcommerce installed successfully.');
    }
}