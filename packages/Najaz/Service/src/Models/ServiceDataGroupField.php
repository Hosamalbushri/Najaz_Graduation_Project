<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Service\Contracts\ServiceDataGroupField as ServiceDataGroupFieldContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceDataGroupField extends TranslatableModel implements ServiceDataGroupFieldContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_data_group_fields';

    /**
     * Translation model foreign key column
     *
     * @var string
     */
    protected $translationForeignKey = 'service_data_group_field_id';

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = [
        'label',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_data_group_id',
        'service_field_type_id',
        'code',
        'type',
        'validation_rules',
        'default_value',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'validation_rules' => 'array',
    ];

    /**
     * Get the data group that owns the field.
     */
    public function dataGroup(): BelongsTo
    {
        return $this->belongsTo(ServiceDataGroupProxy::modelClass(), 'service_data_group_id');
    }

    /**
     * Get the field type that this field is based on.
     */
    public function fieldType(): BelongsTo
    {
        return $this->belongsTo(ServiceFieldTypeProxy::modelClass(), 'service_field_type_id');
    }
}

