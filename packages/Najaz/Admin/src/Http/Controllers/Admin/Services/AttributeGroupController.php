<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\AttributeGroupDataGrid;
use Najaz\Service\Models\ServiceAttributeTypeProxy;
use Najaz\Service\Repositories\ServiceAttributeFieldRepository;
use Najaz\Service\Repositories\ServiceAttributeGroupRepository;
use Webkul\Admin\Http\Controllers\Controller;

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
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $locales = core()->getAllLocales();

        return view('admin::services.attribute-groups.create', [
            'locales' => $locales,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): \Illuminate\Http\RedirectResponse
    {
        $rules = [
            'code'       => 'required|string|max:255|unique:service_attribute_groups,code',
            'sort_order' => 'nullable|integer',
        ];

        foreach (core()->getAllLocales() as $locale) {
            $rules['name.' . $locale->code] = 'required|string|max:255';
            $rules['description.' . $locale->code] = 'nullable|string';
        }

        $this->validate(request(), $rules);

        $data = [
            'code'       => request()->input('code'),
            'sort_order' => request()->input('sort_order', 0),
        ];

        $attributeGroup = $this->dataGroupRepository->create($data);

        // Save translations
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'name'        => request()->input("name.{$locale->code}"),
                'description' => request()->input("description.{$locale->code}"),
            ];

            $attributeGroup->translateOrNew($locale->code)->fill($translationData)->save();
        }
        session()->flash('success', trans('Admin::app.services.attribute-groups.create-success'));

        return redirect()->route('admin.attribute-groups.edit',$attributeGroup->id);

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

        return view('admin::services.attribute-groups.edit', [
            'attributeGroup' => $attributeGroup,
            'attributeTypes' => $attributeTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): \Illuminate\Http\RedirectResponse
    {
        $rules = [
            'code'                => 'required|string|max:255|unique:service_attribute_groups,code,' . $id,
            'sort_order'          => 'nullable|integer',
            'attribute_type_ids'  => 'nullable|array',
            'attribute_type_ids.*'=> 'integer|exists:service_attribute_types,id',
        ];

        foreach (core()->getAllLocales() as $locale) {
            $rules['name.' . $locale->code] = 'required|string|max:255';
            $rules['description.' . $locale->code] = 'nullable|string';
        }

        $this->validate(request(), $rules);

        $data = [
            'code'       => request()->input('code'),
            'sort_order' => request()->input('sort_order', 0),
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
                    if (isset($fieldData['service_attribute_type_id']) && $fieldData['service_attribute_type_id']) {
                        $attributeType = app(\Najaz\Service\Repositories\ServiceAttributeTypeRepository::class)
                            ->findOrFail($fieldData['service_attribute_type_id']);

                        $fieldDataToSave = [
                            'service_attribute_group_id' => $id,
                            'service_attribute_type_id'  => $attributeType->id,
                            'code'                      => $attributeType->code,
                            'type'                      => $attributeType->type,
                            'validation_rules'          => $attributeType->validation ? ['validation' => $attributeType->validation] : null,
                            'default_value'             => $attributeType->default_value,
                            'sort_order'                => $fieldData['sort_order'] ?? 0,
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
            $attributeGroup->fields()->whereNotIn('id', $existingFieldIds)->delete();
        }

        $attributeTypeIds = collect(request()->input('attribute_type_ids', []))
            ->filter()
            ->unique()
            ->values();

        if ($attributeTypeIds->isNotEmpty()) {
            $existingTypeIds = $attributeGroup->fields()->pluck('service_attribute_type_id')->toArray();
            $sortIndex = ($attributeGroup->fields()->max('sort_order') ?? -1) + 1;

            foreach ($attributeTypeIds as $typeId) {
                if (in_array($typeId, $existingTypeIds, true)) {
                    continue;
                }

                $attributeType = ServiceAttributeTypeProxy::modelClass()::with('translations')->find($typeId);

                if (! $attributeType) {
                    continue;
                }

                $field = $this->fieldRepository->create([
                    'service_attribute_group_id' => $attributeGroup->id,
                    'service_attribute_type_id'  => $attributeType->id,
                    'code'                      => $attributeType->code,
                    'type'                      => $attributeType->type,
                    'validation_rules'          => $attributeType->validation ? ['validation' => $attributeType->validation] : null,
                    'default_value'             => $attributeType->default_value,
                    'sort_order'                => $sortIndex++,
                ]);

                foreach (core()->getAllLocales() as $locale) {
                    $label = $attributeType->translate($locale->code)?->name
                        ?? $attributeType->code;

                    $field->translateOrNew($locale->code)->fill([
                        'label' => $label,
                    ])->save();
                }
            }
        }
        session()->flash('success', trans('Admin::app.services.attribute-groups.update-success'));

        return redirect()->route('admin.attribute-groups.index');

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


