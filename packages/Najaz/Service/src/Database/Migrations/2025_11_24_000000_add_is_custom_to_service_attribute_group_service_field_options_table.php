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
        Schema::table('service_attribute_group_service_field_options', function (Blueprint $table) {
            if (!Schema::hasColumn('service_attribute_group_service_field_options', 'is_custom')) {
                $table->boolean('is_custom')->default(false)->after('sort_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_attribute_group_service_field_options', function (Blueprint $table) {
            if (Schema::hasColumn('service_attribute_group_service_field_options', 'is_custom')) {
                $table->dropColumn('is_custom');
            }
        });
    }
};





















