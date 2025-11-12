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
        Schema::table('service_attribute_groups', function (Blueprint $table) {
            if (! Schema::hasColumn('service_attribute_groups', 'group_type')) {
                $table->string('group_type')->default('general')->after('code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_attribute_groups', function (Blueprint $table) {
            if (Schema::hasColumn('service_attribute_groups', 'group_type')) {
                $table->dropColumn('group_type');
            }
        });
    }
};


