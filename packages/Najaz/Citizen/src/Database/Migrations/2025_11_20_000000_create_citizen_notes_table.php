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
        Schema::create('citizen_notes', function (Blueprint $table) {
            $table->id();
            $table->integer('citizen_id')->unsigned()->nullable();
            $table->text('note');
            $table->boolean('citizen_notified')->default(0);
            $table->unsignedInteger('admin_id')->nullable();
            $table->timestamps();

            $table->foreign('citizen_id')->references('id')->on('citizens')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizen_notes');
    }
};

