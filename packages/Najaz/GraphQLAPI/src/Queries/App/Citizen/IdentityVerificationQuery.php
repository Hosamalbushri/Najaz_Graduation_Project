<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Citizen\Models\IdentityVerification;

class IdentityVerificationQuery
{
    /**
     * List all identity verification requests for the authenticated citizen.
     *
     * @return \Illuminate\Support\Collection
     */
    public function list(): \Illuminate\Support\Collection
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        return $citizen->identityVerifications()
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Get the latest identity verification request for the authenticated citizen.
     */
    public function latest(): ?IdentityVerification
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        return $citizen->identityVerifications()
            ->orderByDesc('created_at')
            ->first();
    }
}

