<?php

namespace Najaz\Installer\Database\Seeders\Service;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Service Category table seeder.
 *
 * Command: php artisan db:seed --class=Najaz\\Installer\\Database\\Seeders\\Service\\ServiceCategoryTableSeeder
 */
class ServiceCategoryTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @param  array  $parameters
     * @return void
     */
    public function run($parameters = [])
    {
        // Check if tables exist
        if (! Schema::hasTable('service_categories')) {
            return;
        }

        DB::table('service_categories')->delete();

        if (Schema::hasTable('service_category_translations')) {
            DB::table('service_category_translations')->delete();
        }

        $now = Carbon::now();

        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');

        DB::table('service_categories')->insert([
            [
                'id'           => 1,
                'position'     => 1,
                'logo_path'    => null,
                'status'       => 1,
                'display_mode' => 'services_and_description',
                '_lft'         => 1,
                '_rgt'         => 6,
                'parent_id'    => null,
                'banner_path'  => null,
                'additional'   => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => 2,
                'position'     => 1,
                'logo_path'    => null,
                'status'       => 1,
                'display_mode' => 'services_and_description',
                '_lft'         => 2,
                '_rgt'         => 3,
                'parent_id'    => 1,
                'banner_path'  => null,
                'additional'   => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id'           => 3,
                'position'     => 2,
                'logo_path'    => null,
                'status'       => 1,
                'display_mode' => 'services_and_description',
                '_lft'         => 4,
                '_rgt'         => 5,
                'parent_id'    => 1,
                'banner_path'  => null,
                'additional'   => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        $locales = $parameters['allowed_locales'] ?? [$defaultLocale];

        foreach ($locales as $locale) {
            $translations = [
                [
                    'name'                => 'Root',
                    'slug'                => 'root',
                    'url_path'            => '',
                    'description'         => 'Root Category',
                    'meta_title'          => '',
                    'meta_description'    => '',
                    'meta_keywords'       => '',
                    'service_category_id' => 1,
                    'locale'              => $locale,
                    'locale_id'           => null,
                ],
                [
                    'name'                => $locale === 'ar' ? 'الخدمات القضائية' : 'Judicial Services',
                    'slug'                => $locale === 'ar' ? 'judicial-services' : 'judicial-services',
                    'url_path'            => '',
                    'description'         => $locale === 'ar' ? 'الخدمات المتعلقة بالأمور القضائية والمحاكم' : 'Services related to judicial matters and courts',
                    'meta_title'          => $locale === 'ar' ? 'الخدمات القضائية' : 'Judicial Services',
                    'meta_description'    => $locale === 'ar' ? 'الخدمات المتعلقة بالأمور القضائية والمحاكم' : 'Services related to judicial matters and courts',
                    'meta_keywords'       => $locale === 'ar' ? 'قضائية، محاكم، قانونية' : 'judicial, courts, legal',
                    'service_category_id' => 2,
                    'locale'              => $locale,
                    'locale_id'           => null,
                ],
                [
                    'name'                => $locale === 'ar' ? 'خدمات التوثيق' : 'Notarization Services',
                    'slug'                => $locale === 'ar' ? 'notarization-services' : 'notarization-services',
                    'url_path'            => '',
                    'description'         => $locale === 'ar' ? 'خدمات التوثيق والمصادقة على المستندات' : 'Services for document authentication and notarization',
                    'meta_title'          => $locale === 'ar' ? 'خدمات التوثيق' : 'Notarization Services',
                    'meta_description'    => $locale === 'ar' ? 'خدمات التوثيق والمصادقة على المستندات' : 'Services for document authentication and notarization',
                    'meta_keywords'       => $locale === 'ar' ? 'توثيق، مصادقة، مستندات' : 'notarization, authentication, documents',
                    'service_category_id' => 3,
                    'locale'              => $locale,
                    'locale_id'           => null,
                ],
            ];

            if (Schema::hasTable('service_category_translations')) {
                DB::table('service_category_translations')->insert($translations);
            }
        }
    }
}

