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
        Schema::create('service_field_type_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_field_type_id')->unsigned();
            $table->string('locale');
            $table->string('name');

            $table->unique(['service_field_type_id', 'locale'], 'sftt_field_type_id_locale_unique');
            $table->foreign('service_field_type_id', 'sftt_service_field_type_id_foreign')
                ->references('id')
                ->on('service_field_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_field_type_translations');
    }
};

