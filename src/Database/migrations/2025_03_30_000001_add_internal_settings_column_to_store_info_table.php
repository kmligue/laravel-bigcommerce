<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('store_info', function (Blueprint $table) {
            $table->json('internal_settings')->nullable()->after('multi_storefront_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_info', function (Blueprint $table) {
            $table->dropColumn('internal_settings');
        });
    }
};
