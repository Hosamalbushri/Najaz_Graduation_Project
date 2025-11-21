<?php

namespace Najaz\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Notification\Contracts\Notification as NotificationContract;
use Najaz\Citizen\Models\IdentityVerificationProxy;
use Najaz\Request\Models\ServiceRequestProxy;

class Notification extends Model implements NotificationContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_notifications';

    protected $fillable = [
        'type',
        'read',
        'entity_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['datetime'];

    /**
     * Get Service Request Details.
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequestProxy::modelClass(), 'entity_id');
    }

    /**
     * Get Identity Verification Details.
     */
    public function identityVerification()
    {
        return $this->belongsTo(IdentityVerificationProxy::modelClass(), 'entity_id');
    }

    /**
     * Get the related entity based on type.
     */
    public function entity()
    {
        if ($this->type === 'service_request') {
            return $this->serviceRequest();
        } elseif ($this->type === 'identity_verification') {
            return $this->identityVerification();
        }

        return null;
    }

    /**
     * Get datetime in human readable format.
     */
    public function getDatetimeAttribute()
    {
        return $this->created_at?->diffForHumans();
    }
}

