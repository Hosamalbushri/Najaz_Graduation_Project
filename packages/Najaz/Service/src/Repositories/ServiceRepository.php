<?php

namespace Najaz\Service\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Najaz\Service\Models\ServiceAttributeGroupProxy;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Najaz\Service\Repositories\ServiceAttributeGroupServiceFieldRepository;
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
}

