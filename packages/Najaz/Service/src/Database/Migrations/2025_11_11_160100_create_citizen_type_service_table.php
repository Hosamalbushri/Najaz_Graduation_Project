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
        Schema::create('citizen_type_service', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->unsignedInteger('citizen_type_id');
            $table->timestamps();

            $table->unique(['service_id', 'citizen_type_id'], 'cts_service_citizen_unique');

            $table->foreign('service_id', 'cts_service_id_foreign')
                ->references('id')
                ->on('services')
                ->onDelete('cascade');

            $table->foreign('citizen_type_id', 'cts_citizen_type_id_foreign')
                ->references('id')
                ->on('citizen_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizen_type_service');
    }
};


