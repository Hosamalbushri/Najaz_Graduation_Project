<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\AttributeGroupDataGrid;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Service\Repositories\ServiceAttributeFieldRepository;
use Najaz\Service\Repositories\ServiceAttributeGroupRepository;
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

        // If group type is citizen, automatically add national_id_card field
        if ($data['group_type'] === 'citizen') {
            $attributeTypeRepository = app(\Najaz\Service\Repositories\ServiceAttributeTypeRepository::class);
            $nationalIdCardType = $attributeTypeRepository->findWhere(['code' => 'national_id_card'])->first();

            if ($nationalIdCardType) {
                $fieldRepository = app(\Najaz\Service\Repositories\ServiceAttributeFieldRepository::class);
                
                // Prepare validation rules (same logic as AttributeGroupFieldController)
                $validationRules = null;
                if ($nationalIdCardType->validation) {
                    if (is_array($nationalIdCardType->validation)) {
                        $validationRules = $nationalIdCardType->validation ?: null;
                    } else {
                        $formatted = is_string($nationalIdCardType->validation) ? trim($nationalIdCardType->validation) : null;
                        if ($formatted) {
                            if ($formatted === 'regex') {
                                $pattern = is_string($nationalIdCardType->regex) ? trim($nationalIdCardType->regex) : '';
                                if ($pattern) {
                                    $validationRules = ['validation' => 'regex:' . $pattern];
                                }
                            } else {
                                $validationRules = ['validation' => $formatted];
                            }
                        }
                    }
                }
                
                $fieldData = [
                    'service_attribute_group_id' => $attributeGroup->id,
                    'service_attribute_type_id'  => $nationalIdCardType->id,
                    'code'                      => $nationalIdCardType->code,
                    'type'                      => $nationalIdCardType->type,
                    'validation_rules'          => $validationRules,
                    'default_value'             => $nationalIdCardType->default_value,
                    'sort_order'                => 0,
                    'is_required'               => $nationalIdCardType->is_required ?? true,
                ];

                $field = $fieldRepository->create($fieldData);

                // Save translations for all locales
                foreach (core()->getAllLocales() as $locale) {
                    $typeTranslation = $nationalIdCardType->translate($locale->code);
                    $field->translateOrNew($locale->code)->fill([
                        'label' => $typeTranslation?->name ?? $nationalIdCardType->code,
                    ])->save();
                }
            }
        }

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

        $validations = ValidationEnum::getValues();
        $validationLabels = collect($validations)->mapWithKeys(fn ($value) => [
            $value => trans('Admin::app.services.attribute-types.validation-options.'.$value),
        ]);

        // Prepare fields data
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

        return view('admin::services.attribute-groups.edit', [
            'attributeGroup'   => $attributeGroup,
            'attributeTypes'   => $attributeTypes,
            'validations'      => $validations,
            'validationLabels' => $validationLabels,
            'initialFields'    => $fields,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): RedirectResponse
    {
        $locale = core()->getRequestedLocaleCode();

        $rules = [
            'default_name'                       => 'required|string|max:255',
            'code'                               => 'required|string|max:255|unique:service_attribute_groups,code,'.$id,
            'group_type'                         => 'required|in:general,citizen',
            'sort_order'                         => 'nullable|integer',
            "{$locale}.name"                     => 'required|string|max:255',
            "{$locale}.description"              => 'nullable|string',
        ];

        $this->validate(request(), $rules);

        $data = [
            'code'         => request()->input('code'),
            'default_name' => request()->input('default_name'),
            'group_type'   => request()->input('group_type', 'general'),
            'sort_order'   => request()->input('sort_order', 0),
        ];

        $data['locale'] = $locale;
        $data[$locale] = request()->input($locale, []);

        $attributeGroup = $this->dataGroupRepository->update($data, $id);

        session()->flash('success', trans('Admin::app.services.attribute-groups.update-success'));

        return redirect()->route('admin.attribute-groups.edit', $attributeGroup->id);

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
