<?php

namespace Najaz\Service\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Najaz\Service\Models\ServiceAttributeGroupProxy;
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

    /**
     * Sync the assigned attribute groups for the given service.
     */
    public function syncAttributeGroups(?array $payload, $service): void
    {
        if (! is_array($payload)) {
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

            $service->attributeGroups()->detach();

            foreach ($groupsSync as $pivotUid => $payload) {
                $service->attributeGroups()->attach(
                    $payload['group_id'],
                    array_merge($payload['attributes'], ['pivot_uid' => $pivotUid])
                );
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

