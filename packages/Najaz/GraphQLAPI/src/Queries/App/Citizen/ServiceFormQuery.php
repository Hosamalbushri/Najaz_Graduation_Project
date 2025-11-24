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

        // Eager-load nested relations if not already loaded.
        $rootValue->loadMissing([
            'attributeGroups.fields.attributeType.options',
        ]);

        // Load pivot relations with saved fields
        $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
            'fields.attributeType.options',
            'fields.translations',
            'attributeGroup'
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

                    $options = $attributeType
                        ? $attributeType->options->map(function ($option) {
                            return [
                                'value' => (string) $option->id,
                                'label' => (string) ($option->label ?? $option->admin_name),
                            ];
                        })->values()->all()
                        : [];

                    return [
                        'code'           => $field->code,
                        'label'          => (string) ($field->label ?? $field->code),
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

