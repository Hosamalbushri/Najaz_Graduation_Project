<?php

namespace Najaz\Service\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceFieldTypeTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        DB::table('service_field_types')->delete();

        DB::table('service_field_type_translations')->delete();

        $now = Carbon::now();

        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');

        DB::table('service_field_types')->insert([
            [
                'id'              => 1,
                'code'            => 'id_number',
                'type'            => 'text',
                'is_user_defined' => 0, // حقل ثابت من النظام
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'id'              => 2,
                'code'            => 'citizen_name',
                'type'            => 'text',
                'is_user_defined' => 0, // حقل ثابت من النظام
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ]);

        $locales = $parameters['allowed_locales'] ?? [$defaultLocale];

        foreach ($locales as $locale) {
            DB::table('service_field_type_translations')->insert([
                [
                    'locale'                => $locale,
                    'name'                  => $locale === 'ar' ? 'رقم الهوية' : 'ID Number',
                    'service_field_type_id' => 1,
                ],
                [
                    'locale'                => $locale,
                    'name'                  => $locale === 'ar' ? 'اسم المواطن' : 'Citizen Name',
                    'service_field_type_id' => 2,
                ],
            ]);
        }
    }
}
