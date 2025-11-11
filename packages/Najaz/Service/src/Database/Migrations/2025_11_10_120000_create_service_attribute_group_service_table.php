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
        Schema::create('service_attribute_group_service', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned();
            $table->integer('service_attribute_group_id')->unsigned();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['service_id', 'service_attribute_group_id'], 'sags_service_group_unique');

            $table->foreign('service_id', 'sags_service_id_foreign')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');

            $table->foreign('service_attribute_group_id', 'sags_attribute_group_id_foreign')
                ->references('id')
                ->on('service_attribute_groups')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_attribute_group_service');
    }
};


