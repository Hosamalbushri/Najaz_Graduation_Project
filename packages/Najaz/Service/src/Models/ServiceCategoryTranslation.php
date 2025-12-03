<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Model;
use Najaz\Service\Contracts\ServiceCategoryTranslation as ServiceCategoryTranslationContract;

class ServiceCategoryTranslation extends Model implements ServiceCategoryTranslationContract
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_category_translations';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'slug',
        'url_path',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'locale_id',
        'locale',
    ];
}

