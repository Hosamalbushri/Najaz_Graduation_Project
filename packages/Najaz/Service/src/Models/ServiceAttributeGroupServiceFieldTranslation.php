<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceAttributeGroupServiceFieldTranslation as ServiceAttributeGroupServiceFieldTranslationContract;

class ServiceAttributeGroupServiceFieldTranslation extends Model implements ServiceAttributeGroupServiceFieldTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_group_service_field_translations';

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
        'service_attribute_group_service_field_id',
        'locale',
        'label',
    ];
}

