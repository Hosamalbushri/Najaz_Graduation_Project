<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Najaz\Service\Repositories\ServiceAttributeFieldRepository;
use Najaz\Service\Repositories\ServiceAttributeGroupRepository;
use Najaz\Service\Repositories\ServiceAttributeTypeRepository;
use Webkul\Admin\Http\Controllers\Controller;

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
            'validation_rules'          => $attributeType->validation ? ['validation' => $attributeType->validation] : null,
            'default_value'             => $attributeType->default_value,
            'sort_order'                => request()->input('sort_order', 0),
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
        ]);

        $this->dataGroupRepository->findOrFail($groupId);
        $field = $this->fieldRepository->findOrFail($fieldId);

        // Only allow updating label and sort_order
        $data = [
            'sort_order' => request()->input('sort_order', $field->sort_order),
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
        $this->dataGroupRepository->findOrFail($groupId);
        $this->fieldRepository->findOrFail($fieldId);

        $this->fieldRepository->delete($fieldId);

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.delete-success'),
        ]);
    }
}


