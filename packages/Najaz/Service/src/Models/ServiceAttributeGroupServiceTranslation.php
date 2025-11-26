<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceAttributeGroupServiceTranslation as ServiceAttributeGroupServiceTranslationContract;

class ServiceAttributeGroupServiceTranslation extends Model implements ServiceAttributeGroupServiceTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_group_service_translations';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'custom_name',
        'locale',
        'service_attribute_group_service_id',
    ];
}

