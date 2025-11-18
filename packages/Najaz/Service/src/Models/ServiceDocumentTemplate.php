<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Najaz\Service\Contracts\ServiceDocumentTemplate as ServiceDocumentTemplateContract;

class ServiceDocumentTemplate extends Model implements ServiceDocumentTemplateContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_document_templates';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_id',
        'template_content',
        'available_fields',
        'used_fields',
        'header_image',
        'footer_text',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'available_fields' => 'array',
        'used_fields'      => 'array',
        'is_active'        => 'boolean',
    ];

    /**
     * Get the service that owns this template.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceProxy::modelClass(), 'service_id');
    }
}

