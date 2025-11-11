<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Service\Contracts\ServiceAttributeField as ServiceAttributeFieldContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceAttributeField extends TranslatableModel implements ServiceAttributeFieldContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_fields';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_attribute_field_id';

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
        'service_attribute_group_id',
        'service_attribute_type_id',
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
     * Get the attribute group that owns the field.
     */
    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(ServiceAttributeGroupProxy::modelClass(), 'service_attribute_group_id');
    }

    /**
     * Get the attribute type that this field is based on.
     */
    public function attributeType(): BelongsTo
    {
        return $this->belongsTo(ServiceAttributeTypeProxy::modelClass(), 'service_attribute_type_id');
    }
}


