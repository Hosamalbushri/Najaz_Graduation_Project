<?php

namespace Najaz\Request\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Najaz\Citizen\Models\CitizenProxy;
use Najaz\Request\Contracts\ServiceRequest as ServiceRequestContract;
use Najaz\Service\Models\ServiceProxy;
use Webkul\User\Models\Admin;

class ServiceRequest extends Model implements ServiceRequestContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'increment_id',
        'service_id',
        'citizen_id',
        'status',
        'rejection_reason',
        'citizen_first_name',
        'citizen_middle_name',
        'citizen_last_name',
        'citizen_national_id',
        'citizen_type_name',
        'locale',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Get the service that this request belongs to.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceProxy::modelClass(), 'service_id');
    }

    /**
     * Get the citizen that submitted this request.
     */
    public function citizen(): BelongsTo
    {
        return $this->belongsTo(CitizenProxy::modelClass(), 'citizen_id');
    }

    /**
     * Get the related parties of this service request.
     */
    public function beneficiaries(): BelongsToMany
    {
        return $this->belongsToMany(
            CitizenProxy::modelClass(),
            'service_request_beneficiaries',
            'service_request_id',
            'citizen_id'
        )->withPivot('group_code')
            ->withTimestamps();
    }

    /**
     * Get the form data for this service request.
     */
    public function formData(): HasMany
    {
        return $this->hasMany(ServiceRequestFormData::class, 'service_request_id');
    }

    /**
     * Get the admin notes for this service request.
     */
    public function adminNotes(): HasMany
    {
        return $this->hasMany(ServiceRequestAdminNote::class, 'service_request_id');
    }
}

