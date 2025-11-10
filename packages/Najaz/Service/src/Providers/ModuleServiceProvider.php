<?php

namespace Najaz\Service\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Najaz\Service\Models\Service::class,
        \Najaz\Service\Models\ServiceCustomizableOption::class,
        \Najaz\Service\Models\ServiceCustomizableOptionTranslation::class,
        \Najaz\Service\Models\ServiceDataGroup::class,
        \Najaz\Service\Models\ServiceDataGroupTranslation::class,
        \Najaz\Service\Models\ServiceDataGroupField::class,
        \Najaz\Service\Models\ServiceFieldType::class,
        \Najaz\Service\Models\ServiceFieldTypeTranslation::class,
    ];
}
