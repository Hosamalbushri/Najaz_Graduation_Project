<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'category_id',
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
    protected $with = ['translations', 'category'];

    /**
     * Get the category that the service belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategoryProxy::modelClass(), 'category_id');
    }

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

    /**
     * Scope to load attribute groups with all required relations for edit page.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAttributeGroupsForEdit($query)
    {
        return $query->with([
            'attributeGroups.translations',
            'attributeGroups.fields.translations',
            'attributeGroups.fields.attributeType.translations',
            'attributeGroups.fields.attributeType.options.translations',
        ]);
    }

    /**
     * Get attribute groups formatted for edit page.
     *
     * @param  string|null  $locale
     * @return \Illuminate\Support\Collection
     */
    public function getAttributeGroupsForEdit(?string $locale = null): \Illuminate\Support\Collection
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        // Ensure relations are loaded
        if (! $this->relationLoaded('attributeGroups')) {
            $this->load([
                'attributeGroups.translations',
                'attributeGroups.fields.translations',
                'attributeGroups.fields.attributeType.translations',
                'attributeGroups.fields.attributeType.options.translations',
            ]);
        }

        // Load pivot relations with translations and fields
        $pivotIds = $this->attributeGroups->pluck('pivot.id')->filter();
        if ($pivotIds->isNotEmpty()) {
            \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'translations',
                'fields.translations',
                'fields.attributeType.translations',
                'fields.options.translations',
            ])->whereIn('id', $pivotIds)->get();
        }

        return $this->attributeGroups;
    }

    /**
     * Get formatted attribute groups attribute.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFormattedAttributeGroupsAttribute(): \Illuminate\Support\Collection
    {
        return $this->getAttributeGroupsForEdit();
    }

    /**
     * Get service data prepared for Vue component.
     *
     * @param  string|null  $locale
     * @return array
     */
    public function getDataForVue(?string $locale = null): array
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        $repository = app(\Najaz\Service\Repositories\ServiceRepository::class);
        return $repository->prepareServiceForVue($this, $locale);
    }

    /**
     * Get all available attribute groups for selection.
     *
     * @param  string|null  $locale
     * @return \Illuminate\Support\Collection
     */
    public function getAllAttributeGroups(?string $locale = null): \Illuminate\Support\Collection
    {
        $repository = app(\Najaz\Service\Repositories\ServiceRepository::class);
        return $repository->getAllAttributeGroups($locale);
    }

    /**
     * Get all attribute types for field management.
     *
     * @param  string|null  $locale
     * @return \Illuminate\Support\Collection
     */
    public function getAttributeTypes(?string $locale = null): \Illuminate\Support\Collection
    {
        $repository = app(\Najaz\Service\Repositories\ServiceRepository::class);
        return $repository->getAttributeTypes($locale);
    }

    /**
     * Get validations enum values.
     *
     * @return array
     */
    public static function getValidations(): array
    {
        $repository = app(\Najaz\Service\Repositories\ServiceRepository::class);
        return $repository::getValidations();
    }

    /**
     * Get validation labels.
     *
     * @return array
     */
    public static function getValidationLabels(): array
    {
        $repository = app(\Najaz\Service\Repositories\ServiceRepository::class);
        return $repository::getValidationLabels();
    }

    /**
     * Get file extensions.
     *
     * @return array
     */
    public static function getFileExtensions(): array
    {
        $repository = app(\Najaz\Service\Repositories\ServiceRepository::class);
        return $repository::getFileExtensions();
    }

    /**
     * Get citizen type tree.
     *
     * @return array
     */
    public static function getCitizenTypeTree(): array
    {
        $repository = app(\Najaz\Service\Repositories\ServiceRepository::class);
        return $repository::getCitizenTypeTree();
    }
}
