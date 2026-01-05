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
        Schema::table('service_request_custom_templates', function (Blueprint $table) {
            $table->dropColumn([
                'additional_data',
                'header_image',
                'footer_text',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_request_custom_templates', function (Blueprint $table) {
            $table->json('additional_data')->nullable()->after('template_content');
            $table->string('header_image')->nullable()->after('additional_data');
            $table->text('footer_text')->nullable()->after('header_image');
        });
    }
};

