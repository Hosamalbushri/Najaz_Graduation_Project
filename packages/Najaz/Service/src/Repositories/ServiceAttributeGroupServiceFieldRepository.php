<?php

namespace Najaz\Service\Repositories;

use Illuminate\Container\Container;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Najaz\Service\Models\ServiceAttributeFieldProxy;
use Najaz\Service\Models\ServiceAttributeTypeProxy;
use Webkul\Core\Eloquent\Repository;

class ServiceAttributeGroupServiceFieldRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceAttributeGroupServiceField';
    }

    public function __construct(
        protected ServiceAttributeTypeRepository $attributeTypeRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Synchronize fields for a pivot relation.
     *
     * @param  \Najaz\Service\Models\ServiceAttributeGroupService  $pivotRelation
     * @param  array  $fields
     * @return void
     */
    public function syncFields(ServiceAttributeGroupService $pivotRelation, array $fields): void
    {
        $existingFieldIds = [];

        foreach ($fields as $fieldData) {
            $fieldData = is_array($fieldData) ? $fieldData : [];
            $field = null;

            // If field has an ID, try to update it (if exists)
            if (isset($fieldData['id']) && $fieldData['id']) {
                $field = $this->find($fieldData['id']);
                
                if ($field && $field->service_attribute_group_service_id == $pivotRelation->id) {
                    // Field exists and belongs to this pivot, update it
                    $attributeType = $field->attributeType;
                    
                    $this->update([
                        'code'             => $fieldData['code'] ?? $field->code,
                        'type'             => $fieldData['type'] ?? $field->type,
                        'sort_order'       => $fieldData['sort_order'] ?? $field->sort_order,
                        'is_required'      => $this->toBoolean($fieldData['is_required'] ?? $field->is_required),
                        'validation_rules' => $this->prepareValidationRules(
                            $fieldData['validation_rules'] ?? $field->validation_rules,
                            $attributeType->regex ?? null
                        ),
                        'default_value'    => $fieldData['default_value'] ?? $field->default_value,
                        'service_attribute_type_id' => $fieldData['service_attribute_type_id'] ?? $field->service_attribute_type_id,
                    ], $field->id);

                    $this->syncTranslations($field, $fieldData);

                    $existingFieldIds[] = $field->id;
                    continue; // Move to next field
                }
                // Field doesn't exist or doesn't belong to this pivot, treat as new field
            }
            
            // If field wasn't found or doesn't have ID, check if field with same code exists
            $fieldCode = $fieldData['code'] ?? null;
            
            // If we have a code, check for existing field with same code in this pivot relation
            if ($fieldCode) {
                $existingFieldByCode = $this->findWhere([
                    'service_attribute_group_service_id' => $pivotRelation->id,
                    'code'                               => $fieldCode,
                ])->first();

                if ($existingFieldByCode) {
                    // Field with same code exists, update it instead of creating new one
                    $attributeType = $existingFieldByCode->attributeType;
                    
                    $this->update([
                        'code'             => $fieldData['code'] ?? $existingFieldByCode->code,
                        'type'             => $fieldData['type'] ?? $existingFieldByCode->type,
                        'sort_order'       => $fieldData['sort_order'] ?? $existingFieldByCode->sort_order,
                        'is_required'      => $this->toBoolean($fieldData['is_required'] ?? $existingFieldByCode->is_required),
                        'validation_rules' => $this->prepareValidationRules(
                            $fieldData['validation_rules'] ?? $existingFieldByCode->validation_rules,
                            $attributeType->regex ?? null
                        ),
                        'default_value'    => $fieldData['default_value'] ?? $existingFieldByCode->default_value,
                        'service_attribute_type_id' => $fieldData['service_attribute_type_id'] ?? $existingFieldByCode->service_attribute_type_id,
                    ], $existingFieldByCode->id);

                    $this->syncTranslations($existingFieldByCode, $fieldData);

                    $existingFieldIds[] = $existingFieldByCode->id;
                    continue; // Move to next field
                }
            }

            // If field wasn't found or doesn't have ID, create new one
            // Check if field has template_field_id or service_attribute_field_id
            if (! empty($fieldData['template_field_id']) || ! empty($fieldData['service_attribute_field_id'])) {
                $templateFieldId = $fieldData['template_field_id'] ?? $fieldData['service_attribute_field_id'];
                $templateField = ServiceAttributeFieldProxy::modelClass()::find($templateFieldId);

                if ($templateField) {
                    // Check if field with same code already exists from template
                    $templateFieldCode = $fieldData['code'] ?? $templateField->code;
                    $existingFieldByCode = $this->findWhere([
                        'service_attribute_group_service_id' => $pivotRelation->id,
                        'code'                               => $templateFieldCode,
                    ])->first();

                    if ($existingFieldByCode) {
                        // Update existing field instead of creating new one
                        $attributeType = $existingFieldByCode->attributeType;
                        
                        $this->update([
                            'code'             => $templateFieldCode,
                            'type'             => $fieldData['type'] ?? $templateField->type,
                            'sort_order'       => $fieldData['sort_order'] ?? $existingFieldByCode->sort_order,
                            'is_required'      => $this->toBoolean($fieldData['is_required'] ?? $templateField->is_required),
                            'validation_rules' => $this->prepareValidationRules(
                                $fieldData['validation_rules'] ?? $templateField->validation_rules,
                                $attributeType->regex ?? null
                            ),
                            'default_value'    => $fieldData['default_value'] ?? $templateField->default_value,
                            'service_attribute_type_id' => $templateField->service_attribute_type_id,
                        ], $existingFieldByCode->id);

                        $this->syncTranslations($existingFieldByCode, $fieldData);

                        $existingFieldIds[] = $existingFieldByCode->id;
                    } else {
                        $field = $this->copyFieldFromTemplate($templateField, $pivotRelation, $fieldData);
                        $existingFieldIds[] = $field->id;
                    }
                }
            }
            // If field has service_attribute_type_id, create new field from type
            elseif (! empty($fieldData['service_attribute_type_id'])) {
                $attributeType = $this->attributeTypeRepository->findOrFail($fieldData['service_attribute_type_id']);

                $fieldCode = $fieldData['code'] ?? $attributeType->code;
                
                // Check if field with same code already exists
                $existingFieldByCode = $this->findWhere([
                    'service_attribute_group_service_id' => $pivotRelation->id,
                    'code'                               => $fieldCode,
                ])->first();

                if ($existingFieldByCode) {
                    // Update existing field instead of creating new one
                    $this->update([
                        'code'             => $fieldCode,
                        'type'             => $fieldData['type'] ?? $attributeType->type,
                        'sort_order'       => $fieldData['sort_order'] ?? $existingFieldByCode->sort_order,
                        'is_required'      => $this->toBoolean($fieldData['is_required'] ?? $attributeType->is_required),
                        'validation_rules' => $this->prepareValidationRules(
                            $fieldData['validation_rules'] ?? $attributeType->validation,
                            $attributeType->regex
                        ),
                        'default_value'    => $fieldData['default_value'] ?? $attributeType->default_value,
                        'service_attribute_type_id' => $attributeType->id,
                    ], $existingFieldByCode->id);

                    $this->syncTranslations($existingFieldByCode, $fieldData);

                    $existingFieldIds[] = $existingFieldByCode->id;
                } else {
                    $fieldDataToSave = [
                        'service_attribute_group_service_id' => $pivotRelation->id,
                        'service_attribute_type_id'          => $attributeType->id,
                        'code'                               => $fieldCode,
                        'type'                               => $fieldData['type'] ?? $attributeType->type,
                        'validation_rules'                   => $this->prepareValidationRules(
                            $fieldData['validation_rules'] ?? $attributeType->validation,
                            $attributeType->regex
                        ),
                        'default_value'                      => $fieldData['default_value'] ?? $attributeType->default_value,
                        'sort_order'                         => $fieldData['sort_order'] ?? 0,
                        'is_required'                        => $this->toBoolean($fieldData['is_required'] ?? $attributeType->is_required),
                    ];

                    $field = $this->create($fieldDataToSave);

                    $this->syncTranslations($field, $fieldData);

                    $existingFieldIds[] = $field->id;
                }
            }
        }

        // Delete fields that are not in the new list
        if (! empty($existingFieldIds)) {
            $pivotRelation->fields()
                ->whereNotIn('id', $existingFieldIds)
                ->delete();
        } else {
            $pivotRelation->fields()->delete();
        }
    }

    /**
     * Copy a field from template to pivot relation.
     *
     * @param  \Najaz\Service\Models\ServiceAttributeField  $templateField
     * @param  \Najaz\Service\Models\ServiceAttributeGroupService  $pivotRelation
     * @param  array  $overrides
     * @return \Najaz\Service\Models\ServiceAttributeGroupServiceField
     */
    public function copyFieldFromTemplate($templateField, ServiceAttributeGroupService $pivotRelation, array $overrides = [])
    {
        $attributeType = $templateField->attributeType;
        
        // Get the original field code from template or overrides
        $originalFieldCode = $overrides['code'] ?? $templateField->code;
        
        // Get the group code (custom_code or fallback to attribute group code)
        // Load attributeGroup if not already loaded
        if (! $pivotRelation->relationLoaded('attributeGroup')) {
            $pivotRelation->load('attributeGroup');
        }
        
        $groupCode = $pivotRelation->custom_code ?? ($pivotRelation->attributeGroup ? $pivotRelation->attributeGroup->code : null) ?? '';
        
        // Merge group code with field code using underscore
        $baseFieldCode = $groupCode ? $groupCode . '_' . $originalFieldCode : $originalFieldCode;
        
        // Generate unique field code by checking for duplicates and adding sequential number if needed
        $fieldCode = $this->generateUniqueFieldCode($pivotRelation, $baseFieldCode, $templateField->service_attribute_type_id);

        // Check if field with same code already exists in this pivot relation
        $existingField = $this->findWhere([
            'service_attribute_group_service_id' => $pivotRelation->id,
            'code'                               => $fieldCode,
        ])->first();

        if ($existingField) {
            // Update existing field instead of creating new one
            $this->update([
                'code'             => $fieldCode,
                'type'             => $overrides['type'] ?? $templateField->type,
                'validation_rules' => $overrides['validation_rules'] ?? $templateField->validation_rules,
                'default_value'    => $overrides['default_value'] ?? $templateField->default_value,
                'is_required'      => $this->toBoolean($overrides['is_required'] ?? $templateField->is_required),
                'sort_order'       => $overrides['sort_order'] ?? $templateField->sort_order,
                'service_attribute_field_id' => $templateField->id,
                'service_attribute_type_id'  => $templateField->service_attribute_type_id,
            ], $existingField->id);

            // Sync translations
            $translationData = [];
            if (isset($overrides['label']) && is_array($overrides['label'])) {
                $translationData = $overrides['label'];
            } else {
                // Load translations if not already loaded
                if (! $templateField->relationLoaded('translations')) {
                    $templateField->load('translations');
                }
                
                foreach (core()->getAllLocales() as $locale) {
                    // Try to get translation from loaded translations first
                    $translation = null;
                    if ($templateField->relationLoaded('translations')) {
                        $translation = $templateField->translations->firstWhere('locale', $locale->code);
                    }
                    
                    // Fallback to translate method if translations not loaded
                    if (! $translation) {
                        $translation = $templateField->translate($locale->code);
                    }
                    
                    $translationData[$locale->code] = $translation?->label ?? '';
                }
            }

            $this->syncTranslations($existingField, ['label' => $translationData]);

            // Copy options from template field if it has options (only if field doesn't have options yet)
            if ($existingField->options()->count() === 0) {
                $this->copyFieldOptionsFromTemplate($templateField, $existingField);
            }

            return $existingField;
        }

        $fieldData = [
            'service_attribute_group_service_id' => $pivotRelation->id,
            'service_attribute_field_id'         => $templateField->id,
            'service_attribute_type_id'          => $templateField->service_attribute_type_id,
            'code'                               => $fieldCode,
            'type'                               => $overrides['type'] ?? $templateField->type,
            'validation_rules'                   => $overrides['validation_rules'] ?? $templateField->validation_rules,
            'default_value'                      => $overrides['default_value'] ?? $templateField->default_value,
            'is_required'                        => $this->toBoolean($overrides['is_required'] ?? $templateField->is_required),
            'sort_order'                         => $overrides['sort_order'] ?? $templateField->sort_order,
        ];

        $field = $this->create($fieldData);

        // Copy translations
        $translationData = [];
        if (isset($overrides['label']) && is_array($overrides['label'])) {
            $translationData = $overrides['label'];
        } else {
            // Load translations if not already loaded
            if (! $templateField->relationLoaded('translations')) {
                $templateField->load('translations');
            }
            
            foreach (core()->getAllLocales() as $locale) {
                // Try to get translation from loaded translations first
                $translation = null;
                if ($templateField->relationLoaded('translations')) {
                    $translation = $templateField->translations->firstWhere('locale', $locale->code);
                }
                
                // Fallback to translate method if translations not loaded
                if (! $translation) {
                    $translation = $templateField->translate($locale->code);
                }
                
                $translationData[$locale->code] = $translation?->label ?? '';
            }
        }

        $this->syncTranslations($field, ['label' => $translationData]);

        // Copy options from template field if it has options
        $this->copyFieldOptionsFromTemplate($templateField, $field);

        return $field;
    }

    /**
     * Generate a unique field code by checking for duplicates and adding sequential number if needed.
     *
     * @param  \Najaz\Service\Models\ServiceAttributeGroupService  $pivotRelation
     * @param  string  $baseCode
     * @param  int|null  $attributeTypeId
     * @return string
     */
    protected function generateUniqueFieldCode(ServiceAttributeGroupService $pivotRelation, string $baseCode, ?int $attributeTypeId = null): string
    {
        // Check if base code already exists
        $existingField = $this->findWhere([
            'service_attribute_group_service_id' => $pivotRelation->id,
            'code'                               => $baseCode,
        ])->first();

        // If base code doesn't exist, return it
        if (! $existingField) {
            return $baseCode;
        }

        // If attribute type is provided, check if we should allow duplicates of the same type
        // Otherwise, add sequential number
        $counter = 2;
        $uniqueCode = $baseCode . '_' . $counter;

        // Keep incrementing until we find a unique code
        while ($this->findWhere([
            'service_attribute_group_service_id' => $pivotRelation->id,
            'code'                               => $uniqueCode,
        ])->first()) {
            $counter++;
            $uniqueCode = $baseCode . '_' . $counter;
        }

        return $uniqueCode;
    }

    /**
     * Copy field options from template field to the new field.
     *
     * @param  \Najaz\Service\Models\ServiceAttributeField  $templateField
     * @param  \Najaz\Service\Models\ServiceAttributeGroupServiceField  $field
     * @return void
     */
    protected function copyFieldOptionsFromTemplate($templateField, $field): void
    {
        // Check if the template field has options (from attribute type)
        $attributeType = $templateField->attributeType;
        
        if (! $attributeType) {
            return;
        }

        // Check if attribute type requires options
        $optionTypes = ['select', 'multiselect', 'radio', 'checkbox'];
        if (! in_array($attributeType->type, $optionTypes)) {
            return;
        }

        // Get options from attribute type
        $attributeTypeOptions = $attributeType->options ?? collect();

        if ($attributeTypeOptions->isEmpty()) {
            return;
        }

        // Get the field option repository
        $fieldOptionRepository = app(\Najaz\Service\Repositories\ServiceAttributeGroupServiceFieldOptionRepository::class);

        // Copy each option
        foreach ($attributeTypeOptions as $originalOption) {
            $optionLabels = [];
            foreach (core()->getAllLocales() as $locale) {
                $optionTranslation = $originalOption->translate($locale->code);
                $optionLabels[$locale->code] = $optionTranslation?->label ?? $originalOption->admin_name ?? $originalOption->code ?? '';
            }

            // Use persistOption method which handles translations properly
            $fieldOptionRepository->persistOption($field, [
                'admin_name' => $originalOption->admin_name ?? $originalOption->code ?? '',
                'sort_order' => $originalOption->sort_order ?? 0,
                'label'      => $optionLabels,
            ]);
        }
    }

    /**
     * Sync translations for a field.
     *
     * @param  \Najaz\Service\Models\ServiceAttributeGroupServiceField  $field
     * @param  array  $fieldData
     * @return void
     */
    protected function syncTranslations($field, array $fieldData): void
    {
        $labels = $fieldData['label'] ?? $fieldData['labels'] ?? [];

        foreach (core()->getAllLocales() as $locale) {
            $label = $labels[$locale->code] ?? $labels['label'] ?? '';

            $field->translateOrNew($locale->code)->fill([
                'label' => $label,
            ])->save();
        }
    }

    /**
     * Convert value to boolean.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function toBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

    /**
     * Prepare validation rules.
     *
     * @param  mixed  $rules
     * @param  string|null  $regex
     * @return array|null
     */
    protected function prepareValidationRules($rules, $regex = null): ?array
    {
        if (is_array($rules)) {
            return $rules ?: null;
        }

        $formatted = is_string($rules) ? trim($rules) : null;

        if (! $formatted) {
            return null;
        }

        if ($formatted === 'regex') {
            $pattern = is_string($regex) ? trim($regex) : '';

            if (! $pattern) {
                return null;
            }

            return ['validation' => 'regex:'.$pattern];
        }

        return ['validation' => $formatted];
    }
}

