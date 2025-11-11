<?php

namespace Najaz\Service\Repositories;

use Webkul\Core\Eloquent\Repository;

class ServiceAttributeTypeRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceAttributeType';
    }
}


