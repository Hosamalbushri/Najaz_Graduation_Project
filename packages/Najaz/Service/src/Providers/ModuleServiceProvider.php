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
        \Najaz\Service\Models\ServiceAttributeTypeOption::class,
        \Najaz\Service\Models\ServiceAttributeTypeOptionTranslation::class,
        \Najaz\Service\Models\ServiceDocumentTemplate::class,
        \Najaz\Service\Models\ServiceAttributeGroupService::class,
        \Najaz\Service\Models\ServiceAttributeGroupServiceField::class,
        \Najaz\Service\Models\ServiceAttributeGroupServiceFieldTranslation::class,
        \Najaz\Service\Models\ServiceAttributeGroupServiceFieldOption::class,
        \Najaz\Service\Models\ServiceAttributeGroupServiceFieldOptionTranslation::class,
    ];
}
