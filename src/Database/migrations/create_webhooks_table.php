<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->integer('store_id');
            $table->integer('webhook_id');
            $table->string('client_id');
            $table->string('store_hash');
            $table->integer('webhook_created_at');
            $table->integer('webhook_updated_at');
            $table->string('scope');
            $table->string('destination');
            $table->boolean('is_active');
            $table->json('headers')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webhooks');
    }
};
