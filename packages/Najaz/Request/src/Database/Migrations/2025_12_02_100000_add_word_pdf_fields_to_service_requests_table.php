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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('editable_word_path')->nullable()->after('status');
            $table->string('final_pdf_path')->nullable()->after('editable_word_path');
            $table->unsignedInteger('filled_by_admin_id')->nullable()->after('final_pdf_path');
            $table->timestamp('filled_at')->nullable()->after('filled_by_admin_id');

            $table->foreign('filled_by_admin_id')->references('id')->on('admins')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['filled_by_admin_id']);
            $table->dropColumn([
                'editable_word_path',
                'final_pdf_path',
                'filled_by_admin_id',
                'filled_at',
            ]);
        });
    }
};

