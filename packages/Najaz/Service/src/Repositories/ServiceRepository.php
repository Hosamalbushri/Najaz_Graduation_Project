<?php

namespace Najaz\Service\Repositories;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Najaz\Service\Models\ServiceAttributeFieldProxy;
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

        DB::transaction(function () use ($payload, $service) {
            $groupsSync = [];

            foreach ($payload as $index => $groupData) {
                $groupData = Arr::wrap($groupData);

                $existingGroupId = ! empty($groupData['service_attribute_group_id'])
                    ? (int) $groupData['service_attribute_group_id']
                    : null;

                $templateGroupId = ! empty($groupData['template_id'])
                    ? (int) $groupData['template_id']
                    : null;

                $isNew = filter_var($groupData['is_new'] ?? false, FILTER_VALIDATE_BOOLEAN);
                $code = $groupData['code'] ?? null;
                $name = $groupData['name'] ?? null;
                $description = $groupData['description'] ?? null;
                $groupSortOrder = isset($groupData['sort_order'])
                    ? (int) $groupData['sort_order']
                    : $index;

                $groupId = $existingGroupId;

                if ($isNew || ! $existingGroupId) {
                    if (! $templateGroupId || ! $code) {
                        continue;
                    }

                    $clone = $this->cloneAttributeGroup($templateGroupId, $code, $name, $description);

                    if (! $clone) {
                        continue;
                    }

                    $groupId = $clone->id;
                } else {
                    $group = ServiceAttributeGroupProxy::modelClass()::with('translations')->find($existingGroupId);

                    if (! $group) {
                        continue;
                    }

                    $updatePayload = ['sort_order' => $groupSortOrder];

                    if ($code) {
                        $updatePayload['code'] = $code;
                    }

                    $group->update($updatePayload);

                    if ($name || $description !== null) {
                        foreach (core()->getAllLocales() as $locale) {
                            $currentTranslation = $group->translate($locale->code);

                            $translationPayload = [
                                'name' => $name ?? ($currentTranslation?->name ?? $group->code),
                            ];

                            if ($description !== null) {
                                $translationPayload['description'] = $description;
                            }

                            $group->translateOrNew($locale->code)->fill($translationPayload)->save();
                        }
                    }
                }

                if (! $groupId) {
                    continue;
                }

                $groupsSync[$groupId] = ['sort_order' => $groupSortOrder];
            }

            $service->attributeGroups()->sync($groupsSync);
        });
    }

    /**
     * Clone an attribute group with its fields and translations.
     */
    protected function cloneAttributeGroup(
        int $templateId,
        string $code,
        ?string $name = null,
        ?string $description = null
    ): ?\Illuminate\Database\Eloquent\Model
    {
        $template = ServiceAttributeGroupProxy::modelClass()::with([
            'translations',
            'fields.translations',
        ])->find($templateId);

        if (! $template) {
            return null;
        }

        $newGroup = ServiceAttributeGroupProxy::modelClass()::create([
            'code'       => $code,
            'sort_order' => $template->sort_order ?? 0,
        ]);

        foreach (core()->getAllLocales() as $locale) {
            $templateTranslation = $template->translate($locale->code);

            $newGroup->translateOrNew($locale->code)->fill([
                'name'        => $name ?? ($templateTranslation?->name ?? $code),
                'description' => $description ?? $templateTranslation?->description,
            ])->save();
        }

        foreach ($template->fields as $field) {
            $newField = ServiceAttributeFieldProxy::modelClass()::create([
                'service_attribute_group_id' => $newGroup->id,
                'service_attribute_type_id' => $field->service_attribute_type_id ?? $field->service_field_type_id,
                'code'                  => $field->code,
                'type'                  => $field->type,
                'validation_rules'      => $field->validation_rules,
                'default_value'         => $field->default_value,
                'sort_order'            => $field->sort_order ?? 0,
            ]);

            foreach (core()->getAllLocales() as $locale) {
                $fieldTranslation = $field->translate($locale->code);

                $newField->translateOrNew($locale->code)->fill([
                    'label' => $fieldTranslation?->label ?? $field->code,
                ])->save();
            }
        }

        return $newGroup->fresh();
    }
}

