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
        Schema::create('service_request_custom_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_request_id');
            $table->string('locale', 10); // اللغة (ar, en, etc.)
            $table->longText('template_content')->nullable(); // محتوى القالب المخصص HTML
            $table->json('additional_data')->nullable(); // البيانات الإضافية المستخرجة من الملفات
            $table->string('header_image')->nullable(); // صورة الترويسة
            $table->text('footer_text')->nullable(); // نص التذييل
            $table->unsignedInteger('created_by_admin_id')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('service_request_id')
                ->references('id')
                ->on('service_requests')
                ->onDelete('cascade');

            $table->foreign('created_by_admin_id')
                ->references('id')
                ->on('admins')
                ->onDelete('set null');

            // Indexes
            $table->index('service_request_id');
            $table->index('locale');
            
            // Unique constraint - one custom template per request per locale
            $table->unique(['service_request_id', 'locale'], 'srct_req_id_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_request_custom_templates');
    }
};

