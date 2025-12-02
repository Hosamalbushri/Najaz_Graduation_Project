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

        $label = $optionData['label'] ?? $optionData['labels'] ?? '';
        $locale = $optionData['locale'] ?? null;
        
        // Handle both old format (array) and new format (string with locale)
        if (is_array($label)) {
            $this->syncTranslations($option, $label);
        } else {
            $this->syncTranslations($option, $label, $locale);
        }

        return $option;
    }

    /**
     * Sync translations for an option.
     */
    protected function syncTranslations($option, $label, $locale = null): void
    {
        // If label is an array (old format), handle it
        if (is_array($label)) {
            foreach (core()->getAllLocales() as $localeObj) {
                $translation = $option->translateOrNew($localeObj->code);
                $translation->label = $label[$localeObj->code] ?? '';
                $translation->save();
            }
        } else {
            // New format: single label with locale
            $localeCode = $locale ?? app()->getLocale();
            $translation = $option->translateOrNew($localeCode);
            $translation->label = $label;
            $translation->save();
        }
    }
}

