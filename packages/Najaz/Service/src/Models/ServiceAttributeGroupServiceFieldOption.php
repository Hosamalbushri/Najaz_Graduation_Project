<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Service\Contracts\ServiceAttributeGroupServiceFieldOption as ServiceAttributeGroupServiceFieldOptionContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceAttributeGroupServiceFieldOption extends TranslatableModel implements ServiceAttributeGroupServiceFieldOptionContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_group_service_field_options';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_attribute_group_service_field_option_id';

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = ['label'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_attribute_group_service_field_id',
        'service_attribute_type_option_id',
        'admin_name',
        'sort_order',
        'is_custom',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sort_order' => 'integer',
        'is_custom' => 'boolean',
    ];

    /**
     * Get the field that owns this option.
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(
            ServiceAttributeGroupServiceField::class,
            'service_attribute_group_service_field_id',
            'id'
        );
    }

    /**
     * Get the original option that this option is based on (if not custom).
     */
    public function originalOption(): BelongsTo
    {
        return $this->belongsTo(
            ServiceAttributeTypeOptionProxy::modelClass(),
            'service_attribute_type_option_id',
            'id'
        );
    }
}










