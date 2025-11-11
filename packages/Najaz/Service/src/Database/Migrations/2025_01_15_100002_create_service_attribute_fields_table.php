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
        Schema::create('service_attribute_fields', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_attribute_group_id')->unsigned();
            $table->integer('service_attribute_type_id')->unsigned()->nullable();
            $table->string('code');
            $table->string('type'); // text, textarea, number, date, datetime, file, email, phone
            $table->json('validation_rules')->nullable();
            $table->text('default_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('service_attribute_type_id', 'saf_attribute_type_id_foreign')
                ->references('id')
                ->on('service_attribute_types')
                ->onDelete('set null');
            $table->unique(['service_attribute_group_id', 'code'], 'saf_group_id_code_unique');
            $table->foreign('service_attribute_group_id', 'saf_attribute_group_id_foreign')
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
        Schema::dropIfExists('service_attribute_fields');
    }
};


