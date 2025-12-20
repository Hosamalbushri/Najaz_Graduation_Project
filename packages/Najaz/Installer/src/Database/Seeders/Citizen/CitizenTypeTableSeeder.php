<?php

namespace Najaz\Installer\Database\Seeders\Citizen;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CitizenTypeTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        // Check if table exists
        if (! Schema::hasTable('citizen_types')) {
            return;
        }

        $now = Carbon::now();

        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');

        // Check if default citizen types already exist
        $existingTypes = DB::table('citizen_types')
            ->whereIn('code', ['general', 'legal_guardian', 'judge'])
            ->where('is_user_defined', 0)
            ->pluck('code')
            ->toArray();

        $citizenTypes = [
            [
                'code' => 'general',
                'name_ar' => 'عام',
                'name_en' => 'General',
                'is_user_defined' => 0,
            ],
            [
                'code' => 'legal_guardian',
                'name_ar' => 'أمين شرعي',
                'name_en' => 'Legal Guardian',
                'is_user_defined' => 0,
            ],
            [
                'code' => 'judge',
                'name_ar' => 'قاضي',
                'name_en' => 'Judge',
                'is_user_defined' => 0,
            ],
        ];

        foreach ($citizenTypes as $type) {
            // Skip if already exists
            if (in_array($type['code'], $existingTypes)) {
                continue;
            }

            // Determine the name based on locale
            $name = $defaultLocale === 'ar' ? $type['name_ar'] : $type['name_en'];

            DB::table('citizen_types')->insert([
                'code' => $type['code'],
                'name' => $name,
                'is_user_defined' => $type['is_user_defined'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}

