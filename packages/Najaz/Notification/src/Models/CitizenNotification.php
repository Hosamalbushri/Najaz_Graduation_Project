<?php

namespace Najaz\Notification\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Notification\Contracts\CitizenNotification as CitizenNotificationContract;
use Najaz\Citizen\Models\CitizenProxy;
use Najaz\Citizen\Models\IdentityVerificationProxy;
use Najaz\Request\Models\ServiceRequestProxy;

class CitizenNotification extends Model implements CitizenNotificationContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'citizen_notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'citizen_id',
        'type',
        'entity_type',
        'entity_id',
        'title',
        'message',
        'action_url',
        'metadata',
        'read_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['translated_title', 'translated_message'];

    /**
     * Get translated title attribute.
     *
     * @return string
     */
    public function getTranslatedTitleAttribute(): string
    {
        // If title is already translated (doesn't contain ::), return as is
        if (strpos($this->title, '::') === false) {
            return $this->title;
        }

        // Try to translate using the key
        $translated = trans($this->title);
        
        // If translation failed (returned the key), return original
        if ($translated === $this->title) {
            return $this->title;
        }

        return $translated;
    }

    /**
     * Get translated message attribute.
     *
     * @return string
     */
    public function getTranslatedMessageAttribute(): string
    {
        // If message is already translated (doesn't contain ::), return as is
        if (strpos($this->message, '::') === false) {
            return $this->message;
        }

        // Try to translate using the key
        $translated = trans($this->message, $this->metadata ?? []);
        
        // If translation failed (returned the key), return original
        if ($translated === $this->message) {
            return $this->message;
        }

        return $translated;
    }

    /**
     * Get the citizen that owns the notification.
     */
    public function citizen()
    {
        return $this->belongsTo(CitizenProxy::modelClass(), 'citizen_id');
    }

    /**
     * Get the service request if entity_type is service_request.
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequestProxy::modelClass(), 'entity_id');
    }

    /**
     * Get the identity verification if entity_type is identity_verification.
     */
    public function identityVerification()
    {
        return $this->belongsTo(IdentityVerificationProxy::modelClass(), 'entity_id');
    }

    /**
     * Get the related entity based on entity_type.
     */
    public function entity()
    {
        if ($this->entity_type === 'service_request') {
            return $this->serviceRequest();
        } elseif ($this->entity_type === 'identity_verification') {
            return $this->identityVerification();
        }

        return null;
    }

    /**
     * Check if notification is read.
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Mark notification as read.
     *
     * @return bool
     */
    public function markAsRead(): bool
    {
        if ($this->read_at === null) {
            $this->read_at = now();
            return $this->save();
        }

        return true;
    }
}

