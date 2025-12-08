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
        Schema::create('service_document_template_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('service_document_template_id')->unsigned();
            $table->string('locale');
            $table->text('template_content')->nullable();
            $table->text('footer_text')->nullable();

            $table->unique(['service_document_template_id', 'locale'], 'sdt_template_id_locale_unique');
            $table->foreign('service_document_template_id', 'sdt_template_id_foreign')
                ->references('id')
                ->on('service_document_templates')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_document_template_translations');
    }
};

