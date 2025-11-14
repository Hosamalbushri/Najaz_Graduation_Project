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
            $table->uuid('pivot_uid')
                ->unique();
            $table->integer('service_id')->unsigned();
            $table->integer('service_attribute_group_id')->unsigned();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_notifiable')
                ->default(false);
            $table->string('custom_code')->nullable();
            $table->string('custom_name')
                ->nullable();


            $table->timestamps();
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


