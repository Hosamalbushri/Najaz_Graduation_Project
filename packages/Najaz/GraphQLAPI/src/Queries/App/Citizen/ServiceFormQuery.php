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
            'attributeGroups.translations',
        ]);

        // Load pivot relations with custom service fields (use service-specific tables only)
        $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
            'fields.translations',
            'fields.attributeType.translations',
            'fields.options.translations', // Load custom field options only
            'attributeGroup.translations',
        ])->where('service_id', $rootValue->id)->get()->keyBy('pivot_uid');

        $groups = $rootValue->attributeGroups->map(function ($group) use ($pivotRelations) {
            $pivotUid = $group->pivot?->pivot_uid;
            $pivotRelation = $pivotUid ? $pivotRelations->get($pivotUid) : null;
            
            // Use saved fields if available, otherwise use template fields
            $fieldsToUse = $pivotRelation && $pivotRelation->fields->count() > 0
                ? $pivotRelation->fields
                : ($group->fields ?? collect());

            return [
                'code'        => $group->pivot?->custom_code ?? $group->code,
                'label'       => (string) ($group->pivot?->custom_name ?? $group->name ?? $group->default_name),
                'description' => $group->description ?? null,
                'sortOrder'   => $group->pivot?->sort_order ?? $group->sort_order,
                'pivotUid'    => $pivotUid,
                'isNotifiable'=> (bool) ($group->pivot?->is_notifiable ?? false),
                'customCode'  => $group->pivot?->custom_code,
                'customName'  => $group->pivot?->custom_name,
                'fields'      => $fieldsToUse->map(function ($field) {
                    $attributeType = $field->attributeType;
                    $locale = app()->getLocale();

                    // Get options from custom field options first, then fall back to attribute type options
                    $options = [];
                    
                    // First, try custom field options (service_attribute_group_service_field_options)
                    if ($field instanceof \Najaz\Service\Models\ServiceAttributeGroupServiceField && $field->options && $field->options->isNotEmpty()) {
                        $options = $field->options->map(function ($option) use ($locale) {
                            $optionTranslation = $option->translate($locale);
                            return [
                                'value' => (string) $option->id,
                                'label' => (string) ($optionTranslation?->label ?? $option->admin_name ?? $option->code ?? ''),
                            ];
                        })->values()->all();
                    } elseif ($attributeType && $attributeType->options && $attributeType->options->isNotEmpty()) {
                        // Fall back to attribute type options if no custom options
                        $options = $attributeType->options->map(function ($option) use ($locale) {
                            $optionTranslation = $option->translate($locale);
                            return [
                                'value' => (string) $option->id,
                                'label' => (string) ($optionTranslation?->label ?? $option->admin_name ?? ''),
                            ];
                        })->values()->all();
                    }

                    $fieldTranslation = $field->translate($locale);

                    return [
                        'code'           => $field->code,
                        'label'          => (string) ($fieldTranslation?->label ?? $field->code),
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

