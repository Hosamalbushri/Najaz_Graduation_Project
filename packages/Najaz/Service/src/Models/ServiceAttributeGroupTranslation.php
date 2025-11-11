<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceAttributeGroupTranslation as ServiceAttributeGroupTranslationContract;

class ServiceAttributeGroupTranslation extends Model implements ServiceAttributeGroupTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_group_translations';

    /**
     * Set timestamp false.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Set fillable property to the model.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'locale',
        'service_attribute_group_id',
    ];
}


