<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Kalnoy\Nestedset\NodeTrait;
use Najaz\Service\Contracts\ServiceCategory as ServiceCategoryContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceCategory extends TranslatableModel implements ServiceCategoryContract
{
    use HasFactory, NodeTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_categories';

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
        'description',
        'slug',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * Fillable.
     *
     * @var array
     */
    protected $fillable = [
        'position',
        'status',
        'display_mode',
        'parent_id',
        'additional',
    ];

    /**
     * Eager loading.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * Appends.
     *
     * @var array
     */
    protected $appends = ['logo_url', 'banner_url', 'url'];

    /**
     * The services that belong to the category.
     */
    public function services(): HasMany
    {
        return $this->hasMany(ServiceProxy::modelClass(), 'category_id');
    }

    /**
     * Get url attribute.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        if ($categoryTranslation = $this->translate(core()->getCurrentLocale()->code)) {
            return url($categoryTranslation->slug);
        }

        return url($this->translate(core()->getDefaultLocaleCodeFromDefaultChannel())?->slug);
    }

    /**
     * Get image url for the category image.
     *
     * @return string
     */
    public function getLogoUrlAttribute()
    {
        if (! $this->logo_path) {
            return;
        }

        return Storage::url($this->logo_path);
    }

    /**
     * Get banner url attribute.
     *
     * @return string
     */
    public function getBannerUrlAttribute()
    {
        if (! $this->banner_path) {
            return;
        }

        return Storage::url($this->banner_path);
    }

    /**
     * Use fallback for category.
     */
    protected function useFallback(): bool
    {
        return true;
    }

    /**
     * Get fallback locale for category.
     */
    protected function getFallbackLocale(?string $locale = null): ?string
    {
        if ($fallback = core()->getDefaultLocaleCodeFromDefaultChannel()) {
            return $fallback;
        }

        return parent::getFallbackLocale();
    }
}

