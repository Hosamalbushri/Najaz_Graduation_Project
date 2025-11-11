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
        Schema::create('service_attribute_type_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_attribute_type_id')->unsigned();
            $table->string('locale');
            $table->string('name');

            $table->unique(['service_attribute_type_id', 'locale'], 'satt_type_id_locale_unique');
            $table->foreign('service_attribute_type_id', 'satt_attribute_type_id_foreign')
                ->references('id')
                ->on('service_attribute_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_attribute_type_translations');
    }
};


