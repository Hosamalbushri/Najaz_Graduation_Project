<?php

namespace Najaz\Service\Models;

use Najaz\Service\Contracts\ServiceFieldType as ServiceFieldTypeContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceFieldType extends TranslatableModel implements ServiceFieldTypeContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_field_types';

    /**
     * Translation model foreign key column
     *
     * @var string
     */
    protected $translationForeignKey = 'service_field_type_id';

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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_required' => 'boolean',
        'is_unique' => 'boolean',
        'is_user_defined' => 'boolean',
        'position' => 'integer',
    ];
}

