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
        \Najaz\Service\Models\ServiceAttributeGroup::class,
        \Najaz\Service\Models\ServiceAttributeGroupTranslation::class,
        \Najaz\Service\Models\ServiceAttributeField::class,
        \Najaz\Service\Models\ServiceAttributeType::class,
        \Najaz\Service\Models\ServiceAttributeTypeTranslation::class,
    ];
}
