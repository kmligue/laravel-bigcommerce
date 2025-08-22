<?php

namespace Limonlabs\Bigcommerce\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class InstallTelescope extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bigcommerce:install-telescope 
                            {--force : Force installation even if Telescope is already installed}
                            {--config-only : Only publish configuration files}
                            {--migrations-only : Only run Telescope migrations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure Laravel Telescope for BigCommerce package';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Installing Laravel Telescope for BigCommerce package...');

        // Check if Telescope is already installed
        if (!$this->option('force') && $this->isTelescopeInstalled()) {
            $this->warn('Telescope appears to be already installed.');
            
            if (!$this->confirm('Do you want to continue with the installation?')) {
                $this->info('Installation cancelled.');
                return 0;
            }
        }

        try {
            // Step 1: Publish Telescope configuration
            if (!$this->option('migrations-only')) {
                $this->publishTelescopeConfig();
            }

            // Step 2: Publish Telescope migrations
            if (!$this->option('config-only')) {
                $this->publishTelescopeMigrations();
            }

            // Step 3: Run Telescope migrations
            if (!$this->option('config-only')) {
                $this->runTelescopeMigrations();
            }

            // Step 4: Publish BigCommerce Telescope configuration
            if (!$this->option('migrations-only')) {
                $this->publishBigcommerceTelescopeConfig();
            }

            // Step 5: Update environment file
            if (!$this->option('migrations-only')) {
                $this->updateEnvironmentFile();
            }

            $this->info('✅ Telescope installation completed successfully!');
            $this->displayNextSteps();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Telescope installation failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Check if Telescope is already installed.
     *
     * @return bool
     */
    protected function isTelescopeInstalled()
    {
        return class_exists(\Laravel\Telescope\TelescopeServiceProvider::class) ||
               File::exists(config_path('telescope.php')) ||
               Schema::hasTable('telescope_entries');
    }

    /**
     * Publish Telescope configuration files.
     *
     * @return void
     */
    protected function publishTelescopeConfig()
    {
        $this->info('📁 Publishing Telescope configuration...');

        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'telescope-config',
                '--force' => true,
            ]);

            $this->info('✅ Telescope configuration published successfully.');
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not publish Telescope configuration: ' . $e->getMessage());
            $this->warn('This might be because Telescope is not yet installed via Composer.');
        }
    }

    /**
     * Publish Telescope migrations.
     *
     * @return void
     */
    protected function publishTelescopeMigrations()
    {
        $this->info('📁 Publishing Telescope migrations...');

        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'telescope-migrations',
                '--force' => true,
            ]);

            $this->info('✅ Telescope migrations published successfully.');
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not publish Telescope migrations: ' . $e->getMessage());
            $this->warn('This might be because Telescope is not yet installed via Composer.');
        }
    }

    /**
     * Run Telescope migrations.
     *
     * @return void
     */
    protected function runTelescopeMigrations()
    {
        $this->info('🔄 Running Telescope migrations...');

        try {
            Artisan::call('migrate', [
                '--force' => true,
            ]);

            $this->info('✅ Telescope migrations completed successfully.');
        } catch (\Exception $e) {
            $this->error('❌ Telescope migrations failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Publish BigCommerce Telescope configuration.
     *
     * @return void
     */
    protected function publishBigcommerceTelescopeConfig()
    {
        $this->info('📁 Publishing BigCommerce Telescope configuration...');

        try {
            Artisan::call('vendor:publish', [
                '--tag' => 'limonlabs-bigcommerce-config',
                '--force' => true,
            ]);

            $this->info('✅ BigCommerce Telescope configuration published successfully.');
        } catch (\Exception $e) {
            $this->warn('⚠️  Could not publish BigCommerce configuration: ' . $e->getMessage());
        }
    }

    /**
     * Update environment file with Telescope configuration.
     *
     * @return void
     */
    protected function updateEnvironmentFile()
    {
        $this->info('🔧 Updating environment configuration...');

        $envFile = base_path('.env');
        $envExampleFile = base_path('.env.example');

        if (!File::exists($envFile)) {
            $this->warn('⚠️  .env file not found. Please create it manually.');
            return;
        }

        $envContent = File::get($envFile);
        $updates = [];

        // Add Telescope configuration if not present
        if (!str_contains($envContent, 'BIGCOMMERCE_ENABLE_TELESCOPE')) {
            $updates[] = 'BIGCOMMERCE_ENABLE_TELESCOPE=true';
        }

        // Optional: Set TELESCOPE_ENABLED explicitly (defaults to BIGCOMMERCE_ENABLE_TELESCOPE)
        if (!str_contains($envContent, 'TELESCOPE_ENABLED')) {
            $updates[] = '# TELESCOPE_ENABLED=true  # Optional: Defaults to BIGCOMMERCE_ENABLE_TELESCOPE';
        }

        if (!str_contains($envContent, 'TELESCOPE_PATH')) {
            $updates[] = 'TELESCOPE_PATH=telescope';
        }

        if (!str_contains($envContent, 'TELESCOPE_DOMAIN')) {
            $updates[] = '# TELESCOPE_DOMAIN=';
        }

        if (!str_contains($envContent, 'TELESCOPE_DRIVER')) {
            $updates[] = 'TELESCOPE_DRIVER=database';
        }

        // Add database monitoring settings
        if (!str_contains($envContent, 'TELESCOPE_LOG_ALL_QUERIES')) {
            $updates[] = 'TELESCOPE_LOG_ALL_QUERIES=true';
        }

        if (!str_contains($envContent, 'TELESCOPE_LOG_SLOW_QUERIES')) {
            $updates[] = 'TELESCOPE_LOG_SLOW_QUERIES=true';
        }

        // Add HTTP Client watcher for BigCommerce API monitoring
        if (!str_contains($envContent, 'TELESCOPE_HTTP_CLIENT_WATCHER')) {
            $updates[] = 'TELESCOPE_HTTP_CLIENT_WATCHER=true';
        }

        // Add Telescope security settings
        if (!str_contains($envContent, 'TELESCOPE_ALLOWED_IPS')) {
            $updates[] = 'TELESCOPE_ALLOWED_IPS=127.0.0.1,::1';
        }

        if (!str_contains($envContent, 'TELESCOPE_REQUIRE_AUTH')) {
            $updates[] = 'TELESCOPE_REQUIRE_AUTH=true';
        }

        if (!str_contains($envContent, 'TELESCOPE_ALLOWED_ROLES')) {
            $updates[] = 'TELESCOPE_ALLOWED_ROLES=admin';
        }

        if (!str_contains($envContent, 'TELESCOPE_ALLOW_LIMONADMIN')) {
            $updates[] = 'TELESCOPE_ALLOW_LIMONADMIN=true';
        }

        if (!str_contains($envContent, 'TELESCOPE_QUERY_SLOW')) {
            $updates[] = 'TELESCOPE_QUERY_SLOW=100';
        }

        if (!str_contains($envContent, 'TELESCOPE_REQUEST_SIZE_LIMIT')) {
            $updates[] = 'TELESCOPE_REQUEST_SIZE_LIMIT=64';
        }

        if (!empty($updates)) {
            $envContent .= "\n\n# Telescope Configuration\n" . implode("\n", $updates);
            File::put($envFile, $envContent);
            $this->info('✅ Environment file updated successfully.');
        } else {
            $this->info('ℹ️  Environment file already contains Telescope configuration.');
        }
    }

    /**
     * Display next steps for the user.
     *
     * @return void
     */
    protected function displayNextSteps()
    {
        $this->newLine();
        $this->info('🎯 Next Steps:');
        $this->newLine();
        
        $this->line('1. Make sure Laravel Telescope is installed via Composer:');
        $this->line('   composer require laravel/telescope');
        $this->newLine();
        
        $this->line('2. Access Telescope dashboard at:');
        $this->line('   ' . url('/telescope'));
        $this->newLine();
        
        $this->line('3. Configure Telescope access in your User model:');
        $this->line('   Add the Telescope::check() method to your User model');
        $this->newLine();
        
        $this->line('4. Optional: Configure Telescope to only run in specific environments');
        $this->line('   by setting TELESCOPE_ENABLED=false in production');
        $this->newLine();
        
        $this->line('5. Monitor BigCommerce operations in the Telescope dashboard');
        $this->line('   Look for entries tagged with "bigcommerce"');
        $this->newLine();
        
        $this->warn('⚠️  Remember: Telescope should only be enabled in development/staging environments!');
    }
}
