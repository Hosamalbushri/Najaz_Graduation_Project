<?php

namespace Najaz\Service\Repositories;

use Illuminate\Support\Str;
use Webkul\Core\Eloquent\Repository;

class ServiceCustomizableOptionRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceCustomizableOption';
    }

    /**
     * Save customizable options.
     *
     * @param  array  $data
     * @param  \Najaz\Service\Contracts\Service  $service
     * @return void
     */
    public function saveCustomizableOptions($data, $service)
    {
        $previousCustomizableOptionIds = $service->customizable_options()->pluck('id');

        if (isset($data['customizable_options'])) {
            foreach ($data['customizable_options'] as $customizableOptionId => $customizableOptionInputs) {
                // Prepare option data - TranslatableModel will handle translations automatically
                $optionData = array_merge([
                    'service_id' => $service->id,
                ], $customizableOptionInputs);

                // Remove service_id from optionData if it exists in customizableOptionInputs
                unset($optionData['service_id']);
                $optionData['service_id'] = $service->id;

                if (Str::contains($customizableOptionId, 'option_')) {
                    // Create new option - TranslatableModel handles translations automatically
                    $this->create($optionData);
                } else {
                    // Update existing option
                    if (is_numeric($index = $previousCustomizableOptionIds->search($customizableOptionId))) {
                        $previousCustomizableOptionIds->forget($index);
                    }

                    // TranslatableModel handles translations automatically
                    $this->update($optionData, $customizableOptionId);
                }
            }
        }

        foreach ($previousCustomizableOptionIds as $previousCustomizableOptionId) {
            $this->delete($previousCustomizableOptionId);
        }
    }
}

