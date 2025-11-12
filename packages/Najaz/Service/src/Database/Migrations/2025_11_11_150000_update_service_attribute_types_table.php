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
        Schema::table('service_attribute_types', function (Blueprint $table) {
            $table->boolean('is_required')->default(false)->after('is_user_defined');
            $table->boolean('is_unique')->default(false)->after('is_required');
            $table->integer('position')->nullable()->after('is_unique');
            $table->string('validation')->nullable()->after('position');
            $table->string('regex')->nullable()->after('validation');
            $table->text('default_value')->nullable()->after('regex');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_attribute_types', function (Blueprint $table) {
            $table->dropColumn([
                'is_required',
                'is_unique',
                'position',
                'validation',
                'regex',
                'default_value',
            ]);
        });
    }
};


