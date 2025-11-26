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
        Schema::table('service_attribute_group_service', function (Blueprint $table) {
            // Migrate existing custom_name data to translations table before dropping
            $pivots = \DB::table('service_attribute_group_service')
                ->whereNotNull('custom_name')
                ->get();

            foreach ($pivots as $pivot) {
                $defaultLocale = config('app.locale', 'ar');
                
                \DB::table('service_attribute_group_service_translations')->insert([
                    'service_attribute_group_service_id' => $pivot->id,
                    'locale' => $defaultLocale,
                    'custom_name' => $pivot->custom_name,
                ]);
            }

            if (Schema::hasColumn('service_attribute_group_service', 'custom_name')) {
                $table->dropColumn('custom_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_attribute_group_service', function (Blueprint $table) {
            // Migrate translations back to custom_name column
            $translations = \DB::table('service_attribute_group_service_translations')
                ->where('locale', config('app.locale', 'ar'))
                ->get()
                ->keyBy('service_attribute_group_service_id');

            foreach ($translations as $translation) {
                \DB::table('service_attribute_group_service')
                    ->where('id', $translation->service_attribute_group_service_id)
                    ->update(['custom_name' => $translation->custom_name]);
            }

            $table->string('custom_name')->nullable()->after('custom_code');
        });
    }
};

