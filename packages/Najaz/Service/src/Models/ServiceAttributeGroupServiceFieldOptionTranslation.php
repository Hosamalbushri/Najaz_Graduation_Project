<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Service\Contracts\ServiceAttributeGroupServiceFieldOptionTranslation as ServiceAttributeGroupServiceFieldOptionTranslationContract;

class ServiceAttributeGroupServiceFieldOptionTranslation extends Model implements ServiceAttributeGroupServiceFieldOptionTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_group_service_field_option_translations';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable attributes.
     *
     * @var array
     */
    protected $fillable = [
        'service_attribute_group_service_field_option_id',
        'locale',
        'label',
    ];

    /**
     * Get the option that owns this translation.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(
            ServiceAttributeGroupServiceFieldOptionProxy::modelClass(),
            'service_attribute_group_service_field_option_id',
            'id'
        );
    }
}

