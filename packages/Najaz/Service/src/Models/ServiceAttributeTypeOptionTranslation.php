<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceAttributeTypeOptionTranslation as ServiceAttributeTypeOptionTranslationContract;

class ServiceAttributeTypeOptionTranslation extends Model implements ServiceAttributeTypeOptionTranslationContract
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'label',
        'locale',
        'service_attribute_type_option_id',
    ];
}













