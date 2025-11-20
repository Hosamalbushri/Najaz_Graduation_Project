<?php

namespace Najaz\Request\Repositories;

use Webkul\Core\Eloquent\Repository;

class ServiceRequestAdminNoteRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Najaz\Request\Contracts\ServiceRequestAdminNote';
    }
}

