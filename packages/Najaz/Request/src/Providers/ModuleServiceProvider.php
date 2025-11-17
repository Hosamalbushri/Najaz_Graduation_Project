<?php

namespace Najaz\Request\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Najaz\Request\Models\ServiceRequest::class,
    ];
}