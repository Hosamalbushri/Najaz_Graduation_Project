<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceDataGroupTranslation as ServiceDataGroupTranslationContract;

class ServiceDataGroupTranslation extends Model implements ServiceDataGroupTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_data_group_translations';

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
        'service_data_group_id',
    ];
}

