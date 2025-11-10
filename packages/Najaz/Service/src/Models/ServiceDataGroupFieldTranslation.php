<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceDataGroupFieldTranslation as ServiceDataGroupFieldTranslationContract;

class ServiceDataGroupFieldTranslation extends Model implements ServiceDataGroupFieldTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_data_group_field_translations';

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
        'service_data_group_field_id',
    ];
}

