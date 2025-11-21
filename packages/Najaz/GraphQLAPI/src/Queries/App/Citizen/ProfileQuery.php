<?php

namespace Najaz\GraphQLAPI\Queries\App\Citizen;

use Najaz\Citizen\Models\Citizen;

class ProfileQuery
{
    /**
     * Get the authenticated citizen's profile.
     */
    public function profile(): ?Citizen
    {
        return najaz_graphql()->authorize('citizen-api');
    }
}

