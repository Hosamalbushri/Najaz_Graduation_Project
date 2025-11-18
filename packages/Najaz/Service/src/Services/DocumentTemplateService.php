<?php

namespace Najaz\Service\Services;

use Carbon\Carbon;
use Najaz\Request\Models\ServiceRequest;
use Najaz\Request\Models\ServiceRequestFormData;
use Najaz\Service\Models\ServiceDocumentTemplate;
use Webkul\Core\Traits\PDFHandler;

class DocumentTemplateService
{
    use PDFHandler;

    /**
     * Generate document from template for a service request.
     *
     * @param  ServiceRequest  $serviceRequest
     * @return string HTML content
     */
    public function generateDocument(ServiceRequest $serviceRequest): string
    {
        $service = $serviceRequest->service;
        $template = $service->documentTemplate;

        if (! $template || ! $template->is_active) {
            throw new \Exception('Template not found or inactive');
        }

        // Get all field values
        $fieldValues = $this->getFieldValues($serviceRequest);

        // Debug: Log field values
        \Log::info('DocumentTemplateService - Field Values:', [
            'field_values' => $fieldValues,
            'template_content' => $template->template_content,
        ]);

        // Replace placeholders in template
        $content = $this->replacePlaceholders($template->template_content, $fieldValues);

        \Log::info('DocumentTemplateService - After Replacement:', [
            'content' => $content,
        ]);

        // Build full HTML document
        return $this->buildHtmlDocument($content, $template);
    }

    /**
     * Generate and download PDF document.
     *
     * @param  ServiceRequest  $serviceRequest
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function generateAndDownloadPDF(ServiceRequest $serviceRequest)
    {
        $html = $this->generateDocument($serviceRequest);
        $fileName = 'document-' . $serviceRequest->increment_id . '-' . now()->format('Y-m-d');

        return $this->downloadPDF($html, $fileName);
    }

    /**
     * Get all field values from service request.
     *
     * @param  ServiceRequest  $serviceRequest
     * @return array
     */
    protected function getFieldValues(ServiceRequest $serviceRequest): array
    {
        $values = [];

        // Citizen basic fields
        $values['citizen_first_name'] = $serviceRequest->citizen_first_name ?? '';
        $values['citizen_middle_name'] = $serviceRequest->citizen_middle_name ?? '';
        $values['citizen_last_name'] = $serviceRequest->citizen_last_name ?? '';
        $values['citizen_national_id'] = $serviceRequest->citizen_national_id ?? '';
        $values['citizen_type_name'] = $serviceRequest->citizen_type_name ?? '';

        // Request fields
        $values['request_increment_id'] = $serviceRequest->increment_id ?? '';
        $values['request_date'] = $serviceRequest->submitted_at 
            ? Carbon::parse($serviceRequest->submitted_at)->format('Y-m-d')
            : '';
        $values['current_date'] = Carbon::now()->format('Y-m-d');

        // Get form data from service_request_form_data table
        $formDataRecords = ServiceRequestFormData::where('service_request_id', $serviceRequest->id)
            ->orderBy('sort_order')
            ->get();

        \Log::info('DocumentTemplateService - Form Data Records:', [
            'count' => $formDataRecords->count(),
            'records' => $formDataRecords->map(function ($record) {
                return [
                    'id' => $record->id,
                    'group_code' => $record->group_code,
                    'group_name' => $record->group_name,
                    'fields_data' => $record->fields_data,
                    'fields_data_type' => gettype($record->fields_data),
                ];
            })->toArray(),
        ]);

        $formData = [];
        foreach ($formDataRecords as $record) {
            $groupCode = $record->group_code;
            $fieldsData = $record->fields_data;

            // Handle different data types
            if (is_string($fieldsData)) {
                $fieldsData = json_decode($fieldsData, true) ?? [];
            }

            if (is_array($fieldsData) && ! empty($fieldsData)) {
                $formData[$groupCode] = $fieldsData;
            }
        }

        \Log::info('DocumentTemplateService - Processed Form Data:', [
            'form_data' => $formData,
        ]);

        // Extract field values from form data
        foreach ($formData as $groupCode => $groupFields) {
            if (is_array($groupFields)) {
                foreach ($groupFields as $fieldCode => $fieldValue) {
                    // Convert value to string if needed
                    $stringValue = is_null($fieldValue) ? '' : (string) $fieldValue;
                    
                    // Add nested fields (group.field) - this is important for {{group.field}} placeholders
                    $nestedKey = $groupCode . '.' . $fieldCode;
                    $values[$nestedKey] = $stringValue;

                    // Add flat fields (only if not already set)
                    if (! isset($values[$fieldCode])) {
                        $values[$fieldCode] = $stringValue;
                    }
                }
            }
        }

        \Log::info('DocumentTemplateService - Final Values:', [
            'values' => $values,
        ]);

        return $values;
    }

