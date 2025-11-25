<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\ServiceDataGrid;
use Najaz\Citizen\Models\CitizenTypeProxy;
use Najaz\Service\Models\Service;
use Najaz\Service\Models\ServiceAttributeGroupProxy;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Najaz\Service\Models\ServiceDocumentTemplateProxy;
use Najaz\Service\Repositories\ServiceRepository;
use Najaz\Admin\Http\Controllers\Controller;

class ServiceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ServiceDataGrid::class)->process();
        }

        return view('admin::services.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $currentLocale = app()->getLocale();

        $attributeGroups = ServiceAttributeGroupProxy::modelClass()::with([
            'translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.attributeType.options.translations',
        ])->orderBy('sort_order')->get();

        return view('admin::services.create', [
            'attributeGroupOptions'        => $this->formatAttributeGroupsForFrontend($attributeGroups, $currentLocale),
            'serviceGroupInitialSelection' => $this->buildServiceGroupInitialSelection(
                $this->prepareServiceGroupsForFrontend(null, $currentLocale)
            ),
            'citizenTypeTree'              => $this->buildCitizenTypeTree(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
            'image'       => 'nullable|string|max:2048',
            'sort_order'  => 'nullable|integer|min:0',
            'citizen_type_ids'   => 'nullable|array',
            'citizen_type_ids.*' => 'integer|exists:citizen_types,id',
        ]);

        $data = request()->only([
            'name',
            'description',
            'status',
            'image',
            'sort_order',
        ]);

        $service = $this->serviceRepository->create($data);

        // Groups are now managed separately via ServiceGroupController
        // $this->serviceRepository->syncAttributeGroups(
        //     request()->input('service_attribute_groups'),
        //     $service
        // );

        $this->serviceRepository->syncCitizenTypes(request()->input('citizen_type_ids', []), $service);


        return new JsonResponse([
            'message' => trans('Admin::app.services.services.create-success'),
            'redirect_to' => route('admin.services.edit', $service->id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $currentLocale = app()->getLocale();

        $service = $this->serviceRepository->with([
            'attributeGroups.translations',
            'citizenTypes',
        ])->findOrFail($id);

        // Eager load custom service fields for each pivot relation (use service-specific tables only)
        $pivotIds = $service->attributeGroups->pluck('pivot.id')->filter();
        if ($pivotIds->isNotEmpty()) {
            \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'fields.translations',
                'fields.attributeType.translations',
                'fields.options.translations', // Load custom field options only
            ])->whereIn('id', $pivotIds)->get();
        }

        $attributeGroups = ServiceAttributeGroupProxy::modelClass()::with([
            'translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.attributeType.options.translations',
        ])->orderBy('sort_order')->get();

        $documentTemplate = $service->documentTemplate;

        // Get attribute types for field management
        $attributeTypes = app(\Najaz\Service\Repositories\ServiceAttributeTypeRepository::class)
            ->with(['translations', 'options.translations'])
            ->orderBy('position')
            ->get()
            ->map(function ($type) use ($currentLocale) {
                $translation = $type->translate($currentLocale);
                
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
                    'validation'   => $type->validation,
                    'regex'        => $type->regex,
                    'default_value' => $type->default_value,
                    'is_required'  => $type->is_required,
                    'is_unique'    => $type->is_unique,
                    'options'      => $options,
                    'translations' => $type->translations->map(fn($t) => [
                        'locale' => $t->locale,
                        'name'   => $t->name,
                    ])->toArray(),
                ];
            });

        // Get validations enum values
        $validations = \Webkul\Attribute\Enums\ValidationEnum::getValues();
        $validationLabels = collect($validations)->mapWithKeys(fn ($value) => [
            $value => trans("Admin::app.services.attribute-types.index.datagrid.validation-{$value}"),
        ])->toArray();

        // Build available fields list from service attribute groups
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);

        return view('admin::services.edit', [
            'service'          => $service,
            'documentTemplate' => $documentTemplate,
            'availableFields'  => $availableFields,
            'attributeGroupOptions'        => $this->formatAttributeGroupsForFrontend($attributeGroups, $currentLocale),
            'serviceGroupInitialSelection' => $this->buildServiceGroupInitialSelection(
                $this->prepareServiceGroupsForFrontend($service, $currentLocale)
            ),
            'citizenTypeTree'  => $this->buildCitizenTypeTree(),
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
        $this->validate(request(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
            'image'       => 'nullable|string|max:2048',
            'sort_order'  => 'nullable|integer|min:0',
            'citizen_type_ids'   => 'nullable|array',
            'citizen_type_ids.*' => 'integer|exists:citizen_types,id',
        ]);

        $data = request()->only([
            'name',
            'description',
            'status',
            'image',
            'sort_order',
        ]);

        $service = $this->serviceRepository->update($data, $id);

        // Groups are now managed separately via ServiceGroupController
        // $this->serviceRepository->syncAttributeGroups(
        //     request()->input('service_attribute_groups'),
        //     $service
        // );

        $this->serviceRepository->syncCitizenTypes(request()->input('citizen_type_ids', []), $service);


        return new JsonResponse([
            'message' => trans('Admin::app.services.services.update-success'),
            'data'    => $service->fresh(['attributeGroups', 'citizenTypes']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->serviceRepository->findOrFail($id);

        $this->serviceRepository->delete($id);

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.delete-success'),
        ]);
    }



    protected function buildCitizenTypeTree(): array
    {
        return CitizenTypeProxy::modelClass()::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($type) => [
                'id'       => $type->id,
                'key'      => (string) $type->id,
                'name'     => $type->name,
                'children' => [],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Mass delete services.
     */
    public function massDestroy(): JsonResponse
    {
        $indices = request()->input('indices', []);

        foreach ($indices as $id) {
            $this->serviceRepository->delete($id);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.index.datagrid.mass-delete-success'),
        ]);
    }

    /**
     * Mass update services.
     */
    public function massUpdate(): JsonResponse
    {
        $indices = request()->input('indices', []);
        $value = request()->input('value');

        foreach ($indices as $id) {
            $this->serviceRepository->update([
                'status' => $value,
            ], $id);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.index.datagrid.mass-update-success'),
        ]);
    }

    protected function formatAttributeGroupsForFrontend(Collection $attributeGroups, string $locale): array
    {
        // Load custom service fields from ServiceAttributeGroupService
        $pivotIds = $attributeGroups->pluck('pivot.id')->filter();
        $pivotRelations = collect();
        if ($pivotIds->isNotEmpty()) {
            $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'fields.translations',
                'fields.attributeType.translations',
                'fields.options.translations', // Load custom field options
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');
        }

        return $attributeGroups
            ->map(function ($group) use ($locale, $pivotRelations) {
                $pivotId = $group->pivot->id ?? null;
                
                // Get custom service fields from pivot relation if available, otherwise use template fields
                $pivotRelation = $pivotId ? ($pivotRelations[$pivotId] ?? null) : null;
                $fieldsToUse = $pivotRelation && $pivotRelation->fields->isNotEmpty() 
                    ? $pivotRelation->fields 
                    : ($group->fields ?? collect());

                // Filter groups that have fields
                if ($fieldsToUse->isEmpty()) {
                    return null;
                }

                $translation = $group->translate($locale);
                $supportsNotification = $group->group_type === 'citizen'
                    && $fieldsToUse->contains(
                        fn ($field) => strtolower($field->code ?? '') === 'id_number'
                    );

                return [
                    'id'                    => $group->id,
                    'code'                  => $group->code,
                    'group_type'            => $group->group_type ?? 'general',
                    'name'                  => $translation?->name ?? $group->code,
                    'description'           => $translation?->description,
                    'sort_order'            => $group->sort_order ?? 0,
                    'is_notifiable'         => false,
                    'supports_notification' => $supportsNotification,
                    'fields'                => $fieldsToUse->map(function ($field) use ($locale) {
                        $fieldTranslation = $field->translate($locale);
                        $attributeType = $field->attributeType;
                        $attributeTypeTranslation = $attributeType?->translate($locale);

                        // Get options from custom field options first, then fall back to attribute type options
                        $options = [];
                        
                        // First, try custom field options (service_attribute_group_service_field_options)
                        if ($field->options && $field->options->isNotEmpty()) {
                            $allLocales = core()->getAllLocales();
                            foreach ($field->options as $option) {
                                $optionLabels = [];
                                foreach ($allLocales as $loc) {
                                    $optionTranslation = $option->translate($loc->code);
                                    $optionLabels[$loc->code] = $optionTranslation?->label ?? $option->admin_name ?? $option->code ?? '';
                                }
                                
                                $options[] = [
                                    'id' => $option->id,
                                    'code' => $option->code ?? $option->admin_name ?? '',
                                    'labels' => $optionLabels,
                                ];
                            }
                        } elseif ($attributeType && $attributeType->options && $attributeType->options->isNotEmpty()) {
                            // Fall back to attribute type options if no custom options
                            $allLocales = core()->getAllLocales();
                            foreach ($attributeType->options as $option) {
                                $optionLabels = [];
                                foreach ($allLocales as $loc) {
                                    $optionTranslation = $option->translate($loc->code);
                                    $optionLabels[$loc->code] = $optionTranslation?->label ?? $option->admin_name ?? $option->code ?? '';
                                }
                                
                                $options[] = [
                                    'id' => $option->id,
                                    'code' => $option->code,
                                    'labels' => $optionLabels,
                                ];
                            }
                        }

                        return [
                            'id'                  => $field->id,
                            'code'                => $field->code,
                            'label'               => $fieldTranslation?->label ?? $field->code,
                            'type'                => $field->type,
                            'attribute_type_name' => $attributeTypeTranslation?->name ?? $attributeType?->code ?? '',
                            'sort_order'          => $field->sort_order ?? 0,
                            'options'             => $options,
                        ];
                    })->values(),
                ];
            })
            ->filter() // Remove null entries
            ->values()
            ->toArray();
    }

    protected function prepareServiceGroupsForFrontend(?Service $service, string $locale): Collection
    {
        $serviceGroups = ($service?->attributeGroups ?? collect())
            ->filter(function ($group) {
                // Check if group has saved fields or template fields
                $pivotId = $group->pivot->id ?? null;
                if ($pivotId) {
                    $pivotRelation = \Najaz\Service\Models\ServiceAttributeGroupService::find($pivotId);
                    if ($pivotRelation && $pivotRelation->fields()->count() > 0) {
                        return true;
                    }
                }
                // Fallback to template fields
                return ($group->fields ?? collect())->count() > 0;
            })
            ->map(function ($group) use ($locale) {
                $translation = $group->translate($locale);
                
                // Get pivot relation
                $pivotId = $group->pivot->id ?? null;
                $pivotRelation = null;
                $savedFields = collect();
                
                if ($pivotId) {
                    $pivotRelation = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                        'fields.translations',
                        'fields.attributeType.translations',
                        'fields.options.translations', // Load custom field options only
                    ])->find($pivotId);
                    
                    if ($pivotRelation) {
                        $savedFields = $pivotRelation->fields;
                    }
                }
                
                // Use saved fields if available, otherwise use template fields
                $fieldsToUse = $savedFields->count() > 0 ? $savedFields : ($group->fields ?? collect());
                
                $supportsNotification = $group->group_type === 'citizen'
                    && $fieldsToUse->contains(
                        fn ($field) => strtolower($field->code ?? '') === 'id_number'
                    );

                return [
                    'service_attribute_group_id' => $pivotId, // Use pivot ID only (null if not exists)
                    'template_id'                => $group->id,
                    'code'                       => $group->pivot->custom_code ?? $group->code,
                    'group_type'                 => $group->group_type ?? 'general',
                    'name'                       => $group->pivot->custom_name ?? ($translation?->name ?? $group->code),
                    'description'                => $translation?->description,
                    'sort_order'                 => $group->pivot->sort_order ?? 0,
                    'is_notifiable'              => (bool) ($group->pivot->is_notifiable ?? false),
                    'supports_notification'      => $supportsNotification,
                    'pivot_uid'                  => $group->pivot->pivot_uid ?? '',
                    'pivot_id'                   => $pivotId,
                    'fields'                     => $fieldsToUse->map(function ($field) use ($locale, $group) {
                        $fieldTranslation = $field->translate($locale);
                        $attributeType = $field->attributeType;
                        $attributeTypeTranslation = $attributeType?->translate($locale);
                        
                        // Determine template field ID (original field ID)
                        $templateFieldId = $field->service_attribute_field_id ?? $field->id;
                        
                        // If this is a saved field, use its ID; otherwise use template field ID
                        $fieldId = ($field instanceof \Najaz\Service\Models\ServiceAttributeGroupServiceField) 
                            ? $field->id 
                            : null;

                        // Get labels for all locales
                        $labels = [];
                        if ($field instanceof \Najaz\Service\Models\ServiceAttributeGroupServiceField) {
                            $allLocales = core()->getAllLocales();
                            foreach ($allLocales as $locale) {
                                $translation = $field->translate($locale->code);
                                $labels[$locale->code] = $translation?->label ?? '';
                            }
                        }

                        // Get options from service field options (not from attribute type)
                        $options = [];
                        if ($field instanceof \Najaz\Service\Models\ServiceAttributeGroupServiceField && $field->options) {
                            $allLocales = core()->getAllLocales();
                            foreach ($field->options as $option) {
                                $optionLabels = [];
                                foreach ($allLocales as $loc) {
                                    $optionTranslation = $option->translate($loc->code);
                                    $optionLabels[$loc->code] = $optionTranslation?->label ?? $option->admin_name ?? '';
                                }
                                
                                $options[] = [
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
                            'id'                        => $fieldId,
                            'service_attribute_field_id' => $fieldId ?? $field->id,
                            'template_field_id'         => $templateFieldId,
                            'code'                      => $field->code,
                            'label'                     => $fieldTranslation?->label ?? $field->code,
                            'type'                      => $field->type,
                            'attribute_type_name'       => $attributeTypeTranslation?->name ?? $attributeType?->code ?? '',
                            'sort_order'                => $field->sort_order ?? 0,
                            'is_required'               => $field->is_required ?? false,
                            'default_value'             => $field->default_value ?? null,
                            'validation_rules'          => $field->validation_rules ?? null,
                            'service_attribute_type_id' => $field->service_attribute_type_id ?? null,
                            'labels'                    => $labels,
                            'options'                   => $options,
                        ];
                    })->values()->toArray(),
                ];
            })->values();

        $oldGroupsInput = old('service_attribute_groups');

        if (is_array($oldGroupsInput)) {
            $serviceGroups = collect($oldGroupsInput)->map(function ($group, $index) {
                $groupId = isset($group['service_attribute_group_id']) ? (int) $group['service_attribute_group_id'] : 0;

                if (! $groupId) {
                    return null;
                }

                $fields = collect($group['fields'] ?? [])->filter();

                if ($fields->isEmpty()) {
                    return null;
                }

                return [
                    'service_attribute_group_id' => $groupId,
                    'template_id'                => isset($group['template_id']) ? (int) $group['template_id'] : $groupId,
                    'code'                       => $group['code'] ?? null,
                    'group_type'                 => $group['group_type'] ?? 'general',
                    'name'                       => $group['name'] ?? null,
                    'description'                => $group['description'] ?? null,
                    'sort_order'                 => isset($group['sort_order']) ? (int) $group['sort_order'] : $index,
                    'is_notifiable'              => ! empty($group['is_notifiable']),
                    'fields'                     => $fields->values(),
                    'supports_notification'      => ! empty($group['supports_notification']),
                    'pivot_uid'                  => $group['pivot_uid'] ?? '',
                ];
            })->filter()->values();
        }

        return $serviceGroups;
    }

    protected function buildServiceGroupInitialSelection(Collection $serviceGroups): array
    {
        return [
            'groups' => $serviceGroups->values()->toArray(),
            'fields' => [],
        ];
    }

    /**
     * Store or update document template for a service.
     */
    public function storeDocumentTemplate(int $id): JsonResponse
    {
        $this->validate(request(), [
            'template_content' => 'required|string',
            'used_fields'     => 'nullable|array',
            'header_image'    => 'nullable|string|max:2048',
            'footer_text'     => 'nullable|string|max:500',
            'is_active'       => 'nullable|boolean',
        ]);

        $service = $this->serviceRepository->findOrFail($id);

        // Get used_fields and ensure it's an array
        $usedFields = request()->input('used_fields', []);
        
        // Debug: Log the received data
        \Log::info('Received used_fields:', ['used_fields' => $usedFields, 'type' => gettype($usedFields)]);
        
        // Handle JSON string if sent as string
        if (is_string($usedFields)) {
            $usedFields = json_decode($usedFields, true) ?? [];
        }
        
        if (! is_array($usedFields)) {
            $usedFields = [];
        }

        \Log::info('Processed used_fields:', ['used_fields' => $usedFields]);

        $template = ServiceDocumentTemplateProxy::modelClass()::updateOrCreate(
            ['service_id' => $service->id],
            [
                'template_content' => request()->input('template_content'),
                'used_fields'      => $usedFields,
                'header_image'     => request()->input('header_image'),
                'footer_text'      => request()->input('footer_text'),
                'is_active'        => request()->input('is_active', true),
            ]
        );

        \Log::info('Template saved:', ['template_id' => $template->id, 'used_fields' => $template->used_fields]);

        // Build available fields list
        $currentLocale = app()->getLocale();
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);
        $template->available_fields = $availableFields;
        $template->save();

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.document-template.save-success'),
            'data'    => $template->fresh(),
        ]);
    }

    /**
     * Build available fields list for document template.
     * Uses custom service fields (service_attribute_group_service_fields) instead of template fields.
     */
    protected function buildAvailableFieldsForTemplate(Service $service, string $locale): array
    {
        $service->load(['attributeGroups.translations']);

        $fields = [];

        // Add citizen basic fields
        $fields[] = [
            'code'  => 'citizen_first_name',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_first_name'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'citizen_middle_name',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_middle_name'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'citizen_last_name',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_last_name'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'citizen_national_id',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_national_id'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'citizen_type_name',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_type_name'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'request_increment_id',
            'label' => trans('Admin::app.services.services.document-template.fields.request_increment_id'),
            'group' => 'request',
        ];
        $fields[] = [
            'code'  => 'request_date',
            'label' => trans('Admin::app.services.services.document-template.fields.request_date'),
            'group' => 'request',
        ];
        $fields[] = [
            'code'  => 'current_date',
            'label' => trans('Admin::app.services.services.document-template.fields.current_date'),
            'group' => 'system',
        ];

        // Load custom service fields from ServiceAttributeGroupService
        $pivotIds = $service->attributeGroups->pluck('pivot.id')->filter();
        $pivotRelations = collect();
        if ($pivotIds->isNotEmpty()) {
            $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'attributeGroup.translations',
                'fields.translations', // Load custom service fields with translations
                'fields.attributeType.translations',
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');
        }

        // Add fields from service attribute groups - use custom service fields
        foreach ($service->attributeGroups as $group) {
            $pivotId = $group->pivot->id ?? null;
            $groupCode = $group->pivot->custom_code ?? $group->code;
            $groupTranslation = $group->translate($locale);
            $groupName = $group->pivot->custom_name ?? ($groupTranslation?->name ?? $group->code);

            // Get custom service fields from pivot relation if available, otherwise use template fields
            $pivotRelation = $pivotId ? ($pivotRelations[$pivotId] ?? null) : null;
            $fieldsToUse = $pivotRelation && $pivotRelation->fields->isNotEmpty() 
                ? $pivotRelation->fields 
                : ($group->fields ?? collect());

            foreach ($fieldsToUse as $field) {
                $fieldTranslation = $field->translate($locale);
                $fieldLabel = $fieldTranslation?->label ?? $field->code;

                $fields[] = [
                    'code'  => $groupCode . '.' . $field->code,
                    'label' => $groupName . ' - ' . $fieldLabel,
                    'group' => $groupCode,
                ];

                // Also add flat field code
                $fields[] = [
                    'code'  => $field->code,
                    'label' => $fieldLabel,
                    'group' => $groupCode,
                ];
            }
        }

        return $fields;
    }

}
