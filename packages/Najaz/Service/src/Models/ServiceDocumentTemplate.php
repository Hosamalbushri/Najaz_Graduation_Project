<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Najaz\Service\Contracts\ServiceDocumentTemplate as ServiceDocumentTemplateContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceDocumentTemplate extends TranslatableModel implements ServiceDocumentTemplateContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_document_templates';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_document_template_id';

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = [
        'template_content',
        'footer_text',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_id',
        'available_fields',
        'used_fields',
        'header_image',
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

    /**
     * Get the translations for this template.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ServiceDocumentTemplateTranslationProxy::modelClass(), 'service_document_template_id');
    }
}

