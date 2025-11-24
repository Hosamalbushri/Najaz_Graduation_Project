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
        Schema::create('service_attribute_group_service_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_attribute_group_service_id')->unsigned();
            $table->integer('service_attribute_field_id')->unsigned()->nullable();
            $table->integer('service_attribute_type_id')->unsigned();
            $table->string('code');
            $table->string('type');
            $table->json('validation_rules')->nullable();
            $table->text('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('service_attribute_group_service_id', 'sagsf_pivot_id_foreign')
                ->references('id')
                ->on('service_attribute_group_service')
                ->onDelete('cascade');

            $table->foreign('service_attribute_field_id', 'sagsf_field_id_foreign')
                ->references('id')
                ->on('service_attribute_fields')
                ->onDelete('set null');

            $table->foreign('service_attribute_type_id', 'sagsf_type_id_foreign')
                ->references('id')
                ->on('service_attribute_types')
                ->onDelete('restrict');

            $table->unique(['service_attribute_group_service_id', 'code'], 'sagsf_pivot_code_unique');
            
            $table->index('service_attribute_group_service_id', 'sagsf_pivot_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_attribute_group_service_fields');
    }
};

