<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Najaz\Service\Repositories\ServiceAttributeFieldRepository;
use Najaz\Service\Repositories\ServiceAttributeGroupRepository;
use Najaz\Service\Repositories\ServiceAttributeTypeRepository;
use Najaz\Admin\Http\Controllers\Controller;

class AttributeGroupFieldController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceAttributeGroupRepository $dataGroupRepository,
        protected ServiceAttributeFieldRepository $fieldRepository,
        protected ServiceAttributeTypeRepository $fieldTypeRepository
    ) {}

    /**
     * Store a newly created field in storage.
     */
    public function store(int $groupId): JsonResponse
    {
        $this->validate(request(), [
            'service_attribute_type_id' => 'required|exists:service_attribute_types,id',
            'label'                     => 'required|array',
            'label.*'                   => 'required|string|max:255',
            'sort_order'                => 'nullable|integer',
            'is_required'               => 'nullable|boolean',
            'validation_rules'          => 'nullable|string',
            'default_value'             => 'nullable|string',
        ]);

        $this->dataGroupRepository->findOrFail($groupId);
        $attributeType = $this->fieldTypeRepository->findOrFail(request()->input('service_attribute_type_id'));

        // Check if this field type already exists in this group
        $existingField = $this->fieldRepository->findWhere([
            'service_attribute_group_id' => $groupId,
            'service_attribute_type_id'  => $attributeType->id,
        ])->first();

        if ($existingField) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.type-already-added'),
            ], 422);
        }

        // Copy data from attribute type
        $data = [
            'service_attribute_group_id' => $groupId,
            'service_attribute_type_id'  => $attributeType->id,
            'code'                      => $attributeType->code,
            'type'                      => $attributeType->type,
            'validation_rules'          => $this->prepareValidationRules(
                request()->input('validation_rules', $attributeType->validation),
                $attributeType->regex
            ),
            'default_value'             => request()->input('default_value', $attributeType->default_value),
            'sort_order'                => request()->input('sort_order', 0),
            'is_required'               => request()->boolean('is_required', $attributeType->is_required),
        ];

        $field = $this->fieldRepository->create($data);

        // Save translations (label from user input)
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'label' => request()->input("label.{$locale->code}"),
            ];

            $field->translateOrNew($locale->code)->fill($translationData)->save();
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.create-success'),
            'data'    => $field->load('attributeType'),
        ]);
    }

    /**
     * Update the specified field in storage.
     */
    public function update(int $groupId, int $fieldId): JsonResponse
    {
        $this->validate(request(), [
            'label'      => 'required|array',
            'label.*'    => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_required' => 'nullable|boolean',
            'validation_rules' => 'nullable|string',
            'default_value'    => 'nullable|string',
        ]);

        $this->dataGroupRepository->findOrFail($groupId);
        $field = $this->fieldRepository->findOrFail($fieldId);
        $attributeType = $this->fieldTypeRepository->findOrFail($field->service_attribute_type_id);

        // Only allow updating label, sort_order and is_required flag
        $data = [
            'sort_order' => request()->input('sort_order', $field->sort_order),
            'is_required' => request()->boolean('is_required', $field->is_required),
            'validation_rules' => $this->prepareValidationRules(
                request()->input('validation_rules', $field->validation_rules),
                $attributeType->regex
            ),
            'default_value' => request()->input('default_value', $field->default_value),
        ];

        $field = $this->fieldRepository->update($data, $fieldId);

        // Update translations (label only)
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'label' => request()->input("label.{$locale->code}"),
            ];

            $field->translateOrNew($locale->code)->fill($translationData)->save();
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.update-success'),
            'data'    => $field->fresh(['attributeType']),
        ]);
    }

    /**
     * Remove the specified field from storage.
     */
    public function destroy(int $groupId, int $fieldId): JsonResponse
    {
        $attributeGroup = $this->dataGroupRepository->findOrFail($groupId);
        $field = $this->fieldRepository->findOrFail($fieldId);

        // Prevent deletion of national_id_card field in citizen groups
        if ($attributeGroup->group_type === 'citizen') {
            $attributeType = $this->fieldTypeRepository->find($field->service_attribute_type_id);
            if ($attributeType && $attributeType->code === 'national_id_card') {
                return new JsonResponse([
                    'message' => trans('Admin::app.services.attribute-groups.edit.cannot-delete-protected-field'),
                ], 422);
            }
        }

        $this->fieldRepository->delete($fieldId);

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.delete-success'),
        ]);
    }

    /**
     * Get fields data for the attribute group.
     */
    public function getData(int $groupId): JsonResponse
    {
        $attributeGroup = $this->dataGroupRepository->with([
            'fields.translations',
            'fields.attributeType.translations',
        ])->findOrFail($groupId);

        $attributeTypes = $this->fieldTypeRepository
            ->with(['translations', 'options.translations'])
            ->orderBy('position')
            ->get()
            ->map(function ($type) {
                $translation = $type->translate(app()->getLocale());
                
                // Get options with translations
                $options = [];
                if ($type->options) {
                    $allLocales = core()->getAllLocales();
                    foreach ($type->options as $option) {
                        $optionLabels = [];
                        foreach ($allLocales as $loc) {
                            $optionTranslation = $option->translate($loc->code);
                            $optionLabels[$loc->code] = $optionTranslation?->label ?? $option->admin_name ?? $option->code ?? '';
                        }
                        $options[] = [
                            'id' => $option->id,
                            'code' => $option->code,
                            'admin_name' => $option->admin_name,
                            'labels' => $optionLabels,
                            'sort_order' => $option->sort_order ?? 0,
                        ];
                    }
                }
                
                return [
                    'id'           => $type->id,
                    'code'         => $type->code,
                    'type'         => $type->type,
                    'name'         => $translation?->name ?? $type->code,
                    'translations' => $type->translations->map(fn($t) => [
                        'locale' => $t->locale,
                        'name'   => $t->name,
                    ])->toArray(),
                    'validation'   => $type->validation,
                    'regex'        => $type->regex,
                    'default_value' => $type->default_value,
                    'is_required'  => $type->is_required,
                    'is_unique'    => $type->is_unique,
                    'options'      => $options,
                ];
            });

        $fields = $attributeGroup->fields->map(function ($field) {
            // Get labels for all locales
            $labels = [];
            foreach (core()->getAllLocales() as $locale) {
                $translation = $field->translate($locale->code);
                $labels[$locale->code] = $translation?->label ?? '';
            }

            return [
                'id' => $field->id,
                'service_attribute_type_id' => $field->service_attribute_type_id,
                'code' => $field->code,
                'type' => $field->type,
                'validation_rules' => $field->validation_rules,
                'default_value' => $field->default_value,
                'is_required' => $field->is_required,
                'sort_order' => $field->sort_order,
                'labels' => $labels,
                'translations' => $field->translations->map(fn($t) => [
                    'locale' => $t->locale,
                    'label' => $t->label,
                ])->toArray(),
                'attributeType' => $field->attributeType ? [
                    'id' => $field->attributeType->id,
                    'code' => $field->attributeType->code,
                    'type' => $field->attributeType->type,
                    'validation' => $field->attributeType->validation,
                    'regex' => $field->attributeType->regex,
                    'default_value' => $field->attributeType->default_value,
                    'is_required' => $field->attributeType->is_required,
                    'is_unique' => $field->attributeType->is_unique,
                    'translations' => $field->attributeType->translations->map(fn($t) => [
                        'locale' => $t->locale,
                        'name' => $t->name,
                    ])->toArray(),
                ] : null,
            ];
        })->toArray();

        return new JsonResponse([
            'fields' => $fields,
            'attributeTypes' => $attributeTypes,
        ]);
    }

    /**
     * Reorder fields.
     */
    public function reorder(int $groupId): JsonResponse
    {
        $this->dataGroupRepository->findOrFail($groupId);

        $this->validate(request(), [
            'fields' => 'required|array',
            'fields.*.id' => 'required|integer|exists:service_attribute_fields,id',
            'fields.*.sort_order' => 'required|integer',
        ]);

        $fields = request()->input('fields', []);

        foreach ($fields as $fieldData) {
            $this->fieldRepository->update([
                'sort_order' => $fieldData['sort_order'],
            ], $fieldData['id']);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.reorder-success'),
        ]);
    }

    /**
     * Normalize validation rules input into stored JSON structure.
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

            return ['validation' => 'regex:' . $pattern];
        }

        return ['validation' => $formatted];
    }
}


