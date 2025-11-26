<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Service\Models\Service as ServiceModel;

class ServiceFormQuery
{
    /**
     * Build a high-level form description for the given service.
     *
     * @param  \Najaz\Service\Models\Service  $rootValue
     * @return array
     */
    public function form($rootValue): array
    {
        if (! $rootValue instanceof ServiceModel) {
            return ['groups' => []];
        }

        // Eager-load nested relations if not already loaded (only translations, not template fields)
        $rootValue->loadMissing([
            'translations',
            'attributeGroups.translations',
        ]);

        // Load pivot relations with custom service fields (use service-specific tables only)
        $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
            'translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.options.translations', // Load custom field options only
            'attributeGroup.translations',
        ])->where('service_id', $rootValue->id)->get()->keyBy('pivot_uid');

        // Get current locale (set by LocaleMiddleware from x-locale header or default)
        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale', 'ar');
        
        $groups = $rootValue->attributeGroups->map(function ($group) use ($pivotRelations, $locale, $fallbackLocale) {
            $pivotUid = $group->pivot?->pivot_uid;
            $pivotRelation = $pivotUid ? $pivotRelations->get($pivotUid) : null;
            
            // Use saved fields if available, otherwise use template fields
            $fieldsToUse = $pivotRelation && $pivotRelation->fields->count() > 0
                ? $pivotRelation->fields
                : ($group->fields ?? collect());

            // Get custom_name from translations with fallback (like ServiceResolver)
            $customName = null;
            $customNameTranslations = [];
            if ($pivotRelation && $pivotRelation->relationLoaded('translations')) {
                // Try current locale first
                $translation = $pivotRelation->translations->where('locale', $locale)->first();
                $customName = $translation?->custom_name;
                
                // Fallback to default locale if not found
                if (empty($customName)) {
                    $fallbackTranslation = $pivotRelation->translations->where('locale', $fallbackLocale)->first();
                    $customName = $fallbackTranslation?->custom_name;
                }
                
                // Last resort: get any available translation
                if (empty($customName) && $pivotRelation->translations->isNotEmpty()) {
                    $customName = $pivotRelation->translations->first()->custom_name;
                }
                
                // Build translations object
                foreach ($pivotRelation->translations as $trans) {
                    $customNameTranslations[$trans->locale] = $trans->custom_name;
                }
            }

            // Get group name with fallback (TranslatableModel handles fallback automatically via translate())
            $groupTranslation = $group->translate($locale);
            $groupName = $customName 
                ?? ($groupTranslation?->name) 
                ?? ($group->translate($fallbackLocale)?->name)
                ?? $group->default_name
                ?? $group->code;

            return [
                'code'        => $group->pivot?->custom_code ?? $group->code,
                'label'       => (string) $groupName,
                'description' => $groupTranslation?->description ?? $group->translate($fallbackLocale)?->description ?? null,
                'sortOrder'   => $group->pivot?->sort_order ?? $group->sort_order,
                'pivotUid'    => $pivotUid,
                'isNotifiable'=> (bool) ($group->pivot?->is_notifiable ?? false),
                'customCode'  => $group->pivot?->custom_code,
                'customName'  => $customName,
                'customNameTranslations' => $customNameTranslations,
                'fields'      => $fieldsToUse->map(function ($field) use ($locale, $fallbackLocale) {
                    $attributeType = $field->attributeType;

                    // Get options from custom field options first, then fall back to attribute type options
                    $options = [];
                    
                    // First, try custom field options (service_attribute_group_service_field_options)
                    if ($field instanceof \Najaz\Service\Models\ServiceAttributeGroupServiceField && $field->options && $field->options->isNotEmpty()) {
                        $options = $field->options->map(function ($option) use ($locale, $fallbackLocale) {
                            // Try current locale first
                            $optionTranslation = $option->translate($locale);
                            $label = $optionTranslation?->label;
                            
                            // Fallback to default locale if not found
                            if (empty($label)) {
                                $fallbackTranslation = $option->translate($fallbackLocale);
                                $label = $fallbackTranslation?->label;
                            }
                            
                            // Last resort
                            $label = $label ?? $option->admin_name ?? $option->code ?? '';
                            
                            return [
                                'value' => (string) $option->id,
                                'label' => (string) $label,
                            ];
                        })->values()->all();
                    } elseif ($attributeType && $attributeType->options && $attributeType->options->isNotEmpty()) {
                        // Fall back to attribute type options if no custom options
                        $options = $attributeType->options->map(function ($option) use ($locale, $fallbackLocale) {
                            // Try current locale first
                            $optionTranslation = $option->translate($locale);
                            $label = $optionTranslation?->label;
                            
                            // Fallback to default locale if not found
                            if (empty($label)) {
                                $fallbackTranslation = $option->translate($fallbackLocale);
                                $label = $fallbackTranslation?->label;
                            }
                            
                            // Last resort
                            $label = $label ?? $option->admin_name ?? '';
                            
                            return [
                                'value' => (string) $option->id,
                                'label' => (string) $label,
                            ];
                        })->values()->all();
                    }

                    // Get field label with fallback (TranslatableModel handles fallback automatically)
                    $fieldTranslation = $field->translate($locale);
                    $fieldLabel = $fieldTranslation?->label;
                    
                    // Fallback to default locale if not found
                    if (empty($fieldLabel)) {
                        $fallbackFieldTranslation = $field->translate($fallbackLocale);
                        $fieldLabel = $fallbackFieldTranslation?->label;
                    }
                    
                    // Last resort
                    $fieldLabel = $fieldLabel ?? $field->code;

                    return [
                        'code'           => $field->code,
                        'label'          => (string) $fieldLabel,
                        'type'           => $field->type,
                        'isRequired'     => (bool) $field->is_required,
                        'defaultValue'   => $field->default_value,
                        'validationRules'=> $field->validation_rules,
                        'sortOrder'      => $field->sort_order,
                        'options'        => $options,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        return [
            'groups' => $groups,
        ];
    }
}

