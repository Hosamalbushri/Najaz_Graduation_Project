<?php

namespace Najaz\Service\Repositories;

use Najaz\Service\Models\ServiceAttributeGroupServiceField;
use Webkul\Core\Eloquent\Repository;

class ServiceAttributeGroupServiceFieldOptionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceAttributeGroupServiceFieldOption';
    }

    /**
     * Sync options for the given field.
     */
    public function syncOptions(ServiceAttributeGroupServiceField $field, array $options = []): void
    {
        $existingOptionIds = [];

        foreach ($options as $optionData) {
            $option = $this->persistOption($field, $optionData);

            $existingOptionIds[] = $option->id;
        }

        if (! empty($existingOptionIds)) {
            $field->options()
                ->whereNotIn('id', $existingOptionIds)
                ->delete();
        } else {
            $field->options()->delete();
        }
    }

    /**
     * Persist a single option.
     */
    public function persistOption(ServiceAttributeGroupServiceField $field, array $optionData): \Illuminate\Database\Eloquent\Model
    {
        $payload = [
            'service_attribute_group_service_field_id' => $field->id,
            'service_attribute_type_option_id' => $optionData['service_attribute_type_option_id'] ?? null,
            'admin_name' => $optionData['admin_name'] ?? '',
            'sort_order' => $optionData['sort_order'] ?? 0,
            'is_custom' => $optionData['is_custom'] ?? false,
        ];

        $optionId = $optionData['id'] ?? null;

        if ($optionId) {
            $this->update($payload, $optionId);

            /** @var \Najaz\Service\Models\ServiceAttributeGroupServiceFieldOption $option */
            $option = $this->findOrFail($optionId);
        } else {
            /** @var \Najaz\Service\Models\ServiceAttributeGroupServiceFieldOption $option */
            $option = $this->create($payload);
        }

        $this->syncTranslations($option, $optionData['label'] ?? $optionData['labels'] ?? []);

        return $option;
    }

    /**
     * Sync translations for an option.
     */
    protected function syncTranslations($option, array $labels): void
    {
        foreach (core()->getAllLocales() as $locale) {
            $translation = $option->translateOrNew($locale->code);
            $translation->label = $labels[$locale->code] ?? '';
            $translation->save();
        }
    }
}

