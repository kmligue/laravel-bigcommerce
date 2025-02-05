<?php

namespace Limonlabs\Bigcommerce\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\MigrateCommand;

class TenantMigration extends Command
{
    protected $signature = 'tenants:migrate {--storeHash=}';

    protected $description = 'Run migrations for a specific tenant';

    public function handle()
    {
        $storeHash = $this->option('storeHash');

        if ($storeHash) {
            $this->migrateTenant($storeHash);
        } else {
            // Migrate all tenants 
            $storeInfos = \Limonlabs\Bigcommerce\Models\StoreInfo::all();

            // ISSUE: Only the first tenant is migrated. The rest are not migrated due to the table prefix for "migrations" table not being set correctly. https://www.loom.com/share/6a882218f16743c5bb327d8198e6e7ea
            foreach ($storeInfos as $storeInfo) {
                $this->migrateTenant($storeInfo->store_hash);
            }

            $this->info('All tenants migrated');
        }

        return 0;
    }

    protected function migrateTenant($storeHash)
    {
        \Illuminate\Support\Facades\DB::setTablePrefix('');

        $storeInfo = \Limonlabs\Bigcommerce\Models\StoreInfo::where('store_hash', $storeHash)->first();

        if ($storeInfo) {
            $this->info('Migrating tenant: ' . $storeHash);

            \Illuminate\Support\Facades\DB::setTablePrefix(str_replace('stores/', '', $storeHash) . '_');

            \Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            $this->info('Migration complete');
        } else {
            $this->error('Store not found');
        }
    }
}