<?php

namespace Limonlabs\Bigcommerce\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\Facades\Config;

class TenantMigration extends Command
{
    protected $signature = 'tenants:migrate {--storeHash=}';

    protected $description = 'Run migrations for a specific tenant';

    public function handle()
    {
        $storeHash = $this->option('storeHash');
        $oldPrefix = Config::get('database.connections.tenant.prefix');

        if ($storeHash) {
            $this->migrateTenant($storeHash, $oldPrefix);
        } else {
            // Migrate all tenants 
            $storeInfos = \Limonlabs\Bigcommerce\Models\StoreInfo::all();

            // ISSUE: Only the first tenant is migrated. The rest are not migrated due to the table prefix for "migrations" table not being set correctly. https://www.loom.com/share/6a882218f16743c5bb327d8198e6e7ea
            foreach ($storeInfos as $storeInfo) {
                $this->migrateTenant($storeInfo->store_hash, $oldPrefix);
            }

            $this->info('All tenants migrated');
        }

        return 0;
    }

    protected function migrateTenant($storeHash, $oldPrefix)
    {
        \Illuminate\Support\Facades\DB::setTablePrefix($oldPrefix);

        $storeInfo = \Limonlabs\Bigcommerce\Models\StoreInfo::where('store_hash', $storeHash)->first();

        if ($storeInfo) {
            $this->info('Migrating tenant: ' . $storeHash);

            $prefix = $oldPrefix;

            if (!empty($prefix)) {
                $prefix = $prefix . '_' . str_replace('stores/', '', $storeHash) . '_';
            } else {
                $prefix = str_replace('stores/', '', $storeHash) . '_';
            }

            \Illuminate\Support\Facades\DB::setTablePrefix($prefix);

            \Illuminate\Support\Facades\Artisan::call('migrate', ['--path' => 'database/migrations/tenant', '--force' => true]);

            $this->info('Migration complete');
        } else {
            $this->error('Store not found');
        }
    }
}