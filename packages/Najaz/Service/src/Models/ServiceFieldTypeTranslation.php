<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceFieldTypeTranslation as ServiceFieldTypeTranslationContract;

class ServiceFieldTypeTranslation extends Model implements ServiceFieldTypeTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_field_type_translations';

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
        'service_field_type_id',
    ];
}

