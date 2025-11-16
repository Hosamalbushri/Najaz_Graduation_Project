<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Service\Models\Service;

class ServiceQuery
{
    /**
     * List services available for the authenticated citizen.
     */
    public function list(): \Illuminate\Support\Collection
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        return $this->collectServices($citizen);
    }

    /**
     * Fetch a specific service if it belongs to the citizen's type.
     */
    public function find($_, array $args): ?Service
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            return null;
        }

        return $citizenType->services()
            ->where('services.id', $args['id'])
            ->where('services.status', 1)
            ->first();
    }

    protected function collectServices($citizen): \Illuminate\Support\Collection
    {
        $citizenType = $citizen->citizenType;

        if (! $citizenType) {
            return collect();
        }

        return $citizenType->services()
            ->where('status', 1)
            ->orderBy('sort_order')
            ->get();
    }
}

