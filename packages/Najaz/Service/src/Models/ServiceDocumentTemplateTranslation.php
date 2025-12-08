<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceDocumentTemplateTranslation as ServiceDocumentTemplateTranslationContract;

class ServiceDocumentTemplateTranslation extends Model implements ServiceDocumentTemplateTranslationContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_document_template_translations';

    /**
     * Set timestamp false.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Set fillable property to the model.
     *
     * @var array
     */
    protected $fillable = [
        'template_content',
        'footer_text',
        'locale',
        'service_document_template_id',
    ];
}

