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
        Schema::create('service_data_group_field_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_data_group_field_id')->unsigned();
            $table->string('locale');
            $table->string('label');

            $table->unique(['service_data_group_field_id', 'locale'], 'sdgft_field_id_locale_unique');
            $table->foreign('service_data_group_field_id', 'sdgft_service_data_group_field_id_foreign')
                ->references('id')
                ->on('service_data_group_fields')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_data_group_field_translations');
    }
};




