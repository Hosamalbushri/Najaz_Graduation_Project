<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Najaz\Service\Contracts\ServiceAttributeGroup as ServiceAttributeGroupContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceAttributeGroup extends TranslatableModel implements ServiceAttributeGroupContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_groups';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_attribute_group_id';

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
        'description',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'default_name',
        'group_type',
        'sort_order',
    ];

    /**
     * Get the fields for the attribute group.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ServiceAttributeFieldProxy::modelClass())
            ->orderBy('sort_order');
    }
}


