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
        Schema::create('service_attribute_group_service_field_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_attribute_group_service_field_id')->unsigned();
            $table->integer('service_attribute_type_option_id')->unsigned()->nullable(); // Reference to original option
            $table->string('admin_name')->nullable(); // Custom admin name if option is customized
            $table->integer('sort_order')->default(0);
            $table->boolean('is_custom')->default(false); // True if this is a custom option, false if from original
            $table->timestamps();

            $table->foreign('service_attribute_group_service_field_id', 'sagsfo_field_id_foreign')
                ->references('id')
                ->on('service_attribute_group_service_fields')
                ->onDelete('cascade');

            $table->foreign('service_attribute_type_option_id', 'sagsfo_option_id_foreign')
                ->references('id')
                ->on('service_attribute_type_options')
                ->onDelete('set null');

            $table->index('service_attribute_group_service_field_id', 'sagsfo_field_id_idx');
            $table->index('service_attribute_type_option_id', 'sagsfo_option_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_attribute_group_service_field_options');
    }
};










