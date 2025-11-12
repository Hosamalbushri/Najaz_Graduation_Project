<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\AttributeGroupDataGrid;
use Najaz\Service\Repositories\ServiceAttributeFieldRepository;
use Najaz\Service\Repositories\ServiceAttributeGroupRepository;
use Webkul\Admin\Http\Controllers\Controller;
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
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:255|unique:service_attribute_groups,code',
            'group_type' => 'required|in:general,citizen',
            'sort_order' => 'nullable|integer',
        ];
        $this->validate(request(), $rules);
        $data = [
            'code'       => request()->input('code'),
            'group_type' => request()->input('group_type', 'general'),
            'sort_order' => request()->input('sort_order', 0),
        ];

        $attributeGroup = $this->dataGroupRepository->create($data);
        $locale = core()->getCurrentLocale();

        // Save translations
        $translationData = [
            'name'        => request()->input('name'),
        ];

        $attributeGroup->translateOrNew($locale->code)->fill($translationData)->save();
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
    public function update(int $id): \Illuminate\Http\RedirectResponse
    {
        $rules = [
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
            'code'       => request()->input('code'),
            'group_type' => request()->input('group_type', 'general'),
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

                    $attributeType = app(\Najaz\Service\Repositories\ServiceAttributeTypeRepository::class)
                        ->findOrFail($field->service_attribute_type_id);

                    $this->fieldRepository->update([
                        'sort_order'       => $fieldData['sort_order'] ?? $field->sort_order,
                        'is_required'      => $this->toBoolean($fieldData['is_required'] ?? $field->is_required),
                        'validation_rules' => $this->prepareValidationRules(
                            $fieldData['validation_rules'] ?? $field->validation_rules,
                            $attributeType->regex
                        ),
                        'default_value' => $fieldData['default_value'] ?? $field->default_value,
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

    /**
     * Normalize truthy values coming from the request payload.
     */
    protected function toBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
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

            return ['validation' => 'regex:'.$pattern];
        }

        return ['validation' => $formatted];
    }
}
