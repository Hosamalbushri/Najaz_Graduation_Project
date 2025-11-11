<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceAttributeTypeTranslation as ServiceAttributeTypeTranslationContract;

class ServiceAttributeTypeTranslation extends Model implements ServiceAttributeTypeTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_type_translations';

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
        'locale',
        'service_attribute_type_id',
    ];
}


