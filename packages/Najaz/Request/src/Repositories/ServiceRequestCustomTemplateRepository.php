<?php

namespace Najaz\Request\Repositories;

use Najaz\Request\Models\ServiceRequest;
use Najaz\Request\Models\ServiceRequestCustomTemplateProxy;
use Najaz\Service\Services\DocumentTemplateService;
use Webkul\Core\Eloquent\Repository;

class ServiceRequestCustomTemplateRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return ServiceRequestCustomTemplateProxy::modelClass();
    }

    /**
     * Create or update custom template for a service request.
     *
     * @param  int  $serviceRequestId
     * @param  string  $locale
     * @param  array  $data
     * @return \Najaz\Request\Models\ServiceRequestCustomTemplate
     */
    public function createOrUpdate(int $serviceRequestId, string $locale, array $data)
    {
        $customTemplate = $this->model->where('service_request_id', $serviceRequestId)
            ->where('locale', $locale)
            ->first();

        $templateData = [
            'service_request_id' => $serviceRequestId,
            'locale' => $locale,
            'template_content' => $data['template_content'] ?? null,
            'created_by_admin_id' => auth()->guard('admin')->id(),
        ];

        if ($customTemplate) {
            $customTemplate->update($templateData);
            return $customTemplate;
        }

        return $this->create($templateData);
    }

    /**
     * Get custom template by service request and locale.
     *
     * @param  int  $serviceRequestId
     * @param  string|null  $locale
     * @return \Najaz\Request\Models\ServiceRequestCustomTemplate|null
     */
    public function getByRequestAndLocale(int $serviceRequestId, ?string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();

        return $this->model->where('service_request_id', $serviceRequestId)
            ->where('locale', $locale)
            ->first();
    }

    /**
     * Copy template content from the original service template.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $serviceRequest
     * @param  string|null  $locale
     * @return \Najaz\Request\Models\ServiceRequestCustomTemplate
     */
    public function copyFromOriginalTemplate(ServiceRequest $serviceRequest, ?string $locale = null)
    {
        $locale = $locale ?? $serviceRequest->locale ?? app()->getLocale();

        $template = $serviceRequest->service->documentTemplate ?? null;

        if (! $template || ! $template->is_active) {
            throw new \Exception('Original template not found or inactive');
        }

        // Generate document content using DocumentTemplateService
        $documentService = new DocumentTemplateService;
        $fieldValues = $documentService->getFieldValues($serviceRequest);
        $content = $documentService->replacePlaceholders($template->template_content, $fieldValues);

        return $this->createOrUpdate($serviceRequest->id, $locale, [
            'template_content' => $content,
        ]);
    }

    /**
     * Get uploaded files from service request form data.
     *
     * @param  \Najaz\Request\Models\ServiceRequest  $serviceRequest
     * @return array
     */
    public function getUploadedFiles(ServiceRequest $serviceRequest): array
    {
        $files = [];

        // Load form data with service attribute groups to identify file fields
        $serviceRequest->load([
            'formData',
            'service.attributeGroups.fields.attributeType',
        ]);

        // Get pivot relations for custom service fields
        $pivotIds = $serviceRequest->service->attributeGroups->pluck('pivot.id')->filter();
        $pivotRelations = collect();

        if ($pivotIds->isNotEmpty()) {
            $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'fields.attributeType',
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');
        }

        // Iterate through form data to find file fields
        foreach ($serviceRequest->formData as $formData) {
            if (! $formData->fields_data || ! is_array($formData->fields_data)) {
                continue;
            }

            // Get the attribute group
            $group = $serviceRequest->service->attributeGroups->firstWhere('pivot.custom_code', $formData->group_code)
                ?? $serviceRequest->service->attributeGroups->firstWhere('code', $formData->group_code);

            if (! $group) {
                continue;
            }

            $pivotId = $group->pivot->id ?? null;
            $pivotRelation = $pivotId ? $pivotRelations->get($pivotId) : null;

            // Use custom service fields if available, otherwise fall back to template fields
            $fieldsToUse = $pivotRelation && $pivotRelation->fields && $pivotRelation->fields->isNotEmpty()
                ? $pivotRelation->fields
                : ($group->fields ?? collect());

            foreach ($formData->fields_data as $fieldCode => $fieldValue) {
                $field = $fieldsToUse->firstWhere('code', $fieldCode);

                if (! $field || ! $field->attributeType) {
                    continue;
                }

                // Check if field is a file or image type
                $fieldType = $field->attributeType->code ?? '';
                if (in_array($fieldType, ['file', 'image'])) {
                    $files[] = [
                        'field_code' => $fieldCode,
                        'field_label' => $field->translate(app()->getLocale())?->label ?? $fieldCode,
                        'field_type' => $fieldType,
                        'file_path' => $fieldValue,
                        'group_name' => $formData->group_name,
                    ];
                }
            }
        }

        return $files;
    }
}

