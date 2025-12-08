<?php

namespace Najaz\Request\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Request\Contracts\ServiceRequestCustomTemplate as ServiceRequestCustomTemplateContract;
use Webkul\User\Models\Admin;

class ServiceRequestCustomTemplate extends Model implements ServiceRequestCustomTemplateContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_request_custom_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_request_id',
        'locale',
        'template_content',
        'additional_data',
        'header_image',
        'footer_text',
        'created_by_admin_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'additional_data' => 'array',
    ];

    /**
     * Get the service request that owns this custom template.
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestProxy::modelClass(), 'service_request_id');
    }

    /**
     * Get the admin who created this custom template.
     */
    public function createdByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by_admin_id');
    }
}

