<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\DataGroupDataGrid;
use Najaz\Service\Repositories\ServiceDataGroupFieldRepository;
use Najaz\Service\Repositories\ServiceDataGroupRepository;
use Webkul\Admin\Http\Controllers\Controller;

class DataGroupController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceDataGroupRepository $dataGroupRepository,
        protected ServiceDataGroupFieldRepository $fieldRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(DataGroupDataGrid::class)->process();
        }

        return view('admin::services.data-groups.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $locales = core()->getAllLocales();

        return view('admin::services.data-groups.create',compact('locales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'code'        => 'required|string|max:255|unique:service_data_groups,code',
            'name.*'      => 'required|string|max:255',
            'description' => 'nullable|array',
            'sort_order'  => 'nullable|integer',
        ]);

        $data = [
            'code'       => request()->input('code'),
            'sort_order' => request()->input('sort_order', 0),
        ];

        $dataGroup = $this->dataGroupRepository->create($data);

        // Save translations
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'name'        => request()->input("{$locale->code}.name"),
                'description' => request()->input("{$locale->code}.description"),
            ];

            $dataGroup->translateOrNew($locale->code)->fill($translationData)->save();
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.data-groups.create-success'),
            'data'    => $dataGroup->load('fields'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $dataGroup = $this->dataGroupRepository->with(['fields.translations', 'fields.fieldType.translations'])->findOrFail($id);
        $fieldTypes = app(\Najaz\Service\Repositories\ServiceFieldTypeRepository::class)
            ->with('translations')
            ->all();

        return view('admin::services.data-groups.edit', compact('dataGroup', 'fieldTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'code'        => 'required|string|max:255|unique:service_data_groups,code,' . $id,
            'name'        => 'required|array',
            'name.*'      => 'required|string|max:255',
            'description' => 'nullable|array',
            'sort_order'  => 'nullable|integer',
        ]);

        $data = [
            'code'       => request()->input('code'),
            'sort_order' => request()->input('sort_order', 0),
        ];

        $dataGroup = $this->dataGroupRepository->update($data, $id);

        // Update translations
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'name'        => request()->input("name.{$locale->code}"),
                'description' => request()->input("description.{$locale->code}"),
            ];

            $dataGroup->translateOrNew($locale->code)->fill($translationData)->save();
        }

        // Handle fields
        if (request()->has('fields')) {
            $fields = request()->input('fields', []);
            $existingFieldIds = [];

            foreach ($fields as $fieldData) {
                if (isset($fieldData['id']) && $fieldData['id']) {
                    // Update existing field
                    $field = $this->fieldRepository->findOrFail($fieldData['id']);
                    
                    $this->fieldRepository->update([
                        'sort_order' => $fieldData['sort_order'] ?? $field->sort_order,
                    ], $field->id);

                    // Update translations
                    foreach (core()->getAllLocales() as $locale) {
                        $translationData = [
                            'label' => $fieldData['label'][$locale->code] ?? '',
                        ];
                        $field->translateOrNew($locale->code)->fill($translationData)->save();
                    }

                    $existingFieldIds[] = $field->id;
                } else {
                    // Create new field
                    if (isset($fieldData['service_field_type_id']) && $fieldData['service_field_type_id']) {
                        $fieldType = app(\Najaz\Service\Repositories\ServiceFieldTypeRepository::class)
                            ->findOrFail($fieldData['service_field_type_id']);

                        $fieldDataToSave = [
                            'service_data_group_id' => $id,
                            'service_field_type_id' => $fieldType->id,
                            'code'                  => $fieldType->code,
                            'type'                  => $fieldType->type,
                            'validation_rules'      => $fieldType->validation ? ['validation' => $fieldType->validation] : null,
                            'default_value'         => $fieldType->default_value,
                            'sort_order'            => $fieldData['sort_order'] ?? 0,
                        ];

                        $field = $this->fieldRepository->create($fieldDataToSave);

                        // Save translations
                        foreach (core()->getAllLocales() as $locale) {
                            $translationData = [
                                'label' => $fieldData['label'][$locale->code] ?? '',
                            ];
                            $field->translateOrNew($locale->code)->fill($translationData)->save();
                        }

                        $existingFieldIds[] = $field->id;
                    }
                }
            }

            // Delete fields that were removed
            $dataGroup->fields()->whereNotIn('id', $existingFieldIds)->delete();
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.data-groups.update-success'),
            'data'    => $dataGroup->fresh(['fields.fieldType', 'fields.translations']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->dataGroupRepository->findOrFail($id);

        $this->dataGroupRepository->delete($id);

        return new JsonResponse([
            'message' => trans('Admin::app.services.data-groups.delete-success'),
        ]);
    }

    /**
     * Mass delete data groups.
     */
    public function massDestroy(): JsonResponse
    {
        $indices = request()->input('indices', []);

        foreach ($indices as $id) {
            $this->dataGroupRepository->delete($id);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.data-groups.index.datagrid.mass-delete-success'),
        ]);
    }
}

