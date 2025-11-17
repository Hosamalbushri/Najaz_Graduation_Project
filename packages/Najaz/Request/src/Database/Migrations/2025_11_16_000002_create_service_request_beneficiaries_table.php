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
        Schema::create('service_request_beneficiaries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_request_id');
            $table->unsignedInteger('citizen_id'); // الطرف المستفيد
            $table->string('group_code')->nullable(); // كود المجموعة التي جاء منها (مثلاً: husband_data, wife_data)
            $table->timestamps();

            $table->foreign('service_request_id')->references('id')->on('service_requests')->onDelete('cascade');
            $table->foreign('citizen_id')->references('id')->on('citizens')->onDelete('cascade');

            // منع تكرار نفس المواطن في نفس الطلب
            $table->unique(['service_request_id', 'citizen_id'], 'sr_beneficiaries_unique');
            $table->index('citizen_id', 'sr_beneficiaries_citizen_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_beneficiaries');
    }
};

