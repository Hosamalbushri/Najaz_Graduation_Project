<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Najaz\Service\Contracts\ServiceAttributeGroupService as ServiceAttributeGroupServiceContract;

class ServiceAttributeGroupService extends Pivot implements ServiceAttributeGroupServiceContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_group_service';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pivot_uid',
        'service_id',
        'service_attribute_group_id',
        'sort_order',
        'is_notifiable',
        'custom_code',
        'custom_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_notifiable' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the service that owns this pivot.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceProxy::modelClass(), 'service_id');
    }

    /**
     * Get the attribute group that owns this pivot.
     */
    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(ServiceAttributeGroupProxy::modelClass(), 'service_attribute_group_id');
    }

    /**
     * Get the fields for this pivot relation.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ServiceAttributeGroupServiceFieldProxy::modelClass(), 'service_attribute_group_service_id')
            ->orderBy('sort_order');
    }
}

