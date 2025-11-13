<?php

namespace Najaz\Service\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;

class ServiceAttributeFieldRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceAttributeField';
    }

    public function __construct(
        protected ServiceAttributeTypeRepository $attributeTypeRepository,
        Container $container

    ) {
        parent::__construct($container);

    }

    /**
     * Validate fields payload against group type specific requirements.
     */
    public function validateGroupTypeFields(string $groupType, array $fields): bool
    {
        if ($groupType !== 'citizen') {
            return true;
        }


        foreach ($fields as $fieldData) {
            if (! empty($fieldData['service_attribute_type_id'])) {
                $attributeType = $this->attributeTypeRepository->find($fieldData['service_attribute_type_id']);

                if ($attributeType && $attributeType->code === 'id_number') {
                    return true;
                }
            } elseif (! empty($fieldData['id'])) {
                $existingField = $this->find($fieldData['id']);

                if ($existingField && $existingField->code === 'id_number') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Synchronize attribute group fields with incoming payload.
     */
    public function syncGroupFields($attributeGroup, array $fields): void
    {
        $existingFieldIds = [];

        foreach ($fields as $fieldData) {
            if (isset($fieldData['id']) && $fieldData['id']) {
                $field = $this->findOrFail($fieldData['id']);
                $attributeType = $this->attributeTypeRepository->findOrFail($field->service_attribute_type_id);

                $this->update([
                    'sort_order'       => $fieldData['sort_order'] ?? $field->sort_order,
                    'is_required'      => $this->toBoolean($fieldData['is_required'] ?? $field->is_required),
                    'validation_rules' => $this->prepareValidationRules(
                        $fieldData['validation_rules'] ?? $field->validation_rules,
                        $attributeType->regex
                    ),
                    'default_value'    => $fieldData['default_value'] ?? $field->default_value,
                ], $field->id);

                $this->syncTranslations($field, $fieldData);

                $existingFieldIds[] = $field->id;
            } elseif (! empty($fieldData['service_attribute_type_id'])) {
                $attributeType = $this->attributeTypeRepository->findOrFail($fieldData['service_attribute_type_id']);

                $fieldDataToSave = [
                    'service_attribute_group_id' => $attributeGroup->id,
                    'service_attribute_type_id'  => $attributeType->id,
                    'code'                       => $attributeType->code,
                    'type'                       => $attributeType->type,
                    'validation_rules'           => $this->prepareValidationRules(
                        $fieldData['validation_rules'] ?? $attributeType->validation,
                        $attributeType->regex
                    ),
                    'default_value'             => $fieldData['default_value'] ?? $attributeType->default_value,
                    'sort_order'                => $fieldData['sort_order'] ?? 0,
                    'is_required'               => $this->toBoolean($fieldData['is_required'] ?? $attributeType->is_required),
                ];

                $field = $this->create($fieldDataToSave);

                $this->syncTranslations($field, $fieldData);

                $existingFieldIds[] = $field->id;
            }
        }

        if (method_exists($attributeGroup, 'fields')) {
            $fieldsRelation = $attributeGroup->fields();

            if (! empty($existingFieldIds)) {
                $fieldsRelation->whereNotIn('id', $existingFieldIds)->delete();
            } else {
                $fieldsRelation->delete();
            }
        }
    }

    protected function syncTranslations($field, array $fieldData): void
    {
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'label' => $fieldData['label'][$locale->code] ?? '',
            ];

            $field->translateOrNew($locale->code)->fill($translationData)->save();
        }
    }

    protected function toBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }

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
