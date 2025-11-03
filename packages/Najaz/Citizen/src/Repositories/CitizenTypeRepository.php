<?php

namespace Najaz\Citizen\Repositories;

use Webkul\Core\Eloquent\Repository;

class CitizenTypeRepository extends Repository
{

    public function model()
    {
        return 'Najaz\Citizen\Contracts\CitizenType';
    }
}
