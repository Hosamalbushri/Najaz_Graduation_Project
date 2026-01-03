<?php

namespace Najaz\Service\Repositories;

use Illuminate\Container\Container;
use Najaz\Service\Models\Service;
use Najaz\Service\Models\ServiceDocumentTemplateProxy;
use Najaz\Service\Models\ServiceDocumentTemplateTranslationProxy;
use Webkul\Core\Eloquent\Repository;

class DocumentTemplateRepository extends Repository
{
    /**
     * Create a new repository instance.
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
        Container $container
    ) {
        parent::__construct($container);
    }

    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Najaz\Service\Contracts\ServiceDocumentTemplate';
    }

    /**
     * Get services that don't have templates.
     */
    public function getServicesWithoutTemplates($allServices): \Illuminate\Support\Collection
    {
        // Get service IDs that already have templates
        $servicesWithTemplates = ServiceDocumentTemplateProxy::modelClass()::query()
            ->pluck('service_id')
            ->toArray();

        // Filter services that don't have templates
        return $allServices->reject(function ($service) use ($servicesWithTemplates) {
            return in_array($service->id, $servicesWithTemplates);
        })->map(function ($service) {
            return [
                'id'   => $service->id,
                'name' => $service->name,
            ];
        })->values();
    }

    /**
     * Check if service already has a template.
     */
    public function serviceHasTemplate(int $serviceId): bool
    {
        return ServiceDocumentTemplateProxy::modelClass()::query()
            ->where('service_id', $serviceId)
            ->exists();
    }

    /**
     * Get template by service ID.
     */
    public function getTemplateByServiceId(int $serviceId)
    {
        return ServiceDocumentTemplateProxy::modelClass()::query()
            ->where('service_id', $serviceId)
            ->first();
    }

    /**
     * Create a new document template for a service.
     */
    public function createTemplate(int $serviceId): \Najaz\Service\Models\ServiceDocumentTemplate
    {
        $service = $this->serviceRepository->findOrFail($serviceId);

        // Create empty template
        $template = ServiceDocumentTemplateProxy::modelClass()::create([
            'service_id'        => $serviceId,
            'used_fields'       => [],
            'is_active'         => true,
            'enable_custom_template' => false,
        ]);

        // Create default translation for current locale
        $currentLocale = app()->getLocale();
        ServiceDocumentTemplateTranslationProxy::modelClass()::create([
            'service_document_template_id' => $template->id,
            'locale'                       => $currentLocale,
            'template_content'            => '',
        ]);

        // Build available fields list
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);
        $template->available_fields = $availableFields;
        $template->save();

