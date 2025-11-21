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
        Schema::create('service_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type'); // 'service_request' or 'identity_verification'
            $table->boolean('read')->default(0);
            $table->integer('entity_id')->unsigned();
            $table->timestamps();

            $table->index(['type', 'read']);
            $table->index(['type', 'entity_id']);
            $table->index('entity_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_notifications');
    }
};

