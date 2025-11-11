<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Najaz\Service\Contracts\Service as ServiceContract;

class Service extends Model implements ServiceContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'status',
        'image',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price'      => 'decimal:2',
        'status'     => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the customizable options for the service.
     */

    /**
     * Get the attribute groups assigned to the service.
     */
    public function attributeGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceAttributeGroupProxy::modelClass(),
            'service_attribute_group_service',
            'service_id',
            'service_attribute_group_id'
        )->withPivot('sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

}
