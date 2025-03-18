<?php

namespace Limonlabs\Bigcommerce\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Support\Facades\Config;

class DeleteTenantTables extends Command
{
    protected $signature = 'delete:tables';

    protected $description = 'Delete tenant tables that are over 30 days old';

    public function handle()
    {
        $tablePrefix = config('database.connections.mysql.prefix');
        $storeInfos = tenant_class()::get();

        foreach ($storeInfos as $storeInfo) {
            $table = $tablePrefix . '_' . str_replace('stores/', '', $storeInfo->store_hash);
            
            $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE '{$table}\_%'");
            // convert to array
            $tables = json_decode(json_encode($tables), true);

            foreach ($tables as $_table) {
                foreach ($_table as $table) {
                    // check if $table has '-DEL-' in it
                    if (strpos($table, '-DEL-') === true) {
                        // get the date from the table name
                        $date = explode('-DEL-', $table)[1];

                        if (is_numeric($date)) {
                            // check if the date is older than 30 days
                            if ($date < strtotime('-30 days')) {
                                // drop the table
                                \Illuminate\Support\Facades\DB::statement("DROP TABLE `{$table}`");
                            }
                        }
                    }
                }
            }
        }
    }
}