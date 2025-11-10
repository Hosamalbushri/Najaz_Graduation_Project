<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Najaz\Service\Repositories\ServiceDataGroupFieldRepository;
use Najaz\Service\Repositories\ServiceDataGroupRepository;
use Najaz\Service\Repositories\ServiceFieldTypeRepository;
use Webkul\Admin\Http\Controllers\Controller;

class DataGroupFieldController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceDataGroupRepository $dataGroupRepository,
        protected ServiceDataGroupFieldRepository $fieldRepository,
        protected ServiceFieldTypeRepository $fieldTypeRepository
    ) {}

    /**
     * Store a newly created field in storage.
     */
    public function store(int $groupId): JsonResponse
    {
        $this->validate(request(), [
            'service_field_type_id' => 'required|exists:service_field_types,id',
            'label'                 => 'required|array',
            'label.*'               => 'required|string|max:255',
            'sort_order'            => 'nullable|integer',
        ]);

        $dataGroup = $this->dataGroupRepository->findOrFail($groupId);
        $fieldType = $this->fieldTypeRepository->findOrFail(request()->input('service_field_type_id'));

        // Check if this field type already exists in this group
        $existingField = $this->fieldRepository->findWhere([
            'service_data_group_id' => $groupId,
            'service_field_type_id' => $fieldType->id,
        ])->first();

        if ($existingField) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.data-groups.data-group-fields.field-type-exists'),
            ], 422);
        }

        // Copy data from field type
        $data = [
            'service_data_group_id' => $groupId,
            'service_field_type_id' => $fieldType->id,
            'code'                  => $fieldType->code,
            'type'                  => $fieldType->type,
            'validation_rules'      => $fieldType->validation ? ['validation' => $fieldType->validation] : null,
            'default_value'         => $fieldType->default_value,
            'sort_order'            => request()->input('sort_order', 0),
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
            'message' => trans('Admin::app.services.data-groups.data-group-fields.create-success'),
            'data'    => $field->load('fieldType'),
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

        $dataGroup = $this->dataGroupRepository->findOrFail($groupId);
        $field = $this->fieldRepository->findOrFail($fieldId);

        // Only allow updating label and sort_order
        // Field type and other properties come from ServiceFieldType
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
            'message' => trans('Admin::app.services.data-groups.data-group-fields.update-success'),
            'data'    => $field->fresh(['fieldType']),
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
            'message' => trans('Admin::app.services.data-groups.data-group-fields.delete-success'),
        ]);
    }
}




