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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_id')->unsigned()->nullable();
            $table->integer('citizen_id')->unsigned()->nullable();
            $table->string('citizen_first_name')->nullable();
            $table->string('citizen_middle_name')->nullable();
            $table->string('citizen_last_name')->nullable();
            $table->string('citizen_national_id')->nullable();
            $table->string('citizen_type_name')->nullable();
            $table->string('status')->default('pending');
            $table->string('locale')->nullable(); // اللغة الافتراضية
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable(); // ملاحظات من الأدمن
            $table->unsignedInteger('assigned_to')->nullable(); // موظف معين للطلب
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('set null');
            $table->foreign('citizen_id')->references('id')->on('citizens')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('admins')->onDelete('set null');

            $table->index(['service_id', 'citizen_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};

