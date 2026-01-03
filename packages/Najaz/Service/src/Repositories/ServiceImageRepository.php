<?php

namespace Najaz\Service\Repositories;

class ServiceImageRepository extends ServiceMediaRepository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceImage';
    }
}

