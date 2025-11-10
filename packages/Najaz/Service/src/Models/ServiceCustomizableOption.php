<?php

namespace Najaz\Service\Models;

use Webkul\Core\Eloquent\TranslatableModel;
use Najaz\Service\Contracts\ServiceCustomizableOption as ServiceCustomizableOptionContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceCustomizableOption extends TranslatableModel implements ServiceCustomizableOptionContract
{
    /**
     * Set timestamp false.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Add the translateable attribute.
     *
     * @var array
     */
    public $translatedAttributes = ['label'];

    /**
     * Add fillable property to the model.
     *
     * @var array
     */
    protected $fillable = [
        'is_required',
        'max_characters',
        'service_id',
        'sort_order',
        'supported_file_extensions',
        'type',
    ];

    /**
     * Get the service that owns the option.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceProxy::modelClass());
    }
}






