<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Najaz\Service\Contracts\ServiceDataGroup as ServiceDataGroupContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceDataGroup extends TranslatableModel implements ServiceDataGroupContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_data_groups';

    /**
     * Translation model foreign key column
     *
     * @var string
     */
    protected $translationForeignKey = 'service_data_group_id';

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
        'sort_order',
    ];

    /**
     * Get the fields for the data group.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ServiceDataGroupFieldProxy::modelClass())
            ->orderBy('sort_order');
    }
}

