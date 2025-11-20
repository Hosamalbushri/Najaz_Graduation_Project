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
        Schema::create('service_request_admin_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_request_id')->unsigned()->nullable();
            $table->text('note');
            $table->boolean('citizen_notified')->default(0);
            $table->unsignedInteger('admin_id')->nullable();
            $table->timestamps();

            $table->foreign('service_request_id')->references('id')->on('service_requests')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_admin_notes');
    }
};

