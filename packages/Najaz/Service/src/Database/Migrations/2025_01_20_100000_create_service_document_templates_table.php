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
        Schema::create('service_document_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->text('template_content'); // محتوى القالب مع الحقول المتغيرة
            $table->json('available_fields')->nullable(); // قائمة الحقول المتاحة للاستخدام
            $table->json('used_fields')->nullable(); // الحقول المستخدمة في القالب
            $table->string('header_image')->nullable(); // صورة الرأس (شعار المؤسسة)
            $table->string('footer_text')->nullable(); // نص التذييل
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->index('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_document_templates');
    }
};