    /**
     * Replace placeholders in template content.
     *
     * @param  string  $content
     * @param  array  $fieldValues
     * @return string
     */
    protected function replacePlaceholders(string $content, array $fieldValues): string
    {
        \Log::info('DocumentTemplateService - Replace Placeholders Start:', [
            'content_length' => strlen($content),
            'field_values_count' => count($fieldValues),
            'field_values_keys' => array_keys($fieldValues),
        ]);

        // Find all placeholders in the content first
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        
        \Log::info('DocumentTemplateService - Found Placeholders:', [
            'matches' => $matches[1] ?? [],
        ]);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $fieldCode) {
                $fieldCode = trim($fieldCode);
                $placeholder = '{{' . $fieldCode . '}}';
                
                // Check if we have this field in our values
                if (isset($fieldValues[$fieldCode])) {
                    $stringValue = is_null($fieldValues[$fieldCode]) ? '' : (string) $fieldValues[$fieldCode];
                    // Use str_replace (not str_ireplace) for exact match
                    $content = str_replace($placeholder, $stringValue, $content);
                    \Log::info('DocumentTemplateService - Replaced:', [
                        'placeholder' => $placeholder,
                        'value' => $stringValue,
                    ]);
                } else {
                    // If field not found, replace with empty string
                    $content = str_replace($placeholder, '', $content);
                    \Log::warning('DocumentTemplateService - Placeholder not found:', [
                        'placeholder' => $placeholder,
                        'field_code' => $fieldCode,
                    ]);
                }
            }
        }

        // Also replace all known placeholders as a fallback
        foreach ($fieldValues as $fieldCode => $value) {
            $stringValue = is_null($value) ? '' : (string) $value;
            $placeholder = '{{' . $fieldCode . '}}';
            if (strpos($content, $placeholder) !== false) {
                $content = str_replace($placeholder, $stringValue, $content);
            }
        }

        // Remove any remaining placeholders that weren't matched
        $content = preg_replace('/\{\{[^}]+\}\}/', '', $content);

        \Log::info('DocumentTemplateService - Replace Placeholders End:', [
            'content_length' => strlen($content),
        ]);

        return $content;
    }

    /**
     * Build full HTML document with header and footer.
     *
     * @param  string  $content
     * @param  ServiceDocumentTemplate  $template
     * @return string
     */
    protected function buildHtmlDocument(string $content, ServiceDocumentTemplate $template): string
    {
        $locale = app()->getLocale();
        $direction = core()->getCurrentLocale()->direction ?? 'ltr';

        $headerImage = $template->header_image 
            ? '<img src="' . asset($template->header_image) . '" style="max-width: 200px; margin-bottom: 20px;" />'
            : '';

        $footerText = $template->footer_text 
            ? '<div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #666;">' . e($template->footer_text) . '</div>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="{$locale}" dir="{$direction}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            direction: {$direction};
            padding: 40px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .content {
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        {$headerImage}
    </div>
    <div class="content">
        {$content}
    </div>
    {$footerText}
</body>
</html>
HTML;
    }
}

