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
            $table->boolean('has_advanced_during_trial')->default(false)->after('trial_ends_at');
            $table->string('post_trial_plan')->nullable()->after('has_advanced_during_trial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_info', function (Blueprint $table) {
            $table->dropColumn('has_advanced_during_trial');
            $table->dropColumn('post_trial_plan');
        });
    }
};
