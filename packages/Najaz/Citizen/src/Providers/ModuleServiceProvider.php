<?php

namespace Najaz\Citizen\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Najaz\Citizen\Models\Citizen::class,
        \Najaz\Citizen\Models\CitizenType::class,
    ];
}