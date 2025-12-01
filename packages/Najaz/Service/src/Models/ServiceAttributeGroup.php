<?php

namespace Najaz\Service\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Najaz\Service\Contracts\ServiceAttributeGroup as ServiceAttributeGroupContract;
use Webkul\Core\Eloquent\TranslatableModel;

class ServiceAttributeGroup extends TranslatableModel implements ServiceAttributeGroupContract
{
    /**
     * Table associated with the model.
     *
     * @var string
     */
    protected $table = 'service_attribute_groups';

    /**
     * Translation model foreign key column.
     *
     * @var string
     */
    protected $translationForeignKey = 'service_attribute_group_id';

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
        'code',
        'default_name',
        'group_type',
        'sort_order',
    ];

    /**
     * Get the fields for the attribute group.
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ServiceAttributeFieldProxy::modelClass())
            ->orderBy('sort_order');
    }

    /**
     * Get the services that use this attribute group.
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(
            ServiceProxy::modelClass(),
            'service_attribute_group_service',
            'service_attribute_group_id',
            'service_id'
        )->using(ServiceAttributeGroupService::class)
            ->withPivot('id', 'pivot_uid', 'sort_order', 'is_notifiable', 'custom_code')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Convert attribute group to array format for frontend catalog.
     *
     * @param  string|null  $locale
     * @param  \Najaz\Service\Models\ServiceAttributeGroupService|null  $pivotRelation
     * @return array|null
     */
    public function toArrayForCatalog(?string $locale = null, ?\Najaz\Service\Models\ServiceAttributeGroupService $pivotRelation = null): ?array
    {
        if (! $locale) {
            $locale = core()->getRequestedLocaleCode();
        }

        // Get fields to use (saved fields from pivot or template fields)
        $fieldsToUse = $pivotRelation && $pivotRelation->fields->isNotEmpty()
            ? $pivotRelation->fields
            : ($this->fields ?? collect());

        // Filter groups that have fields
        if ($fieldsToUse->isEmpty()) {
            return null;
        }

        $translation = $this->translate($locale);
        $supportsNotification = $this->group_type === 'citizen'
            && $fieldsToUse->contains(
                fn ($field) => strtolower($field->code ?? '') === 'id_number'
            );

        // Get all translations
        $translations = [];
        if ($this->relationLoaded('translations')) {
            foreach ($this->translations as $trans) {
                $translations[$trans->locale] = [
                    'name' => $trans->name ?? '',
                    'description' => $trans->description ?? '',
                ];
            }
        }

        return [
            'id'                    => $this->id,
            'code'                  => $this->code,
            'group_type'            => $this->group_type ?? 'general',
            'name'                  => $translation?->name ?? $this->code,
            'description'           => $translation?->description,
            'translations'          => $translations,
            'sort_order'            => $this->sort_order ?? 0,
            'is_notifiable'         => false,
            'supports_notification' => $supportsNotification,
            'fields'                => $fieldsToUse->map(function ($field) use ($locale) {
                $fieldTranslation = $field->translate($locale);
                $attributeType = $field->attributeType;
                $attributeTypeTranslation = $attributeType?->translate($locale);

                // Get labels for all locales
                $allLocales = core()->getAllLocales();
                $labels = [];
                foreach ($allLocales as $loc) {
                    $trans = $field->translate($loc->code);
                    $labels[$loc->code] = $trans?->label ?? $field->code ?? '';
                }

                // Get options from custom field options first, then fall back to attribute type options
                $options = [];
                
                // First, try custom field options
                if ($field->options && $field->options->isNotEmpty()) {
                    foreach ($field->options as $option) {
                        $optionLabels = [];
                        foreach ($allLocales as $loc) {
                            $optionTranslation = $option->translate($loc->code);
                            $optionLabels[$loc->code] = $optionTranslation?->label ?? $option->admin_name ?? $option->code ?? '';
                        }
                        
                        $options[] = [
                            'id' => $option->id,
                            'code' => $option->code ?? $option->admin_name ?? '',
                            'labels' => $optionLabels,
                        ];
                    }
                } elseif ($attributeType && $attributeType->options && $attributeType->options->isNotEmpty()) {
                    // Fall back to attribute type options
                    foreach ($attributeType->options as $option) {
                        $optionLabels = [];
                        foreach ($allLocales as $loc) {
                            $optionTranslation = $option->translate($loc->code);
                            $optionLabels[$loc->code] = $optionTranslation?->label ?? $option->admin_name ?? $option->code ?? '';
                        }
                        
                        $options[] = [
                            'id' => $option->id,
                            'code' => $option->code,
                            'labels' => $optionLabels,
                        ];
                    }
                }

                return [
                    'id'                  => $field->id,
                    'code'                => $field->code,
                    'label'               => $fieldTranslation?->label ?? $field->code,
                    'labels'              => $labels,
                    'type'                => $field->type,
                    'attribute_type_name' => $attributeTypeTranslation?->name ?? $attributeType?->code ?? '',
                    'sort_order'          => $field->sort_order ?? 0,
                    'options'             => $options,
                ];
            })->values()->toArray(),
        ];
    }
}


