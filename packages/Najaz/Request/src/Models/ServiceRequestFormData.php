<?php

namespace Najaz\Request\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Request\Contracts\ServiceRequest as ServiceRequestContract;

class ServiceRequestFormData extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_request_form_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_request_id',
        'group_code',
        'group_name',
        'fields_data',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fields_data' => 'array',
    ];

    /**
     * Get the service request that owns this form data.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestProxy::modelClass(), 'service_request_id');
    }
}

