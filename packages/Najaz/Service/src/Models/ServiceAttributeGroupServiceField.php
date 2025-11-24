<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Najaz\Service\Contracts\ServiceAttributeGroupServiceField as ServiceAttributeGroupServiceFieldContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceAttributeGroupServiceField extends TranslatableModel implements ServiceAttributeGroupServiceFieldContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_group_service_fields';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_attribute_group_service_field_id';

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
        'service_attribute_group_service_id',
        'service_attribute_field_id',
        'service_attribute_type_id',
        'code',
        'type',
        'validation_rules',
        'default_value',
        'is_required',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'validation_rules' => 'array',
        'is_required' => 'bool',
        'sort_order' => 'integer',
    ];

    /**
     * Get the pivot relation that owns this field.
     */
    public function pivotRelation(): BelongsTo
    {
        return $this->belongsTo(
            ServiceAttributeGroupService::class,
            'service_attribute_group_service_id',
            'id'
        );
    }

    /**
     * Get the original field template that this field is based on.
     */
    public function originalField(): BelongsTo
    {
        return $this->belongsTo(ServiceAttributeFieldProxy::modelClass(), 'service_attribute_field_id');
    }

    /**
     * Get the attribute type that this field is based on.
     */
    public function attributeType(): BelongsTo
    {
        return $this->belongsTo(ServiceAttributeTypeProxy::modelClass(), 'service_attribute_type_id');
    }

    /**
     * Get the options for this field.
     */
    public function options(): HasMany
    {
        return $this->hasMany(
            ServiceAttributeGroupServiceFieldOptionProxy::modelClass(),
            'service_attribute_group_service_field_id',
            'id'
        )->orderBy('sort_order');
    }
}

