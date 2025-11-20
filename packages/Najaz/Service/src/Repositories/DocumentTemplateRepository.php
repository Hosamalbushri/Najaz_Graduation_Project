<?php

namespace Najaz\Service\Repositories;

use Illuminate\Container\Container;
use Najaz\Service\Models\Service;
use Najaz\Service\Models\ServiceDocumentTemplateProxy;
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
            'template_content'  => '',
            'used_fields'       => [],
            'header_image'      => null,
            'footer_text'       => null,
            'is_active'         => true,
        ]);

        // Build available fields list
        $currentLocale = app()->getLocale();
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
        $template->update($data);

        // Update available fields list
        $service = $template->service;
        $currentLocale = app()->getLocale();
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);
        $template->available_fields = $availableFields;
        $template->save();

        return $template;
    }

    /**
     * Build available fields list for document template.
     */
    public function buildAvailableFieldsForTemplate(Service $service, string $locale): array
    {
        $fields = [];

        // Citizen fields
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.citizen'),
            'code'  => 'citizen_first_name',
            'label' => trans('Admin::app.services.document-templates.edit.fields.citizen_first_name'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.citizen'),
            'code'  => 'citizen_middle_name',
            'label' => trans('Admin::app.services.document-templates.edit.fields.citizen_middle_name'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.citizen'),
            'code'  => 'citizen_last_name',
            'label' => trans('Admin::app.services.document-templates.edit.fields.citizen_last_name'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.citizen'),
            'code'  => 'citizen_national_id',
            'label' => trans('Admin::app.services.document-templates.edit.fields.citizen_national_id'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.edit.fields.citizen'),
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

        // Service attribute group fields
        $service->load('attributeGroups.fields');
        foreach ($service->attributeGroups as $group) {
            $groupCode = $group->pivot->custom_code ?? $group->code;
            $groupTranslation = $group->translate($locale);
            $groupName = $group->pivot->custom_name ?? ($groupTranslation?->name ?? $group->code ?? $groupCode);

            foreach ($group->fields as $field) {
                $fieldCode = $field->code;
                $fieldTranslation = $field->translate($locale);
                $fieldLabel = $fieldTranslation?->label ?? $fieldCode;

                // Add nested field (group.field)
                $fields[] = [
                    'group' => $groupName,
                    'code'  => $groupCode.'.'.$fieldCode,
                    'label' => $groupName.' - '.$fieldLabel,
                ];
            }
        }

        return $fields;
    }
}

