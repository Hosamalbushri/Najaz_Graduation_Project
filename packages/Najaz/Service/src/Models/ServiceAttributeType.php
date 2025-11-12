<?php

namespace Najaz\Service\Models;

use Najaz\Service\Contracts\ServiceAttributeType as ServiceAttributeTypeContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceAttributeType extends TranslatableModel implements ServiceAttributeTypeContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_types';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_attribute_type_id';

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = ['name'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'type',
        'is_user_defined',
        'is_required',
        'is_unique',
        'position',
        'validation',
        'regex',
        'default_value',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_required'    => 'boolean',
        'is_unique'      => 'boolean',
        'is_user_defined'=> 'boolean',
        'position'       => 'integer',
    ];
}


