<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Najaz\Citizen\Models\CitizenTypeProxy;
use Najaz\Service\Contracts\Service as ServiceContract;
use Webkul\Core\Eloquent\TranslatableModel;

class Service extends TranslatableModel implements ServiceContract
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'services';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_id';

    /**
     * Translated attributes.
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
        'description',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'image',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status'     => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Eager loading.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * Get the attribute groups assigned to the service.
     */
    public function attributeGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceAttributeGroupProxy::modelClass(),
            'service_attribute_group_service',
            'service_id',
            'service_attribute_group_id'
        )->using(ServiceAttributeGroupService::class)
            ->withPivot('id', 'pivot_uid', 'sort_order', 'is_notifiable', 'custom_code')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Get the citizen types that can access the service.
     */
    public function citizenTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            CitizenTypeProxy::modelClass(),
            'citizen_type_service',
            'service_id',
            'citizen_type_id'
        )->withTimestamps();
    }

    /**
     * Get the document template for this service.
     */
    public function documentTemplate(): HasOne
    {
        return $this->hasOne(ServiceDocumentTemplateProxy::modelClass(), 'service_id');
    }

    /**
     * Scope to load translations.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithTranslations($query)
    {
        return $query->with('translations');
    }

    /**
     * Use fallback for service.
     */
    protected function useFallback(): bool
    {
        return true;
    }

    /**
     * Get fallback locale for service.
     */
    protected function getFallbackLocale(?string $locale = null): ?string
    {
        $fallbackLocale = config('app.fallback_locale', 'ar');
        
        if ($fallbackLocale) {
            return $fallbackLocale;
        }

        return parent::getFallbackLocale();
    }
}
