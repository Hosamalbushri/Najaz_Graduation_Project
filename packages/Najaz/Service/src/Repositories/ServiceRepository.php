<?php

namespace Najaz\Service\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Najaz\Service\Models\ServiceAttributeGroupProxy;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Webkul\Core\Eloquent\Repository;

class ServiceRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\Service';
    }

    public function __construct(
        protected ServiceAttributeGroupServiceFieldRepository $groupServiceFieldRepository,
        \Illuminate\Container\Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Sync the assigned attribute groups for the given service.
     */
    public function syncAttributeGroups(?array $payload, $service): void
    {
        if (! is_array($payload) || empty($payload)) {
            // Check if there are any requests using current groups before detaching
            $currentGroups = $service->attributeGroups()->get();
            foreach ($currentGroups as $group) {
                $groupCode = $group->pivot->custom_code ?? $group->code;

                $hasRequests = DB::table('service_request_form_data')
                    ->join('service_requests', 'service_request_form_data.service_request_id', '=', 'service_requests.id')
                    ->where('service_requests.service_id', $service->id)
                    ->where('service_request_form_data.group_code', $groupCode)
                    ->exists();

                if ($hasRequests) {
                    throw ValidationException::withMessages([
                        'service_attribute_groups' => trans(
                            'Admin::app.services.services.attribute-groups.delete-has-requests',
                            ['group_code' => $groupCode]
                        ),
                    ]);
                }
            }

            $service->attributeGroups()->sync([]);

            return;
        }

        $normalizedCodes = [];

        foreach ($payload as $index => $groupData) {
            $groupData = Arr::wrap($groupData);
            $rawCode = $groupData['custom_code'] ?? $groupData['code'] ?? null;
            $code = is_string($rawCode) ? trim($rawCode) : '';

            if ($code === '') {
                continue;
            }

            $normalized = mb_strtolower($code);

            if (isset($normalizedCodes[$normalized])) {
                throw ValidationException::withMessages([
                    "service_attribute_groups.{$index}.code" => trans(
                        'Admin::app.services.services.attribute-groups.duplicate-code',
                        ['code' => $code]
                    ),
                ]);

            }

            $normalizedCodes[$normalized] = true;
        }

        DB::transaction(function () use ($payload, $service) {
            // Get current groups to check for relationships before detaching
            $currentGroups = $service->attributeGroups()->get();

            // Build list of new group codes (pivot_uid or new group codes)
            $newGroupCodes = [];
            foreach ($payload as $groupData) {
                $groupData = Arr::wrap($groupData);
                $pivotUid = $groupData['pivot_uid'] ?? null;
                if ($pivotUid) {
                    $newGroupCodes[] = $pivotUid;
                }
            }

            // Check if any groups being removed are used in requests
            foreach ($currentGroups as $group) {
                $pivotUid = $group->pivot->pivot_uid ?? null;

                // If this pivot is not in the new list, it will be removed
                if (! in_array($pivotUid, $newGroupCodes)) {
                    // Get the group_code (custom_code or group code)
                    $groupCode = $group->pivot->custom_code ?? $group->code;

                    // Check if there are any service requests with this group_code
                    $hasRequests = DB::table('service_request_form_data')
                        ->join('service_requests', 'service_request_form_data.service_request_id', '=', 'service_requests.id')
                        ->where('service_requests.service_id', $service->id)
                        ->where('service_request_form_data.group_code', $groupCode)
                        ->exists();

                    if ($hasRequests) {
                        throw ValidationException::withMessages([
                            'service_attribute_groups' => trans(
                                'Admin::app.services.services.attribute-groups.delete-has-requests',
                                ['group_code' => $groupCode]
                            ),
                        ]);
                    }
                }
            }

            $groupsSync = [];

            foreach ($payload as $index => $groupData) {
                $groupData = Arr::wrap($groupData);

                $templateGroupId = ! empty($groupData['template_id'])
                    ? (int) $groupData['template_id']
                    : null;

                $groupSortOrder = isset($groupData['sort_order'])
                    ? (int) $groupData['sort_order']
                    : $index;
                $isNotifiable = $this->toBoolean($groupData['is_notifiable'] ?? false);
                $customCode = $groupData['custom_code'] ?? $groupData['code'] ?? null;
                $customName = $groupData['custom_name'] ?? $groupData['name'] ?? null;

                $groupId = $templateGroupId;

                if (! $groupId) {
                    continue;
                }

                if (! ServiceAttributeGroupProxy::modelClass()::whereKey($groupId)->exists()) {
                    continue;
                }

                $pivotUid = $groupData['pivot_uid'] ?? null;

                if (! is_string($pivotUid) || ! $pivotUid) {
                    $pivotUid = (string) Str::uuid();
                }

                $groupsSync[$pivotUid] = [
                    'group_id'   => $groupId,
                    'attributes' => [
                        'sort_order'    => $groupSortOrder,
                        'is_notifiable' => $isNotifiable,
                        'custom_code'   => $customCode,
                        'custom_name'   => $customName,
                    ],
                ];
            }

            // Get existing pivot relations with their UIDs
            $existingPivotUids = ServiceAttributeGroupService::where('service_id', $service->id)
                ->pluck('pivot_uid')
                ->toArray();

            // Get UIDs for groups that should be kept
            $keepPivotUids = array_keys($groupsSync);

            // Delete pivot relations that are not in the new list
            $pivotUidsToDelete = array_diff($existingPivotUids, $keepPivotUids);
            if (! empty($pivotUidsToDelete)) {
                ServiceAttributeGroupService::where('service_id', $service->id)
                    ->whereIn('pivot_uid', $pivotUidsToDelete)
                    ->delete();
            }

            foreach ($groupsSync as $pivotUid => $syncPayload) {
                // Check if this pivot relation already exists
                $pivotRelation = ServiceAttributeGroupService::where('pivot_uid', $pivotUid)
                    ->where('service_id', $service->id)
                    ->first();

                if ($pivotRelation) {
                    // Update existing pivot relation
                    $pivotRelation->update(array_merge($syncPayload['attributes'], [
                        'service_attribute_group_id' => $syncPayload['group_id'],
                    ]));
                } else {
                    // Create new pivot relation
                    $pivotRelation = ServiceAttributeGroupService::create([
                        'service_id'                  => $service->id,
                        'service_attribute_group_id'  => $syncPayload['group_id'],
                        'pivot_uid'                   => $pivotUid,
                        'sort_order'                  => $syncPayload['attributes']['sort_order'],
                        'is_notifiable'               => $syncPayload['attributes']['is_notifiable'],
                        'custom_code'                 => $syncPayload['attributes']['custom_code'],
                        'custom_name'                 => $syncPayload['attributes']['custom_name'],
                    ]);
                }

                // Refresh the relation to ensure it's synced
                $pivotRelation = $pivotRelation->fresh();

                // Fields are managed separately via modal (ServiceGroupFieldController), not synced with service form
                // Only copy fields from template when creating a new pivot relation (first time)
                // After that, fields are managed independently through the modal
                if (! in_array($pivotUid, $existingPivotUids)) {
                    // Check if fields already exist (shouldn't happen, but safety check)
                    if ($pivotRelation->fields()->count() === 0) {
                        $templateGroup = ServiceAttributeGroupProxy::modelClass()::with('fields.translations')
                            ->find($syncPayload['group_id']);

                        if ($templateGroup && $templateGroup->fields) {
                            foreach ($templateGroup->fields as $templateField) {
                                $this->groupServiceFieldRepository->copyFieldFromTemplate(
                                    $templateField,
                                    $pivotRelation
                                );
                            }
                        }
                    }
                }
                // Note: Fields modifications are done separately through ServiceGroupFieldController modal
                // This method only handles adding/removing/updating groups, not their fields
            }
        });
    }

    /**
     * Sync the citizen types associated with the service.
     */
    public function syncCitizenTypes($citizenTypeIds, $service): void
    {
        $ids = collect($citizenTypeIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->toArray();

        $service->citizenTypes()->sync($ids);
    }

    /**
     * Create service with translations.
     *
     * @return \Najaz\Service\Contracts\Service
     */
    public function create(array $data)
    {
        // TranslatableModel will handle translations automatically
        // The data should come with locale structure like: ['ar' => ['name' => '...'], 'en' => ['name' => '...']]
        return parent::create($data);
    }

    /**
     * Update service with translations.
     *
     * @param  int  $id
     * @return \Najaz\Service\Contracts\Service
     */
    public function update(array $data, $id)
    {
        // TranslatableModel will handle translations automatically
        return parent::update($data, $id);
    }

    protected function toBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }

    /**
     * Get all available attribute groups for selection.
     */
    public function getAllAttributeGroups(?string $locale = null): \Illuminate\Support\Collection
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        return ServiceAttributeGroupProxy::modelClass()::with([
            'translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.attributeType.options.translations',
        ])->orderBy('sort_order')->get();
    }

    /**
     * Get all attribute types for field management.
     */
    public function getAttributeTypes(?string $locale = null): \Illuminate\Support\Collection
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        try {
            // Try to get repository using different methods
            $repository = null;

            // Method 1: Direct app() call
            try {
                $repository = app(\Najaz\Service\Repositories\ServiceAttributeTypeRepository::class);
            } catch (\Exception $e) {
                // Repository not available, will use model directly
            }

            // Method 2: Use model directly if repository fails
            if (! $repository) {
                try {
                    $modelClass = \Najaz\Service\Models\ServiceAttributeTypeProxy::modelClass();
                    $types = $modelClass::with(['translations', 'options.translations'])
                        ->orderBy('position')
                        ->get();

                    if ($types->isEmpty()) {
                        return collect([]);
                    }

                    // Map the types manually
                    return $types->map(function ($type) use ($locale) {
                        $translation = $type->translate($locale);

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
                                    'id'         => $option->id,
                                    'code'       => $option->code,
                                    'admin_name' => $option->admin_name,
                                    'labels'     => $optionLabels,
                                    'sort_order' => $option->sort_order ?? 0,
                                ];
                            }
                        }

                        return [
                            'id'            => $type->id,
                            'code'          => $type->code,
                            'type'          => $type->type,
                            'name'          => $translation?->name ?? $type->code,
                            'validation'    => $type->validation,
                            'regex'         => $type->regex,
                            'default_value' => $type->default_value,
                            'is_required'   => $type->is_required,
                            'is_unique'     => $type->is_unique,
                            'options'       => $options,
                            'translations'  => $type->translations->map(fn ($t) => [
                                'locale' => $t->locale,
                                'name'   => $t->name,
                            ])->toArray(),
                        ];
                    });
                } catch (\Exception $e) {
                    return collect([]);
                }
            }

            $types = $repository
                ->with(['translations', 'options.translations'])
                ->orderBy('position')
                ->get();

            if ($types->isEmpty()) {
                return collect([]);
            }

            return $types->map(function ($type) use ($locale) {
                $translation = $type->translate($locale);

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
                            'id'         => $option->id,
                            'code'       => $option->code,
                            'admin_name' => $option->admin_name,
                            'labels'     => $optionLabels,
                            'sort_order' => $option->sort_order ?? 0,
                        ];
                    }
                }

                return [
                    'id'            => $type->id,
                    'code'          => $type->code,
                    'type'          => $type->type,
                    'name'          => $translation?->name ?? $type->code,
                    'validation'    => $type->validation,
                    'regex'         => $type->regex,
                    'default_value' => $type->default_value,
                    'is_required'   => $type->is_required,
                    'is_unique'     => $type->is_unique,
                    'options'       => $options,
                    'translations'  => $type->translations->map(fn ($t) => [
                        'locale' => $t->locale,
                        'name'   => $t->name,
                    ])->toArray(),
                ];
            });
        } catch (\Exception $e) {
            return collect([]);
        }
    }

    /**
     * Get validations enum values.
     */
    public static function getValidations(): array
    {
        return \Webkul\Attribute\Enums\ValidationEnum::getValues();
    }

    /**
     * Get validation labels.
     */
    public static function getValidationLabels(): array
    {
        $validations = self::getValidations();

        return collect($validations)->mapWithKeys(fn ($value) => [
            $value => trans("Admin::app.services.attribute-types.index.datagrid.validation-{$value}"),
        ])->toArray();
    }

    /**
     * Get file extensions.
     */
    public static function getFileExtensions(): array
    {
        return \Najaz\Service\Enums\FileExtensionEnum::getAll();
    }

    /**
     * Get citizen type tree.
     */
    public static function getCitizenTypeTree(): array
    {
        return \Najaz\Citizen\Models\CitizenTypeProxy::modelClass()::orderBy('name')
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
     * Build available fields list for document template.
     *
     * @param  \Najaz\Service\Contracts\Service  $service
     */
    public function getAvailableFieldsForTemplate($service, ?string $locale = null): array
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

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
            $pivotRelations = ServiceAttributeGroupService::with([
                'attributeGroup.translations',
                'fields.translations',
                'fields.attributeType.translations',
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');
        }

        // Add fields from service attribute groups
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
                    'code'  => $groupCode.'.'.$field->code,
                    'label' => $groupName.' - '.$fieldLabel,
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

    /**
     * Prepare service for edit page with all required relations loaded.
     *
     * @return \Najaz\Service\Contracts\Service
     */
    public function findForEdit(int $id)
    {
        $service = $this->withAttributeGroupsForEdit()
            ->with(['translations', 'citizenTypes'])
            ->findOrFail($id);

        // Load pivot relations with translations and fields
        $pivotIds = $service->attributeGroups->pluck('pivot.id')->filter();
        if ($pivotIds->isNotEmpty()) {
            $pivotRelations = ServiceAttributeGroupService::with([
                'translations',
                'fields.translations',
                'fields.attributeType.translations',
                'fields.options.translations',
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');

            // Attach loaded pivot relations to groups
            foreach ($service->attributeGroups as $group) {
                $pivotId = $group->pivot->id ?? null;
                if ($pivotId && isset($pivotRelations[$pivotId])) {
                    // Replace pivot with loaded relation
                    $group->setRelation('pivot', $pivotRelations[$pivotId]);
                }
            }
        }

        return $service;
    }

    /**
     * Scope to load attribute groups with all required relations for edit page.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAttributeGroupsForEdit($query)
    {
        return $query->with([
            'attributeGroups.translations',
            'attributeGroups.fields.translations',
            'attributeGroups.fields.attributeType.translations',
            'attributeGroups.fields.attributeType.options.translations',
        ]);
    }

    /**
     * Prepare service data for Vue component.
     *
     * @param  \Najaz\Service\Contracts\Service  $service
     */
    public function prepareServiceForVue($service, ?string $locale = null): array
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        // Use already loaded relations if available (optimization)
        if (! $service->relationLoaded('attributeGroups')) {
            $service->load([
                'attributeGroups.translations',
                'attributeGroups.fields.translations',
                'attributeGroups.fields.attributeType.translations',
                'attributeGroups.fields.attributeType.options.translations',
            ]);
        }

        // Load pivot relations with translations and fields (only if not already loaded)
        $pivotIds = $service->attributeGroups->pluck('pivot.id')->filter();
        $pivotRelations = collect();
        if ($pivotIds->isNotEmpty()) {
            // Check if pivot relations are already loaded
            $firstPivot = $service->attributeGroups->first()?->pivot;
            $needsLoading = ! $firstPivot || ! $firstPivot->relationLoaded('fields');

            if ($needsLoading) {
                $pivotRelations = ServiceAttributeGroupService::with([
                    'translations',
                    'fields.translations',
                    'fields.attributeType.translations',
                    'fields.options.translations',
                ])->whereIn('id', $pivotIds)->get()->keyBy('id');

                // Attach loaded pivot relations to groups
                foreach ($service->attributeGroups as $group) {
                    $pivotId = $group->pivot->id ?? null;
                    if ($pivotId && isset($pivotRelations[$pivotId])) {
                        $group->setRelation('pivot', $pivotRelations[$pivotId]);
                    }
                }
            } else {
                // Use already loaded pivot relations
                foreach ($service->attributeGroups as $group) {
                    $pivotId = $group->pivot->id ?? null;
                    if ($pivotId) {
                        $pivotRelations[$pivotId] = $group->pivot;
                    }
                }
            }
        }

        return [
            'id'               => $service->id,
            'attribute_groups' => $service->attributeGroups->map(function ($group) use ($locale, $pivotRelations) {
                $pivotId = $group->pivot->id ?? null;
                $pivotRelation = $pivotId ? ($pivotRelations[$pivotId] ?? null) : null;

                // Get fields to use
                $fieldsToUse = $pivotRelation && $pivotRelation->fields->isNotEmpty()
                    ? $pivotRelation->fields
                    : ($group->fields ?? collect());

                // Get translations for group
                $groupTranslations = [];
                if ($group->relationLoaded('translations')) {
                    foreach ($group->translations as $trans) {
                        $groupTranslations[$trans->locale] = [
                            'name'        => $trans->name ?? '',
                            'description' => $trans->description ?? '',
                        ];
                    }
                }

                // Get pivot translations
                $pivotTranslations = [];
                if ($pivotRelation && $pivotRelation->relationLoaded('translations')) {
                    foreach ($pivotRelation->translations as $trans) {
                        $pivotTranslations[] = [
                            'locale'      => $trans->locale,
                            'custom_name' => $trans->custom_name ?? '',
                        ];
                    }
                }

                // Get fields data using shared method from ServiceAttributeGroupServiceFieldRepository
                $fieldRepository = app(\Najaz\Service\Repositories\ServiceAttributeGroupServiceFieldRepository::class);
                
                $fieldsData = $fieldsToUse->map(function ($field) use ($locale, $fieldRepository) {
                    // Use shared method to format field data
                    $formattedField = $fieldRepository->formatFieldForResponse($field, $locale);
                    
                    // Convert labels to translations format for compatibility
                    $fieldTranslations = array_map(function ($loc, $label) {
                        return ['locale' => $loc, 'label' => $label ?? ''];
                    }, array_keys($formattedField['labels'] ?? []), $formattedField['labels'] ?? []);
                    
                    // Convert options labels to translations format for compatibility
                    $optionsData = array_map(function ($option) {
                        $optionTranslations = array_map(function ($loc, $label) {
                            return ['locale' => $loc, 'label' => $label ?? ''];
                        }, array_keys($option['labels'] ?? []), $option['labels'] ?? []);
                        
                        return [
                            'id'           => $option['id'] ?? null,
                            'code'         => $option['code'] ?? $option['admin_name'] ?? '',
                            'admin_name'   => $option['admin_name'] ?? '',
                            'sort_order'   => $option['sort_order'] ?? 0,
                            'is_custom'    => $option['is_custom'] ?? true,
                            'translations' => $optionTranslations,
                        ];
                    }, $formattedField['options'] ?? []);
                    
                    // Get attribute type translations
                    $attributeType = $field->attributeType;
                    $attributeTypeTranslations = $attributeType && $attributeType->relationLoaded('translations')
                        ? $attributeType->translations->map(fn($trans) => [
                            'locale' => $trans->locale,
                            'name'   => $trans->name ?? '',
                        ])->toArray()
                        : [];
                    
                    return [
                        'id'                         => $formattedField['id'],
                        'service_attribute_field_id' => $formattedField['service_attribute_field_id'],
                        'code'                       => $formattedField['code'],
                        'type'                       => $formattedField['type'],
                        'label'                      => $formattedField['label'] ?: $formattedField['code'],
                        'sort_order'                 => $formattedField['sort_order'],
                        'is_required'                => $formattedField['is_required'],
                        'default_value'              => $formattedField['default_value'],
                        'validation_rules'           => $formattedField['validation_rules'],
                        'service_attribute_type_id'  => $formattedField['service_attribute_type_id'],
                        'translations'               => $fieldTranslations,
                        'attribute_type'             => $attributeType ? [
                            'id'           => $attributeType->id,
                            'code'         => $attributeType->code,
                            'type'         => $attributeType->type,
                            'name'         => $formattedField['attribute_type_name'] ?? $attributeType->code,
                            'translations' => $attributeTypeTranslations,
                        ] : null,
                        'options' => $optionsData,
                    ];
                })->values()->toArray();

                return [
                    'id'           => $group->id,
                    'code'         => $group->code,
                    'group_type'   => $group->group_type ?? 'general',
                    'name'         => $group->translate($locale)?->name ?? $group->code,
                    'description'  => $group->translate($locale)?->description ?? '',
                    'translations' => $groupTranslations,
                    'fields'       => $fieldsData,
                    'pivot'        => $pivotRelation ? [
                        'id'            => $pivotRelation->id,
                        'pivot_uid'     => $pivotRelation->pivot_uid,
                        'sort_order'    => $pivotRelation->sort_order ?? 0,
                        'is_notifiable' => $pivotRelation->is_notifiable ?? false,
                        'custom_code'   => $pivotRelation->custom_code ?? null,
                        'translations'  => $pivotTranslations,
                        'fields'        => $fieldsData,
                    ] : null,
                ];
            })->values()->toArray(),
        ];
    }
}
