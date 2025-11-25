<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Najaz\Service\Models\ServiceAttributeGroupProxy;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Najaz\Service\Repositories\ServiceAttributeGroupServiceFieldRepository;
use Najaz\Service\Repositories\ServiceRepository;
use Najaz\Admin\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceGroupController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
        protected ServiceAttributeGroupServiceFieldRepository $groupServiceFieldRepository,
    ) {}

    /**
     * Store a newly created group for the service.
     */
    public function store(int $serviceId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);

        $this->validate(request(), [
            'template_id'     => 'required|exists:service_attribute_groups,id',
            'code'            => 'required|string|max:255',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'is_notifiable'   => 'nullable|boolean',
            'sort_order'      => 'nullable|integer',
        ]);

        $templateGroupId = (int) request()->input('template_id');
        $templateGroup = ServiceAttributeGroupProxy::modelClass()::with([
            'fields.translations',
            'fields.attributeType.translations',
            'fields.attributeType.options.translations', // Load options for fields that need them
        ])->findOrFail($templateGroupId);

        // Check for duplicate code
        $customCode = request()->input('code');
        $normalizedCode = mb_strtolower(trim($customCode));
        
        $existingGroup = ServiceAttributeGroupService::where('service_id', $serviceId)
            ->where(function ($query) use ($normalizedCode) {
                $query->whereRaw('LOWER(custom_code) = ?', [$normalizedCode])
                    ->orWhereHas('attributeGroup', function ($q) use ($normalizedCode) {
                        $q->whereRaw('LOWER(code) = ?', [$normalizedCode]);
                    });
            })
            ->first();

        if ($existingGroup) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.attribute-groups.duplicate-code', ['code' => $customCode]),
            ], 422);
        }

        // Get current max sort_order
        $maxSortOrder = ServiceAttributeGroupService::where('service_id', $serviceId)
            ->max('sort_order') ?? -1;

        $pivotUid = (string) Str::uuid();
        $sortOrder = request()->input('sort_order', $maxSortOrder + 1);
        $isNotifiable = $this->toBoolean(request()->input('is_notifiable', false));

        // Check if group supports notification
        $groupType = $templateGroup->group_type ?? 'general';
        $supportsNotification = $this->groupSupportsNotification($templateGroup);
        
        if (! $supportsNotification) {
            $isNotifiable = false;
        }

        DB::transaction(function () use ($service, $templateGroupId, $pivotUid, $sortOrder, $isNotifiable, $customCode, $templateGroup) {
            $pivotRelation = ServiceAttributeGroupService::create([
                'service_id'                 => $service->id,
                'service_attribute_group_id' => $templateGroupId,
                'pivot_uid'                  => $pivotUid,
                'sort_order'                 => $sortOrder,
                'is_notifiable'              => $isNotifiable,
                'custom_code'                => $customCode,
                'custom_name'                => request()->input('name'),
            ]);

            // Copy fields from template when creating a new pivot relation
            if ($templateGroup->fields) {
                foreach ($templateGroup->fields as $templateField) {
                    $this->groupServiceFieldRepository->copyFieldFromTemplate(
                        $templateField,
                        $pivotRelation
                    );
                }
            }
        });

        // Reload the service with the new group
        $service = $service->fresh(['attributeGroups.translations', 'attributeGroups.fields.translations']);

        $pivotRelation = ServiceAttributeGroupService::with([
            'attributeGroup.translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.options.translations',
        ])->where('pivot_uid', $pivotUid)
            ->where('service_id', $service->id)
            ->first();

        if (! $pivotRelation) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.attribute-groups.create-error'),
            ], 500);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.attribute-groups.create-success'),
            'data'    => $this->formatPivotForResponse($pivotRelation),
        ]);
    }

    /**
     * Update the specified group for the service.
     */
    public function update(int $serviceId, int $pivotId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        $this->validate(request(), [
            'code'          => 'required|string|max:255',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_notifiable' => 'nullable|boolean',
        ]);

        $customCode = request()->input('code');
        $normalizedCode = mb_strtolower(trim($customCode));
        
        // Check for duplicate code (excluding current pivot)
        $existingGroup = ServiceAttributeGroupService::where('service_id', $serviceId)
            ->where('id', '!=', $pivotId)
            ->where(function ($query) use ($normalizedCode) {
                $query->whereRaw('LOWER(custom_code) = ?', [$normalizedCode])
                    ->orWhereHas('attributeGroup', function ($q) use ($normalizedCode) {
                        $q->whereRaw('LOWER(code) = ?', [$normalizedCode]);
                    });
            })
            ->first();

        if ($existingGroup) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.attribute-groups.duplicate-code', ['code' => $customCode]),
            ], 422);
        }

        $group = $pivotRelation->attributeGroup;
        $supportsNotification = $this->groupSupportsNotification($group);
        $isNotifiable = $supportsNotification 
            ? $this->toBoolean(request()->input('is_notifiable', false))
            : false;

        $pivotRelation->update([
            'custom_code'   => $customCode,
            'custom_name'   => request()->input('name'),
            'is_notifiable' => $isNotifiable,
        ]);

        // Reload the pivot relation with updated data
        $pivotRelation = ServiceAttributeGroupService::with([
            'attributeGroup.translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.options.translations',
        ])->findOrFail($pivotId);

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.attribute-groups.update-success'),
            'data'    => $this->formatPivotForResponse($pivotRelation),
        ]);
    }

    /**
     * Remove the specified group from the service.
     */
    public function destroy(int $serviceId, int $pivotId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);
        
        $pivotRelation = ServiceAttributeGroupService::findOrFail($pivotId);

        // Verify that this pivot belongs to the service
        if ($pivotRelation->service_id != $serviceId) {
            abort(404);
        }

        // Check if this group is used in any requests
        $groupCode = $pivotRelation->custom_code ?? $pivotRelation->attributeGroup->code;
        
        $hasRequests = DB::table('service_request_form_data')
            ->join('service_requests', 'service_request_form_data.service_request_id', '=', 'service_requests.id')
            ->where('service_requests.service_id', $serviceId)
            ->where('service_request_form_data.group_code', $groupCode)
            ->exists();
        
        if ($hasRequests) {
            return new JsonResponse([
                'message' => trans(
                    'Admin::app.services.services.attribute-groups.delete-has-requests',
                    ['group_code' => $groupCode]
                ),
            ], 422);
        }

        $pivotRelation->delete();

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.attribute-groups.delete-success'),
        ]);
    }

    /**
     * Reorder groups for the service.
     */
    public function reorder(int $serviceId): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($serviceId);

        $this->validate(request(), [
            'pivot_ids' => 'required|array',
            'pivot_ids.*' => 'required|integer|exists:service_attribute_group_service,id',
        ]);

        $pivotIds = request()->input('pivot_ids', []);

        // Verify all pivots belong to this service
        $pivotCount = ServiceAttributeGroupService::where('service_id', $serviceId)
            ->whereIn('id', $pivotIds)
            ->count();

        if ($pivotCount !== count($pivotIds)) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.services.attribute-groups.invalid-pivot-ids'),
            ], 422);
        }

        DB::transaction(function () use ($serviceId, $pivotIds) {
            foreach ($pivotIds as $index => $pivotId) {
                ServiceAttributeGroupService::where('id', $pivotId)
                    ->where('service_id', $serviceId)
                    ->update(['sort_order' => $index]);
            }
        });

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.attribute-groups.reorder-success'),
        ]);
    }

    /**
     * Format pivot relation data for frontend response.
     */
    protected function formatPivotForResponse($pivotRelation): array
    {
        if (! $pivotRelation) {
            throw new \Exception('Pivot relation not found');
        }

        $locale = app()->getLocale();
        $group = $pivotRelation->attributeGroup;

        if (! $group) {
            throw new \Exception('Attribute group not found for pivot relation');
        }

        $fields = ($pivotRelation->fields ?? collect())->map(function ($field) use ($locale) {
            // Get labels for all locales
            $labels = [];
            foreach (core()->getAllLocales() as $loc) {
                $translation = $field->translate($loc->code);
                $labels[$loc->code] = $translation?->label ?? '';
            }
            
            // Get options with labels for all locales
            $options = [];
            if ($field->options) {
                foreach ($field->options as $option) {
                    $optionLabels = [];
                    foreach (core()->getAllLocales() as $loc) {
                        $optionTranslation = $option->translate($loc->code);
                        $optionLabels[$loc->code] = $optionTranslation?->label ?? $option->admin_name ?? $option->code ?? '';
                    }
                    
                    $options[] = [
                        'id' => $option->id,
                        'uid' => "option_{$option->id}",
                        'service_attribute_type_option_id' => $option->service_attribute_type_option_id ?? null,
                        'admin_name' => $option->admin_name ?? '',
                        'code' => $option->code ?? $option->admin_name ?? '',
                        'labels' => $optionLabels,
                        'sort_order' => $option->sort_order ?? 0,
                        'is_custom' => $option->is_custom ?? false,
                    ];
                }
            }
            
            return [
                'id'                      => $field->id,
                'service_attribute_field_id' => $field->service_attribute_field_id ?? null,
                'template_field_id'       => $field->template_field_id ?? $field->id ?? null,
                'code'                    => $field->code,
                'label'                   => $field->translate($locale)?->label ?? $field->code,
                'labels'                  => $labels,
                'type'                    => $field->type,
                'attribute_type_name'     => $field->attributeType?->translate($locale)?->name ?? $field->type,
                'service_attribute_type_id' => $field->service_attribute_type_id ?? null,
                'validation_rules'        => $field->validation_rules ?? null,
                'default_value'           => $field->default_value ?? null,
                'is_required'             => $field->is_required ?? false,
                'sort_order'              => $field->sort_order ?? 0,
                'options'                 => $options,
            ];
        })->sortBy('sort_order')->values()->toArray();

        return [
            'service_attribute_group_id' => $pivotRelation->id,
            'template_id'                => $group->id,
            'pivot_uid'                  => $pivotRelation->pivot_uid,
            'code'                       => $pivotRelation->custom_code ?? $group->code,
            'name'                       => $pivotRelation->custom_name ?? $group->translate($locale)?->name ?? $group->code,
            'display_name'               => $pivotRelation->custom_name ?? $group->translate($locale)?->name ?? $group->code,
            'description'                => $group->translate($locale)?->description ?? '',
            'group_type'                 => $group->group_type ?? 'general',
            'sort_order'                 => $pivotRelation->sort_order ?? 0,
            'is_notifiable'              => $pivotRelation->is_notifiable ?? false,
            'supports_notification'      => $this->groupSupportsNotification($group),
            'fields'                     => $fields,
        ];
    }

    /**
     * Check if group supports notification.
     */
    protected function groupSupportsNotification($group): bool
    {
        if (! $group) {
            return false;
        }

        $type = (strtolower($group->group_type ?? 'general'));

        if ($type !== 'citizen') {
            return false;
        }

        $fields = $group->fields ?? collect();

        return $fields->some(function ($field) {
            $code = strtolower($field->code ?? '');
            return $code === 'id_number';
        });
    }

    /**
     * Convert value to boolean.
     */
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
}

