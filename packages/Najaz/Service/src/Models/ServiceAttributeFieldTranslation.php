<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceAttributeFieldTranslation as ServiceAttributeFieldTranslationContract;

class ServiceAttributeFieldTranslation extends Model implements ServiceAttributeFieldTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_field_translations';

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
        'label',
        'locale',
        'service_attribute_field_id',
    ];
}