        return $template;
    }

    /**
     * Update template and refresh available fields.
     */
    public function updateTemplateWithAvailableFields(int $templateId, array $data): \Najaz\Service\Models\ServiceDocumentTemplate
    {
        $template = $this->findOrFail($templateId);
        
        // Extract translation data
        $locale = $data['locale'] ?? app()->getLocale();
        $templateContent = $data['template_content'] ?? '';
        
        // Remove translation fields from main data
        unset($data['locale'], $data['template_content']);
        
        // Update main template data
        $template->update($data);

        // Update or create translation
        $translation = ServiceDocumentTemplateTranslationProxy::modelClass()::updateOrCreate(
            [
                'service_document_template_id' => $templateId,
                'locale'                        => $locale,
            ],
            [
                'template_content' => $templateContent,
            ]
        );

        // Update available fields list
        $service = $template->service;
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $locale);
        $template->available_fields = $availableFields;
        $template->save();

        return $template;
    }

    /**
     * Build available fields list for document template.
     */
    public function buildAvailableFieldsForTemplate(Service $service, string $locale): array
    {
        // Set locale temporarily for translations
        $originalLocale = app()->getLocale();
        app()->setLocale($locale);
        
        $fields = [];

        // Applicant fields (مقدم الطلب)
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.applicant'),
            'code'  => 'citizen_full_name',
            'label' => trans('Admin::app.services.document-templates.edit.fields.citizen_full_name'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.applicant'),
            'code'  => 'citizen_national_id',
            'label' => trans('Admin::app.services.document-templates.edit.fields.citizen_national_id'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.applicant'),
            'code'  => 'citizen_type_name',
            'label' => trans('Admin::app.services.document-templates.edit.fields.citizen_type_name'),
        ];

        // Request fields
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.request'),
            'code'  => 'request_increment_id',
            'label' => trans('Admin::app.services.document-templates.edit.fields.request_increment_id'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.request'),
            'code'  => 'request_date',
            'label' => trans('Admin::app.services.document-templates.edit.fields.request_date'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.request'),
            'code'  => 'current_date',
            'label' => trans('Admin::app.services.document-templates.edit.fields.current_date'),
        ];

        // Service attribute group fields - use custom service fields (service_attribute_group_service_fields)
        // Ensure attribute groups are loaded with translations
        if (! $service->relationLoaded('attributeGroups')) {
            $service->load('attributeGroups.translations');
        } else {
            // If already loaded, ensure translations are loaded
            foreach ($service->attributeGroups as $group) {
                if (! $group->relationLoaded('translations')) {
                    $group->load('translations');
                }
            }
        }
        
        // Load custom service fields from ServiceAttributeGroupService
        $pivotIds = $service->attributeGroups->pluck('pivot.id')->filter();
        $pivotRelations = collect();
        if ($pivotIds->isNotEmpty()) {
            // Check if pivot relations are already loaded with fields
            $firstPivot = $service->attributeGroups->first()?->pivot;
            $needsLoading = ! $firstPivot || ! $firstPivot->relationLoaded('fields');

            if ($needsLoading) {
            $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'translations', // Load pivot translations for custom_name
                'attributeGroup.translations',
                'fields.translations', // Load custom service fields with translations
                'fields.attributeType', // Load attribute type
                'fields.attributeType.translations',
                'fields.options.translations', // Load field options translations as well
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');

                // Attach loaded pivot relations to groups
                foreach ($service->attributeGroups as $group) {
                    $pivotId = $group->pivot->id ?? null;
                    if ($pivotId && isset($pivotRelations[$pivotId])) {
                        $group->setRelation('pivot', $pivotRelations[$pivotId]);
                    }
                }
            } else {
                // Use already loaded pivot relations
                foreach ($service->attributeGroups as $group) {
                    $pivotId = $group->pivot->id ?? null;
                    if ($pivotId) {
                        $pivotRelations[$pivotId] = $group->pivot;
                    }
                }
            }
        }

        foreach ($service->attributeGroups as $group) {
            $pivotId = $group->pivot->id ?? null;
            $pivotRelation = $pivotId ? ($pivotRelations[$pivotId] ?? null) : null;
            
            // Skip if no pivot relation (should not happen, but safety check)
            if (! $pivotRelation) {
                continue;
            }

            // Get group code from pivot relation
            $groupCode = $pivotRelation->custom_code ?? $group->code;
            
            // Get custom name from pivot relation translations (same as ServiceFormQuery)
            $groupName = null;
            if ($pivotRelation->relationLoaded('translations')) {
                // Try current locale first
                $translation = $pivotRelation->translations->where('locale', $locale)->first();
                $groupName = $translation?->custom_name;
                
                // Fallback to default locale if not found
                if (empty($groupName)) {
                    $fallbackLocale = config('app.fallback_locale', 'ar');
                    $fallbackTranslation = $pivotRelation->translations->where('locale', $fallbackLocale)->first();
                    $groupName = $fallbackTranslation?->custom_name;
                }
                
                // Last resort: get any available translation
                if (empty($groupName) && $pivotRelation->translations->isNotEmpty()) {
                    $groupName = $pivotRelation->translations->first()->custom_name;
                }
            }
            
            // Fallback to group translation if custom name not found
            if (empty($groupName)) {
                $groupTranslation = $group->translate($locale);
                $groupName = $groupTranslation?->name ?? $group->name ?? $group->code ?? $groupCode ?? 'Other';
            }

            // Get custom service fields from pivot relation only (don't use template fields)
            $fieldsToUse = $pivotRelation->fields ?? collect();

            // Ensure fields have translations loaded
            if ($fieldsToUse->isNotEmpty()) {
                foreach ($fieldsToUse as $field) {
                    if (! $field->relationLoaded('translations')) {
                        $field->load('translations');
                    }
                    if (! $field->relationLoaded('attributeType')) {
                        $field->load('attributeType.translations');
                    }
                }
            }

            // Only add fields if pivot relation has fields (skip template fields)
            foreach ($fieldsToUse as $field) {
                // Skip if field code is empty
                if (empty($field->code)) {
                    continue;
                }
                
                // Get field type - prefer direct 'type' attribute, fallback to attributeType->code
                $fieldType = null;
                
                // First try direct 'type' attribute (for ServiceAttributeGroupServiceField)
                if (isset($field->type) && !empty($field->type)) {
                    $fieldType = $field->type;
                } else {
                    // Fallback to attributeType relationship
                    if (! $field->relationLoaded('attributeType')) {
                        $field->load('attributeType');
                    }
                    
                    if ($field->attributeType) {
                        $fieldType = $field->attributeType->code;
                    }
                }
                
                // Skip file and image fields
                if ($fieldType && in_array($fieldType, ['file', 'image'])) {
                    continue;
                }
                
                $fieldCode = $field->code;
                $fieldTranslation = $field->translate($locale);
                
                // Ensure field label is never empty - use fallback chain
                // Try translation first, then field label, then code, then default
                $fieldLabel = $fieldTranslation?->label 
                    ?? ($field->label ?? null)
                    ?? $fieldCode
                    ?? 'Field';
                
                // Always add the field regardless of translation status
                $fields[] = [
                    'group' => $groupName,
                    'code'  => $fieldCode,
                    'label' => $fieldLabel,
                ];
            }
        }

        // Restore original locale
        app()->setLocale($originalLocale);

        return $fields;
    }
}

