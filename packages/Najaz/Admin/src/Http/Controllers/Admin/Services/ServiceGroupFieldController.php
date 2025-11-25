<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Najaz\Service\Repositories\ServiceAttributeGroupServiceFieldRepository;
use Najaz\Service\Repositories\ServiceAttributeTypeRepository;
use Najaz\Service\Repositories\ServiceRepository;
use Najaz\Admin\Http\Controllers\Controller;
use Webkul\Attribute\Enums\ValidationEnum;

class ServiceGroupFieldController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
        protected ServiceAttributeGroupServiceFieldRepository $groupServiceFieldRepository,
        protected ServiceAttributeTypeRepository $attributeTypeRepository,
    ) {}

    /**
     * Show the form for editing fields of a service group (returns partial view for modal).
     */
    public function edit(int $serviceId, int $pivotId): Response
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::with([
            'attributeGroup.translations',
            'fields.translations',
            'fields.attributeType.translations',
            'service',
        ])->findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $attributeTypes = $this->attributeTypeRepository
            ->with(['translations', 'options'])
            ->orderBy('position')
            ->get()
            ->map(function ($type) {
                $translation = $type->translate(app()->getLocale());
                return [
                    'id'           => $type->id,
                    'code'         => $type->code,
                    'type'         => $type->type,
                    'name'         => $translation?->name ?? $type->code,
                    'validation'   => $type->validation,
                    'regex'        => $type->regex,
                    'default_value' => $type->default_value,
                    'is_required'  => $type->is_required,
                    'is_unique'    => $type->is_unique,
                ];
            });

        // Get validations enum values
        $validations = ValidationEnum::getValues();
        $validationLabels = collect($validations)->mapWithKeys(fn ($value) => [
            $value => trans("Admin::app.services.attribute-types.index.datagrid.validation-{$value}"),
        ])->toArray();

        return response()->view('admin::services.groups.fields._modal', [
            'service'          => $service,
            'pivotRelation'    => $pivotRelation,
            'attributeTypes'   => $attributeTypes,
            'validations'      => $validations,
            'validationLabels' => $validationLabels,
        ]);
    }

    /**
     * Get pivot relation data for modal (returns JSON).
     */
    public function getData(int $serviceId, int $pivotId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::with([
            'attributeGroup.translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.options.translations', // Load custom field options
            'service',
        ])->findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $attributeTypes = $this->attributeTypeRepository
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

        // Get validations enum values
        $validations = ValidationEnum::getValues();
        $validationLabels = collect($validations)->mapWithKeys(fn ($value) => [
            $value => trans("Admin::app.services.attribute-types.index.datagrid.validation-{$value}"),
        ])->toArray();

        // Convert pivotRelation to array with fields
        $pivotRelationArray = [
            'id' => $pivotRelation->id,
            'service_id' => $pivotRelation->service_id,
            'service_attribute_group_id' => $pivotRelation->service_attribute_group_id,
            'pivot_uid' => $pivotRelation->pivot_uid,
            'sort_order' => $pivotRelation->sort_order,
            'is_notifiable' => $pivotRelation->is_notifiable,
            'custom_code' => $pivotRelation->custom_code,
            'custom_name' => $pivotRelation->custom_name,
            'attributeGroup' => [
                'id' => $pivotRelation->attributeGroup->id,
                'code' => $pivotRelation->attributeGroup->code,
                'translations' => $pivotRelation->attributeGroup->translations->map(fn($t) => [
                    'locale' => $t->locale,
                    'name' => $t->name,
                    'description' => $t->description,
                ])->toArray(),
            ],
            'fields' => $pivotRelation->fields->map(function ($field) {
                // Get custom options for this field (only from service field options)
                $customOptions = [];
                if ($field->options) {
                    $allLocales = core()->getAllLocales();
                    foreach ($field->options as $option) {
                        $optionLabels = [];
                        foreach ($allLocales as $loc) {
                            $optionTranslation = $option->translate($loc->code);
                            $optionLabels[$loc->code] = $optionTranslation?->label ?? $option->admin_name ?? '';
                        }
                        $customOptions[] = [
                            'id' => $option->id,
                            'uid' => "option_{$option->id}",
                            'service_attribute_type_option_id' => $option->service_attribute_type_option_id ?? null,
                            'code' => $option->code ?? $option->admin_name ?? '',
                            'admin_name' => $option->admin_name ?? '',
                            'labels' => $optionLabels,
                            'sort_order' => $option->sort_order ?? 0,
                            'is_custom' => $option->is_custom ?? true,
                        ];
                    }
                }

                return [
                    'id' => $field->id,
                    'service_attribute_group_service_id' => $field->service_attribute_group_service_id,
                    'service_attribute_field_id' => $field->service_attribute_field_id,
                    'service_attribute_type_id' => $field->service_attribute_type_id,
                    'code' => $field->code,
                    'type' => $field->type,
                    'validation_rules' => $field->validation_rules,
                    'default_value' => $field->default_value,
                    'is_required' => $field->is_required,
                    'sort_order' => $field->sort_order,
                    'translations' => $field->translations->map(fn($t) => [
                        'locale' => $t->locale,
                        'label' => $t->label,
                    ])->toArray(),
                    'options' => $customOptions, // Include custom options
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
            })->toArray(),
        ];

        return new JsonResponse([
            'pivotRelation'    => $pivotRelationArray,
            'attributeTypes'   => $attributeTypes,
            'validations'      => $validations,
            'validationLabels' => $validationLabels,
        ]);
    }

    /**
     * Store a newly created field in storage.
     */
    public function store(int $serviceId, int $pivotId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $this->validate(request(), [
            'service_attribute_type_id' => 'required|exists:service_attribute_types,id',
            'label'                     => 'required|array',
            'label.*'                   => 'required|string|max:255',
            'sort_order'                => 'nullable|integer',
            'is_required'               => 'nullable|boolean',
            'validation_rules'          => 'nullable|string',
            'default_value'             => 'nullable|string',
        ]);

        $attributeType = $this->attributeTypeRepository->findOrFail(request()->input('service_attribute_type_id'));

        // Get the group code (custom_code or fallback to attribute group code)
        // Load attributeGroup if not already loaded
        if (! $pivotRelation->relationLoaded('attributeGroup')) {
            $pivotRelation->load('attributeGroup');
        }
        
        $groupCode = $pivotRelation->custom_code ?? ($pivotRelation->attributeGroup ? $pivotRelation->attributeGroup->code : null) ?? '';
        
        // Get original field code from attribute type
        $originalFieldCode = $attributeType->code;
        
        // Merge group code with field code using underscore
        $baseFieldCode = $groupCode ? $groupCode . '_' . $originalFieldCode : $originalFieldCode;
        
        // Generate unique field code by checking for duplicates and adding sequential number if needed
        $fieldCode = $this->generateUniqueFieldCode($pivotRelation, $baseFieldCode, $attributeType->id);

        $fieldData = [
            'service_attribute_group_service_id' => $pivotId,
            'service_attribute_type_id'          => $attributeType->id,
            'code'                               => $fieldCode,
            'type'                               => request()->input('type', $attributeType->type),
            'validation_rules'                   => $this->prepareValidationRules(
                request()->input('validation_rules', $attributeType->validation),
                $attributeType->regex
            ),
            'default_value'                      => request()->input('default_value', $attributeType->default_value),
            'sort_order'                         => request()->input('sort_order', 0),
            'is_required'                        => request()->boolean('is_required', $attributeType->is_required),
        ];

        $field = $this->groupServiceFieldRepository->create($fieldData);

        // Save translations
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'label' => request()->input("label.{$locale->code}"),
            ];

            $field->translateOrNew($locale->code)->fill($translationData)->save();
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.create-success'),
            'data'    => $field->load(['attributeType', 'translations']),
        ]);
    }

    /**
     * Display the specified field.
     */
    public function show(int $serviceId, int $pivotId, int $fieldId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $field = $this->groupServiceFieldRepository->with([
            'translations',
            'attributeType.translations',
            'attributeType.options.translations',
            'options.translations',
            'options.originalOption.translations',
        ])->findOrFail($fieldId);

        // Verify that this field belongs to the pivot
        if ($field->service_attribute_group_service_id != $pivotId) {
            abort(404);
        }

        return new JsonResponse([
            'data' => [
                'id' => $field->id,
                'service_attribute_group_service_id' => $field->service_attribute_group_service_id,
                'service_attribute_type_id' => $field->service_attribute_type_id,
                'code' => $field->code,
                'type' => $field->type,
                'validation_rules' => $field->validation_rules,
                'default_value' => $field->default_value,
                'is_required' => $field->is_required,
                'sort_order' => $field->sort_order,
                'translations' => $field->translations->map(fn($t) => [
                    'locale' => $t->locale,
                    'label' => $t->label,
                ])->toArray(),
                'labels' => $field->translations->mapWithKeys(fn($t) => [
                    $t->locale => $t->label,
                ])->toArray(),
                'options' => $field->options->map(function($option) {
                    $allLocales = core()->getAllLocales();
                    $optionLabels = [];
                    foreach ($allLocales as $locale) {
                        $translation = $option->translate($locale->code);
                        $optionLabels[$locale->code] = $translation?->label ?? $option->admin_name ?? '';
                    }
                    
                    return [
                        'id' => $option->id,
                        'uid' => "option_{$option->id}",
                        'service_attribute_type_option_id' => $option->service_attribute_type_option_id,
                        'admin_name' => $option->admin_name,
                        'code' => $option->admin_name,
                        'labels' => $optionLabels,
                        'sort_order' => $option->sort_order ?? 0,
                        'is_custom' => $option->is_custom ?? false,
                    ];
                })->toArray(),
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
            ],
        ]);
    }

    /**
     * Update the specified field in storage.
     */
    public function update(int $serviceId, int $pivotId, int $fieldId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $field = $this->groupServiceFieldRepository->findOrFail($fieldId);

        // Verify that this field belongs to the pivot
        if ($field->service_attribute_group_service_id != $pivotId) {
            abort(404);
        }

        $this->validate(request(), [
            'label'           => 'required|array',
            'label.*'         => 'required|string|max:255',
            'sort_order'      => 'nullable|integer',
            'is_required'     => 'nullable|boolean',
            'validation_rules' => 'nullable|string',
            'default_value'   => 'nullable|string',
        ]);

        $attributeType = $field->attributeType;

        // Keep existing code (code cannot be changed)
        $data = [
            'code'             => $field->code,
            'sort_order'       => request()->input('sort_order', $field->sort_order),
            'is_required'      => request()->boolean('is_required', $field->is_required),
            'validation_rules' => $this->prepareValidationRules(
                request()->input('validation_rules', $field->validation_rules),
                $attributeType->regex ?? null
            ),
            'default_value'    => request()->input('default_value', $field->default_value),
        ];

        $this->groupServiceFieldRepository->update($data, $fieldId);

        // Update translations
        $field = $field->fresh();
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'label' => request()->input("label.{$locale->code}"),
            ];

            $field->translateOrNew($locale->code)->fill($translationData)->save();
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.update-success'),
            'data'    => $field->fresh(['attributeType', 'translations']),
        ]);
    }

    /**
     * Remove the specified field from storage.
     */
    public function destroy(int $serviceId, int $pivotId, int $fieldId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $field = $this->groupServiceFieldRepository->findOrFail($fieldId);

        // Verify that this field belongs to the pivot
        if ($field->service_attribute_group_service_id != $pivotId) {
            abort(404);
        }

        // Check if this field is used in any requests
        $groupCode = $pivotRelation->custom_code ?? $pivotRelation->attributeGroup->code;
        $fieldCode = $field->code;
        
        // Get field label for error message
        $fieldLabel = $field->translate(app()->getLocale())->label ?? $field->code;
        
        // Check if there are any requests with this group code
        $hasRequests = DB::table('service_request_form_data')
            ->join('service_requests', 'service_request_form_data.service_request_id', '=', 'service_requests.id')
            ->where('service_requests.service_id', $serviceId)
            ->where('service_request_form_data.group_code', $groupCode)
            ->whereNotNull('service_request_form_data.fields_data')
            ->get()
            ->filter(function ($record) use ($fieldCode) {
                // Check if fields_data JSON contains the field code as a key
                $fieldsData = json_decode($record->fields_data, true);
                return is_array($fieldsData) && array_key_exists($fieldCode, $fieldsData);
            })
            ->isNotEmpty();
        
        if ($hasRequests) {
            return new JsonResponse([
                'message' => trans(
                    'Admin::app.services.services.attribute-groups.delete-field-has-requests',
                    ['field_name' => $fieldLabel]
                ),
            ], 422);
        }

        $this->groupServiceFieldRepository->delete($fieldId);

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-groups.attribute-group-fields.delete-success'),
        ]);
    }

    /**
     * Prepare validation rules.
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
        $existingField = $this->groupServiceFieldRepository->findWhere([
            'service_attribute_group_service_id' => $pivotRelation->id,
            'code'                               => $baseCode,
        ])->first();

        // If base code doesn't exist, return it
        if (! $existingField) {
            return $baseCode;
        }

        // Add sequential number if code exists
        $counter = 2;
        $uniqueCode = $baseCode . '_' . $counter;

        // Keep incrementing until we find a unique code
        while ($this->groupServiceFieldRepository->findWhere([
            'service_attribute_group_service_id' => $pivotRelation->id,
            'code'                               => $uniqueCode,
        ])->first()) {
            $counter++;
            $uniqueCode = $baseCode . '_' . $counter;
        }

        return $uniqueCode;
    }

    /**
     * Reorder fields within a service attribute group.
     */
    public function reorder(int $serviceId, int $pivotId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $fieldIds = request()->input('field_ids', []);

        if (!is_array($fieldIds) || empty($fieldIds)) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.reorder-invalid'),
            ], 400);
        }

        DB::beginTransaction();

        try {
            foreach ($fieldIds as $index => $fieldId) {
                $this->groupServiceFieldRepository->update([
                    'sort_order' => $index,
                ], $fieldId);
            }

            DB::commit();

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.reorder-success'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return new JsonResponse([
                'message' => trans('Admin::app.services.services.groups.fields.reorder-error'),
            ], 500);
        }
    }
}
