<?php

namespace Najaz\Citizen\Repositories;

use Webkul\Core\Eloquent\Repository;

class IdentityVerificationRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Citizen\Contracts\IdentityVerification';
    }
}
















