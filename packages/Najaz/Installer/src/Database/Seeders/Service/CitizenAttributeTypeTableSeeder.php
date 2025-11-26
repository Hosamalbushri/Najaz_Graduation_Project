<?php

namespace Najaz\Installer\Database\Seeders\Service;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitizenAttributeTypeTableSeeder extends Seeder
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
        if (! DB::getSchemaBuilder()->hasTable('service_attribute_types')) {
            return;
        }

        $now = Carbon::now();

        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');
        $locales = $parameters['allowed_locales'] ?? [$defaultLocale];

        // Check which attribute types already exist
        $existingTypes = DB::table('service_attribute_types')
            ->whereIn('code', ['citizen_name', 'national_id_card', 'documents', 'gender', 'marital_status'])
            ->pluck('code')
            ->toArray();

        // Define new attribute types to add
        $attributeTypes = [
            [
                'code' => 'citizen_name',
                'type' => 'text',
                'is_user_defined' => 0,
                'name_ar' => 'اسم المواطن',
                'name_en' => 'Citizen Name',
            ],
            [
                'code' => 'national_id_card',
                'type' => 'text',
                'is_user_defined' => 0,
                'name_ar' => 'بطاقة هوية',
                'name_en' => 'National ID Card',
            ],
            [
                'code' => 'documents',
                'type' => 'file',
                'is_user_defined' => 0,
                'name_ar' => 'وثائق',
                'name_en' => 'Documents',
            ],
            [
                'code' => 'gender',
                'type' => 'select',
                'is_user_defined' => 0,
                'name_ar' => 'جنس',
                'name_en' => 'Gender',
                'options' => [
                    [
                        'admin_name' => 'male',
                        'label_ar' => 'ذكر',
                        'label_en' => 'Male',
                        'sort_order' => 1,
                    ],
                    [
                        'admin_name' => 'female',
                        'label_ar' => 'أنثى',
                        'label_en' => 'Female',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'code' => 'marital_status',
                'type' => 'select',
                'is_user_defined' => 0,
                'name_ar' => 'الحالة الاجتماعية',
                'name_en' => 'Marital Status',
                'options' => [
                    [
                        'admin_name' => 'single',
                        'label_ar' => 'أعزب',
                        'label_en' => 'Single',
                        'sort_order' => 1,
                    ],
                    [
                        'admin_name' => 'married',
                        'label_ar' => 'متزوج',
                        'label_en' => 'Married',
                        'sort_order' => 2,
                    ],
                    [
                        'admin_name' => 'divorced',
                        'label_ar' => 'مطلق',
                        'label_en' => 'Divorced',
                        'sort_order' => 3,
                    ],
                    [
                        'admin_name' => 'widowed',
                        'label_ar' => 'أرمل',
                        'label_en' => 'Widowed',
                        'sort_order' => 4,
                    ],
                ],
            ],
        ];

        foreach ($attributeTypes as $attrType) {
            // Skip if already exists
            if (in_array($attrType['code'], $existingTypes)) {
                continue;
            }

            // Get next available ID
            $lastId = DB::table('service_attribute_types')->max('id') ?? 0;
            $attributeTypeId = $lastId + 1;

            // Insert attribute type
            DB::table('service_attribute_types')->insert([
                'id' => $attributeTypeId,
                'code' => $attrType['code'],
                'type' => $attrType['type'],
                'is_user_defined' => $attrType['is_user_defined'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert translations for each locale
            foreach ($locales as $locale) {
                $name = $locale === 'ar' ? $attrType['name_ar'] : $attrType['name_en'];

                DB::table('service_attribute_type_translations')->insert([
                    'locale' => $locale,
                    'name' => $name,
                    'service_attribute_type_id' => $attributeTypeId,
                ]);
            }

            // Insert options for select type (gender)
            if (isset($attrType['options']) && $attrType['type'] === 'select') {
                foreach ($attrType['options'] as $optionData) {
                    // Get next available option ID
                    $lastOptionId = DB::table('service_attribute_type_options')->max('id') ?? 0;
                    $optionId = $lastOptionId + 1;

                    // Insert option
                    DB::table('service_attribute_type_options')->insert([
                        'id' => $optionId,
                        'service_attribute_type_id' => $attributeTypeId,
                        'admin_name' => $optionData['admin_name'],
                        'sort_order' => $optionData['sort_order'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    // Insert option translations
                    foreach ($locales as $locale) {
                        $label = $locale === 'ar' ? $optionData['label_ar'] : $optionData['label_en'];

                        DB::table('service_attribute_type_option_translations')->insert([
                            'locale' => $locale,
                            'label' => $label,
                            'service_attribute_type_option_id' => $optionId,
                        ]);
                    }
                }
            }
        }
    }
}

