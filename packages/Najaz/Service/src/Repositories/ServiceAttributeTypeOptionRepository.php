<?php

namespace Najaz\Service\Repositories;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Models\ServiceAttributeType;
use Najaz\Service\Models\ServiceAttributeTypeOption;
use Webkul\Core\Eloquent\Repository;

class ServiceAttributeTypeOptionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceAttributeTypeOption';
    }

    /**
     * Sync attribute options for the given attribute type.
     */
    public function syncOptions(ServiceAttributeType $attributeType, array $options = []): void
    {
        $existingOptionIds = [];

        foreach ($options as $optionData) {
            $option = $this->persistOption($attributeType, $optionData);

            $existingOptionIds[] = $option->id;
        }

        if (! empty($existingOptionIds)) {
            $attributeType->options()
                ->whereNotIn('id', $existingOptionIds)
                ->delete();
        } else {
            $attributeType->options()->delete();
        }
    }

    /**
     * Persist a single option.
     */
    protected function persistOption(ServiceAttributeType $attributeType, array $optionData): Model
    {
        $payload = [
            'service_attribute_type_id' => $attributeType->id,
            'admin_name'                => $optionData['admin_name'] ?? '',
            'sort_order'                => $optionData['sort_order'] ?? 0,
        ];

        $optionId = $optionData['id'] ?? null;

        if ($optionId) {
            $this->update($payload, $optionId);

            /** @var \Najaz\Service\Models\ServiceAttributeTypeOption $option */
            $option = $this->findOrFail($optionId);
        } else {
            /** @var \Najaz\Service\Models\ServiceAttributeTypeOption $option */
            $option = $this->create($payload);
        }

        $this->syncTranslations($option, $optionData['label'] ?? $optionData['labels'] ?? []);

        return $option;
    }

    /**
     * Sync translations on an option.
     */
    protected function syncTranslations(ServiceAttributeTypeOption $option, array $labels): void
    {
        foreach (core()->getAllLocales() as $locale) {
            $option->translateOrNew($locale->code)->fill([
                'label' => $labels[$locale->code] ?? '',
            ])->save();
        }
    }
}


