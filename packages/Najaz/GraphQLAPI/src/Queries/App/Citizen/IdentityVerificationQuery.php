<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Citizen\Models\IdentityVerification;

class IdentityVerificationQuery
{
    /**
     * Get the identity verification request for the authenticated citizen.
     * Since each citizen can have only one verification, this returns a single item or null.
     *
     * @return \Illuminate\Support\Collection
     */
    public function list(): \Illuminate\Support\Collection
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $verification = $citizen->identityVerification;

        return $verification ? collect([$verification]) : collect();
    }

    /**
     * Get the identity verification request for the authenticated citizen.
     * Since each citizen can have only one verification, this is the same as list().
     */
    public function latest(): ?IdentityVerification
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        return $citizen->identityVerification;
    }
}

