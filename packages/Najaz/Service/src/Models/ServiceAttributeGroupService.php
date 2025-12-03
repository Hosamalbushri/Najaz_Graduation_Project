<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Najaz\Service\Contracts\ServiceAttributeGroupService as ServiceAttributeGroupServiceContract;

class ServiceAttributeGroupService extends Pivot implements ServiceAttributeGroupServiceContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_group_service';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'pivot_uid',
        'service_id',
        'service_attribute_group_id',
        'sort_order',
        'is_notifiable',
        'custom_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_notifiable' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the service that owns this pivot.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceProxy::modelClass(), 'service_id');
    }

    /**
     * Get the attribute group that owns this pivot.
     */
    public function attributeGroup(): BelongsTo
    {
        return $this->belongsTo(ServiceAttributeGroupProxy::modelClass(), 'service_attribute_group_id');
    }

    /**
     * Get the fields for this pivot relation.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ServiceAttributeGroupServiceFieldProxy::modelClass(), 'service_attribute_group_service_id')
            ->orderBy('sort_order');
    }

    /**
     * Get the translations for this pivot.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(
            ServiceAttributeGroupServiceTranslationProxy::modelClass(),
            'service_attribute_group_service_id'
        );
    }

    /**
     * Get custom_name attribute with translation fallback.
     *
     * @return string|null
     */
    public function getCustomNameAttribute(): ?string
    {
        return $this->getCustomNameForLocale(core()->getRequestedLocaleCode());
    }

    /**
     * Get custom name for a specific locale.
     *
     * @param  string|null  $locale
     * @return string|null
     */
    public function getCustomNameForLocale(?string $locale = null): ?string
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        // Check if translations are loaded
        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $translation = $this->translations->where('locale', $locale)->first();

        if ($translation && $translation->custom_name) {
            return $translation->custom_name;
        }

        // Fallback to default locale
        $fallbackLocale = config('app.fallback_locale', 'ar');
        $fallbackTranslation = $this->translations->where('locale', $fallbackLocale)->first();

        return $fallbackTranslation?->custom_name ?? null;
    }

    /**
     * Get display name attribute (custom_name or group name).
     *
     * @return string
     */
    public function getDisplayNameAttribute(): string
    {
        $customName = $this->custom_name;

        if ($customName && $customName !== '') {
            return $customName;
        }

        $locale = core()->getRequestedLocaleCode();
        $group = $this->attributeGroup;

        if ($group) {
            $translation = $group->translate($locale);
            return $translation?->name ?? $group->code ?? '';
        }

        return '';
    }

    /**
     * Get custom name translations for all locales.
     *
     * @return array
     */
    public function getCustomNameTranslationsAttribute(): array
    {
        // Check if translations are loaded
        if (! $this->relationLoaded('translations')) {
            $this->load('translations');
        }

        $translations = [];
        foreach (core()->getAllLocales() as $locale) {
            $translation = $this->translations->where('locale', $locale->code)->first();
            $translations[$locale->code] = $translation?->custom_name ?? '';
        }

        return $translations;
    }

    /**
     * Get fields for display with translations.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getFieldsForDisplayAttribute()
    {
        if (! $this->relationLoaded('fields')) {
            $this->load(['fields.translations', 'fields.attributeType.translations', 'fields.options.translations']);
        }

        return $this->fields;
    }

    /**
     * Get fields as array for frontend.
     *
     * @param  string|null  $locale
     * @return array
     */
    public function getFieldsArray(?string $locale = null): array
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        if (! $this->relationLoaded('fields')) {
            $this->load(['fields.translations', 'fields.attributeType.translations', 'fields.options.translations']);
        }

        $fieldRepository = app(\Najaz\Service\Repositories\ServiceAttributeGroupServiceFieldRepository::class);
        
        return $this->fields->map(function ($field) use ($locale, $fieldRepository) {
            // Use Repository method to convert to frontend format (avoid duplication)
            return $fieldRepository->formatFieldForResponse($field, $locale);
        })->sortBy('sort_order')->values()->toArray();
    }

    /**
     * Convert pivot relation to array format for frontend.
     *
     * @param  string|null  $locale
     * @return array
     */
    public function toArrayForFrontend(?string $locale = null): array
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        // Ensure relations are loaded
        if (! $this->relationLoaded('attributeGroup')) {
            $this->load('attributeGroup.translations');
        }

        $group = $this->attributeGroup;
        if (! $group) {
            throw new \Exception('Attribute group not found for pivot relation');
        }

        // Get custom name translations
        $customNameTranslations = $this->custom_name_translations;
        $displayName = $this->display_name;

        // Get fields
        $fields = $this->getFieldsArray($locale);

        // Check if group supports notification
        $supportsNotification = $this->groupSupportsNotification();

        return [
            'service_attribute_group_id' => $this->id,
            'template_id'                => $group->id,
            'pivot_uid'                  => $this->pivot_uid,
            'code'                       => $this->custom_code ?? $group->code,
            'name'                       => $displayName,
            'display_name'               => $displayName,
            'custom_name'                => $customNameTranslations,
            'description'                => $group->translate($locale)?->description ?? '',
            'group_type'                 => $group->group_type ?? 'general',
            'sort_order'                 => $this->sort_order ?? 0,
            'is_notifiable'              => $this->is_notifiable,
            'supports_notification'      => $supportsNotification,
            'fields'                     => $fields,
        ];
    }

    /**
     * Check if group supports notification.
     *
     * @return bool
     */
    protected function groupSupportsNotification(): bool
    {
        $group = $this->attributeGroup;
        if (! $group) {
            return false;
        }

        $type = strtolower($group->group_type ?? 'general');
        if ($type !== 'citizen') {
            return false;
        }

        if (! $this->relationLoaded('fields')) {
            $this->load('fields');
        }

        return $this->fields->contains(function ($field) {
            $code = strtolower($field->code ?? '');
            // Check for exact match or if code contains 'national_id_card'
            return $code === 'national_id_card' || str_contains($code, 'national_id_card');
        });
    }
}

