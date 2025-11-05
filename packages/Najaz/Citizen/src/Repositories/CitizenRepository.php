<?php

namespace Najaz\Citizen\Repositories;

use Webkul\Core\Eloquent\Repository;

class CitizenRepository  extends Repository
{

    public function model()
    {
        return 'Najaz\Citizen\Contracts\Citizen';
    }
}
