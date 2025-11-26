<?php

namespace Najaz\Installer\Database\Seeders\Service;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttributeGroupTableSeeder extends Seeder
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
        if (! DB::getSchemaBuilder()->hasTable('service_attribute_groups')) {
            return;
        }

        $now = Carbon::now();

        $defaultLocale = $parameters['default_locale'] ?? config('app.locale');
        $locales = $parameters['allowed_locales'] ?? [$defaultLocale];

        // Check which groups already exist
        $existingGroups = DB::table('service_attribute_groups')
            ->whereIn('code', ['personal_information', 'general_data'])
            ->pluck('code')
            ->toArray();

        $groups = [
            [
                'code' => 'personal_information',
                'default_name' => 'معلومات شخصية',
                'default_name_en' => 'Personal Information',
                'group_type' => 'citizen',
                'sort_order' => 1,
                'fields' => [
                    'citizen_name',
                    'national_id_card',
                    'documents',
                    'gender',
                ],
            ],
            [
                'code' => 'general_data',
                'default_name' => 'بيانات عامة',
                'default_name_en' => 'General Data',
                'group_type' => 'general',
                'sort_order' => 2,
                'fields' => [
                    'citizen_name',
                    'national_id_card',
                    'documents',
                ],
            ],
        ];

        foreach ($groups as $groupData) {
            // Skip if already exists
            if (in_array($groupData['code'], $existingGroups)) {
                continue;
            }

            // Get next available ID
            $lastGroupId = DB::table('service_attribute_groups')->max('id') ?? 0;
            $groupId = $lastGroupId + 1;

            // Insert attribute group
            DB::table('service_attribute_groups')->insert([
                'id' => $groupId,
                'code' => $groupData['code'],
                'default_name' => $groupData['default_name'],
                'group_type' => $groupData['group_type'],
                'sort_order' => $groupData['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert translations for each locale
            foreach ($locales as $locale) {
                $name = $locale === 'ar' ? $groupData['default_name'] : $groupData['default_name_en'];

                DB::table('service_attribute_group_translations')->insert([
                    'locale' => $locale,
                    'name' => $name,
                    'description' => null,
                    'service_attribute_group_id' => $groupId,
                ]);
            }

            // Insert fields for this group
            if (isset($groupData['fields']) && is_array($groupData['fields'])) {
                $sortOrder = 1;
                foreach ($groupData['fields'] as $fieldCode) {
                    // Get attribute type by code
                    $attributeType = DB::table('service_attribute_types')
                        ->where('code', $fieldCode)
                        ->first();

                    if ($attributeType) {
                        // Check if field already exists in this group
                        $existingField = DB::table('service_attribute_fields')
                            ->where('service_attribute_group_id', $groupId)
                            ->where('code', $fieldCode)
                            ->first();

                        if (! $existingField) {
                            // Get next available field ID
                            $lastFieldId = DB::table('service_attribute_fields')->max('id') ?? 0;
                            $fieldId = $lastFieldId + 1;

                            // Insert field
                            DB::table('service_attribute_fields')->insert([
                                'id' => $fieldId,
                                'service_attribute_group_id' => $groupId,
                                'service_attribute_type_id' => $attributeType->id,
                                'code' => $fieldCode,
                                'type' => $attributeType->type,
                                'validation_rules' => null,
                                'default_value' => null,
                                'is_required' => false,
                                'sort_order' => $sortOrder,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);

                            // Get attribute type translation for field label
                            $attributeTypeTranslation = DB::table('service_attribute_type_translations')
                                ->where('service_attribute_type_id', $attributeType->id)
                                ->where('locale', $defaultLocale)
                                ->first();

                            // Insert field translations
                            foreach ($locales as $locale) {
                                $labelTranslation = DB::table('service_attribute_type_translations')
                                    ->where('service_attribute_type_id', $attributeType->id)
                                    ->where('locale', $locale)
                                    ->first();

                                $label = $labelTranslation ? $labelTranslation->name : ($attributeTypeTranslation ? $attributeTypeTranslation->name : $fieldCode);

                                DB::table('service_attribute_field_translations')->insert([
                                    'locale' => $locale,
                                    'label' => $label,
                                    'service_attribute_field_id' => $fieldId,
                                ]);
                            }

                            $sortOrder++;
                        }
                    }
                }
            }
        }
    }
}

