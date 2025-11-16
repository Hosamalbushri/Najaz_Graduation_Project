<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Service\Models\Service;

class CitizenQuery
{
    /**
     * Return services available for the citizen based on their type.
     */
    public function services($rootValue)
    {
        if (! $rootValue || ! $rootValue->relationLoaded('citizenType')) {
            $rootValue?->load('citizenType');
        }

        $citizenType = $rootValue?->citizenType;

        if (! $citizenType) {
            return collect();
        }

        return $citizenType->services()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get();
    }
}

