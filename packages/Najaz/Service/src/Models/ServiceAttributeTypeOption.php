<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Service\Contracts\ServiceAttributeTypeOption as ServiceAttributeTypeOptionContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceAttributeTypeOption extends TranslatableModel implements ServiceAttributeTypeOptionContract
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_attribute_type_option_id';

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = ['label'];

    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'service_attribute_type_id',
        'admin_name',
        'sort_order',
    ];

    /**
     * Attribute casting.
     *
     * @var array
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Relation with attribute type.
     */
    public function attributeType(): BelongsTo
    {
        return $this->belongsTo(ServiceAttributeTypeProxy::modelClass(), 'service_attribute_type_id');
    }
}













