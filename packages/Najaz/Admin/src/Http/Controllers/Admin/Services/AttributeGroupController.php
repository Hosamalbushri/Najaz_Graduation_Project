<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\AttributeGroupDataGrid;
use Najaz\Service\Repositories\ServiceAttributeFieldRepository;
use Najaz\Service\Repositories\ServiceAttributeGroupRepository;
use Najaz\Admin\Http\Controllers\Controller;
use Webkul\Attribute\Enums\ValidationEnum;

class AttributeGroupController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceAttributeGroupRepository $dataGroupRepository,
        protected ServiceAttributeFieldRepository $fieldRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(AttributeGroupDataGrid::class)->process();
        }

        return view('admin::services.attribute-groups.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): \Symfony\Component\HttpFoundation\Response
    {
        $rules = [
            'default_name' => 'required|string|max:255',
            'code'         => 'required|string|max:255|unique:service_attribute_groups,code',
            'group_type'   => 'required|in:general,citizen',
            'sort_order'   => 'nullable|integer',
        ];
        $this->validate(request(), $rules);
        $data = [
            'code'         => request()->input('code'),
            'default_name' => request()->input('default_name'),
            'group_type'   => request()->input('group_type', 'general'),
            'sort_order'   => request()->input('sort_order', 0),
        ];

        $attributeGroup = $this->dataGroupRepository->create($data);
        if (request()->expectsJson()) {
            return new JsonResponse([
                'message'     => trans('Admin::app.services.attribute-groups.create-success'),
                'redirect_to' => route('admin.attribute-groups.index'),
            ]);
        }

        return new JsonResponse([
            'message'     => trans('Admin::app.services.attribute-groups.create-success'),
            'redirect_to' => route('admin.attribute-groups.edit', $attributeGroup->id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $attributeGroup = $this->dataGroupRepository->with(['fields.translations', 'fields.attributeType.translations'])->findOrFail($id);
        $attributeTypes = app(\Najaz\Service\Repositories\ServiceAttributeTypeRepository::class)
            ->with('translations')
            ->all();
        $validations = ValidationEnum::getValues();
        $validationLabels = collect($validations)->mapWithKeys(fn ($value) => [
            $value => trans('Admin::app.services.attribute-types.validation-options.'.$value),
        ]);

        return view('admin::services.attribute-groups.edit', [
            'attributeGroup'   => $attributeGroup,
            'attributeTypes'   => $attributeTypes,
            'validations'      => $validations,
            'validationLabels' => $validationLabels,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $rules = [
            'default_name'                       => 'required|string|max:255',
            'code'                               => 'required|string|max:255|unique:service_attribute_groups,code,'.$id,
            'group_type'                         => 'required|in:general,citizen',
            'sort_order'                         => 'nullable|integer',
            'attribute_type_ids'                 => 'nullable|array',
            'attribute_type_ids.*'               => 'integer|exists:service_attribute_types,id',
            'fields'                             => 'nullable|array',
            'fields.*.service_attribute_type_id' => 'nullable|integer|exists:service_attribute_types,id',
            'fields.*.is_required'               => 'nullable|boolean',
            'fields.*.sort_order'                => 'nullable|integer',
            'fields.*.validation_rules'          => 'nullable|string',
            'fields.*.default_value'             => 'nullable|string',
        ];

        foreach (core()->getAllLocales() as $locale) {
            $rules['name.'.$locale->code] = 'required|string|max:255';
            $rules['description.'.$locale->code] = 'nullable|string';
        }

        $this->validate(request(), $rules);

        $data = [
            'code'         => request()->input('code'),
            'default_name' => request()->input('default_name'),
            'group_type'   => request()->input('group_type', 'general'),
            'sort_order'   => request()->input('sort_order', 0),
        ];

        $attributeGroup = $this->dataGroupRepository->update($data, $id);

        // Update translations
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'name'        => request()->input("name.{$locale->code}"),
                'description' => request()->input("description.{$locale->code}"),
            ];

            $attributeGroup->translateOrNew($locale->code)->fill($translationData)->save();
        }

        // Handle fields payload (drag/drop modal)
        if (request()->has('fields')) {
            $fields = request()->input('fields', []);

            if (! $this->fieldRepository->validateGroupTypeFields($data['group_type'], $fields)) {
                return new JsonResponse([
                    'message'     => trans('Admin::app.services.attribute-groups.validation.citizen-id-number-required'),
                ]);
            }

            $this->fieldRepository->syncGroupFields($attributeGroup, $fields);
        }

        return new JsonResponse([
            'message'     => trans('Admin::app.services.attribute-groups.update-success'),
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
            'message' => trans('Admin::app.services.attribute-groups.delete-success'),
        ]);
    }

    /**
     * Mass delete attribute groups.
     */
    public function massDestroy(): JsonResponse
    {
        $indices = request()->input('indices', []);

        foreach ($indices as $id) {
            $this->dataGroupRepository->delete($id);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.index.datagrid.mass-delete-success'),
        ]);
    }
}
