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
        Schema::create('service_request_status_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_request_id')->unsigned();
            $table->enum('reason_type', ['rejection', 'revision']);
            $table->text('reason');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('service_request_id')
                ->references('id')
                ->on('service_requests')
                ->onDelete('cascade');

            $table->index(['service_request_id', 'reason_type'], 'sr_status_reasons_req_type_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_status_reasons');
    }
};

