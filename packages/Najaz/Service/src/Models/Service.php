<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Najaz\Citizen\Models\CitizenTypeProxy;
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
        'status'     => 'boolean',
        'sort_order' => 'integer',
    ];

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
        )->using(ServiceAttributeGroupService::class)
            ->withPivot('id', 'pivot_uid', 'sort_order', 'is_notifiable', 'custom_code', 'custom_name')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Get the citizen types that can access the service.
     */
    public function citizenTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            CitizenTypeProxy::modelClass(),
            'citizen_type_service',
            'service_id',
            'citizen_type_id'
        )->withTimestamps();
    }

    /**
     * Get the document template for this service.
     */
    public function documentTemplate(): HasOne
    {
        return $this->hasOne(ServiceDocumentTemplateProxy::modelClass(), 'service_id');
    }
}
