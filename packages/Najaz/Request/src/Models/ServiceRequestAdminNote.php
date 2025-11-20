<?php

namespace Najaz\Request\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Request\Contracts\ServiceRequestAdminNote as ServiceRequestAdminNoteContract;
use Webkul\User\Models\Admin;

class ServiceRequestAdminNote extends Model implements ServiceRequestAdminNoteContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_request_admin_notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_request_id',
        'note',
        'citizen_notified',
        'admin_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'citizen_notified' => 'boolean',
    ];

    /**
     * Get the service request that owns the admin note.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestProxy::modelClass(), 'service_request_id');
    }

    /**
     * Get the admin who created the note.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}

