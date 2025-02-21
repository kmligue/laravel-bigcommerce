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
            $table->string('secure_url')->nullable()->after('timezone');
            $table->string('status')->nullable()->after('secure_url');
            $table->string('country')->nullable()->after('status');
            $table->string('plan_level')->nullable()->after('country');
            $table->boolean('multi_storefront_enabled')->default(false)->after('plan_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_info', function (Blueprint $table) {
            $table->dropColumn('secure_url');
            $table->dropColumn('status');
            $table->dropColumn('country');
            $table->dropColumn('plan_level');
            $table->dropColumn('multi_storefront_enabled');
        });
    }
};
