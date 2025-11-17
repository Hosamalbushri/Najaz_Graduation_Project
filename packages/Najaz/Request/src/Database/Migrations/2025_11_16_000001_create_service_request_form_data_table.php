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
        Schema::create('service_request_form_data', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_request_id');
            $table->string('group_code'); // كود المجموعة (customCode أو code)
            $table->string('group_name')->nullable(); // اسم المجموعة (customName أو name)
            $table->json('fields_data')->nullable(); // باقي الحقول كـ JSON
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('service_request_id')->references('id')->on('service_requests')->onDelete('cascade');
            
            $table->index('service_request_id');
            $table->index(['service_request_id', 'group_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_form_data');
    }
};

