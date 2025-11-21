<?php

namespace Najaz\Notification\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Najaz\Notification\Models\Notification::class,
    ];
}

