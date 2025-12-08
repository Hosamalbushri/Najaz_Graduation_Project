<?php

namespace Najaz\Service\Services;

use Carbon\Carbon;
use Najaz\Request\Models\ServiceRequest;
use Najaz\Request\Models\ServiceRequestFormData;
use Najaz\Service\Models\ServiceAttributeGroupService;
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

        // Get request locale (fallback to app locale)
        $requestLocale = $serviceRequest->locale ?? app()->getLocale();
        
        // Get template content for request locale
        $templateTranslation = $template->translate($requestLocale);
        $templateContent = $templateTranslation?->template_content ?? $template->template_content;

        // Get all field values
        $fieldValues = $this->getFieldValues($serviceRequest);

        // Debug: Log field values
        \Log::info('DocumentTemplateService - Field Values:', [
            'field_values' => $fieldValues,
            'template_content' => $templateContent,
            'request_locale' => $requestLocale,
        ]);

        // Replace placeholders in template
        $content = $this->replacePlaceholders($templateContent, $fieldValues);

        // Merge custom template content if available
        $content = $this->mergeCustomContent($serviceRequest, $content);

        \Log::info('DocumentTemplateService - After Replacement:', [
            'content' => $content,
        ]);

        // Build full HTML document with request locale
        return $this->buildHtmlDocument($content, $template, $requestLocale);
    }

    /**
     * Merge custom template content with the original template content.
     *
     * @param  ServiceRequest  $serviceRequest
     * @param  string  $originalContent
     * @return string
     */
    public function mergeCustomContent(ServiceRequest $serviceRequest, string $originalContent): string
    {
        // Load custom template for current locale
        $serviceRequest->load('customTemplate');
        $customTemplate = $serviceRequest->customTemplate;

        if (! $customTemplate || empty($customTemplate->template_content)) {
            return $originalContent;
        }

        // Simply append custom content to original content
        return $originalContent . $customTemplate->template_content;
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
     * Uses new field structure from service_attribute_group_service_fields.
     *
     * @param  ServiceRequest  $serviceRequest
     * @return array
     */
    public function getFieldValues(ServiceRequest $serviceRequest): array
    {
        $values = [];

        // Applicant basic fields
        $values['citizen_full_name'] = trim(
            ($serviceRequest->citizen_first_name ?? '') . ' ' .
            ($serviceRequest->citizen_middle_name ?? '') . ' ' .
            ($serviceRequest->citizen_last_name ?? '')
        );
        // Keep old fields for backward compatibility
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

        // Load service attribute groups with fields and options (new structure)
        $service = $serviceRequest->service;
        $pivotRelations = ServiceAttributeGroupService::with([
            'attributeGroup.translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.options.translations', // Load custom field options
        ])
        ->where('service_id', $service->id)
        ->get();

        // Create a map of group codes to pivot relations for quick lookup
        $groupCodeMap = [];
        foreach ($pivotRelations as $pivot) {
            $groupCode = $pivot->custom_code ?? $pivot->attributeGroup->code;
            $groupCodeMap[$groupCode] = $pivot;
        }

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
        // Field codes in fields_data already match the new field structure (field->code)
        foreach ($formData as $groupCode => $groupFields) {
            if (is_array($groupFields)) {
                // Get the pivot relation for this group to access field information
                $pivotRelation = $groupCodeMap[$groupCode] ?? null;
                
                foreach ($groupFields as $fieldCode => $fieldValue) {
                    // Convert value to string if needed
                    $stringValue = is_null($fieldValue) ? '' : (string) $fieldValue;
                    
                    // Verify field code exists in new structure (optional validation)
                    if ($pivotRelation) {
                        $field = $pivotRelation->fields->firstWhere('code', $fieldCode);
                        if ($field) {
                            // Field exists in new structure, use it
                            // The value is already converted to label in saveFormData
                        }
                    }
                    
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
     * Supports both old format {{field_code}} and new format <span data-template-field data-field="field_code">
     *
     * @param  string  $content
     * @param  array  $fieldValues
     * @return string
     */
    public function replacePlaceholders(string $content, array $fieldValues): string
    {
        \Log::info('DocumentTemplateService - Replace Placeholders Start:', [
            'content_length' => strlen($content),
            'field_values_count' => count($fieldValues),
            'field_values_keys' => array_keys($fieldValues),
        ]);

        // First, replace <code data-field="field_code"> tags with field values
        // This handles the new HTML format with code tags
        $content = $this->replaceCodeTags($content, $fieldValues);

        // Then, handle legacy {{field_code}} format for backward compatibility
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        
        \Log::info('DocumentTemplateService - Found Legacy Placeholders:', [
            'matches' => $matches[1] ?? [],
        ]);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $fieldCode) {
                $fieldCode = trim($fieldCode);
                $placeholder = '{{' . $fieldCode . '}}';
                
                // Check if we have this field in our values
                if (isset($fieldValues[$fieldCode])) {
                    $stringValue = is_null($fieldValues[$fieldCode]) ? '' : (string) $fieldValues[$fieldCode];
                    $content = str_replace($placeholder, $stringValue, $content);
                    \Log::info('DocumentTemplateService - Replaced Legacy Placeholder:', [
                        'placeholder' => $placeholder,
                        'value' => $stringValue,
                    ]);
                } else {
                    // If field not found, replace with empty string
                    $content = str_replace($placeholder, '', $content);
                    \Log::warning('DocumentTemplateService - Legacy Placeholder not found:', [
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
     * Replace <span data-template-field="true" data-field="field_code"> tags (and legacy tags) with field values.
     *
     * @param  string  $content
     * @param  array  $fieldValues
     * @return string
     */
    /**
     * Replace field placeholder tags with actual field values.
     * Uses DOMDocument for proper HTML parsing and manipulation.
     *
     * @param  string  $content
     * @param  array  $fieldValues
     * @return string
     */
    protected function replaceCodeTags(string $content, array $fieldValues): string
    {
        if (empty($content) || empty($fieldValues)) {
            return $content;
        }

        // Helper function to get field value safely
        $getFieldValue = function ($fieldCode) use ($fieldValues) {
            if (!isset($fieldValues[$fieldCode])) {
                return '';
            }
            $value = $fieldValues[$fieldCode];
            return is_null($value) ? '' : (string) $value;
        };

        // Helper function to escape HTML
        $escapeHtml = function ($value) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        };

        // Try using DOMDocument for proper HTML parsing
        try {
            // Suppress warnings for malformed HTML
            libxml_use_internal_errors(true);
            
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->encoding = 'UTF-8';
            
            // Wrap content in a container div to handle partial HTML
            $wrappedContent = '<div>' . $content . '</div>';
            
            // Load HTML with UTF-8 encoding
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $wrappedContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            
            // Clear libxml errors
            libxml_clear_errors();
            
            $xpath = new \DOMXPath($dom);
            $replaced = false;
            
            // Replace <span data-template-field data-field="..."> tags
            $spanNodes = $xpath->query('//span[@data-template-field and @data-field]');
            foreach ($spanNodes as $node) {
                $fieldCode = trim($node->getAttribute('data-field'));
                if ($fieldCode) {
                    $fieldValue = $escapeHtml($getFieldValue($fieldCode));
                    $textNode = $dom->createTextNode($fieldValue);
                    $node->parentNode->replaceChild($textNode, $node);
                    $replaced = true;
                }
            }
            
            if ($replaced) {
                // Get the inner HTML of the wrapper div
                $body = $dom->getElementsByTagName('body')->item(0);
                if ($body) {
                    $div = $body->getElementsByTagName('div')->item(0);
                    if ($div) {
                        $result = '';
                        foreach ($div->childNodes as $child) {
                            $result .= $dom->saveHTML($child);
                        }
                        return $result;
                    }
                }
            }
            
        } catch (\Exception $e) {
            // Fallback to regex if DOMDocument fails
            \Log::warning('DocumentTemplateService - DOMDocument parsing failed, using regex fallback', [
                'error' => $e->getMessage(),
            ]);
        }
        
        // Fallback to regex for partial HTML or when DOMDocument fails
        return $this->replaceCodeTagsWithRegex($content, $fieldValues);
    }
    
    /**
     * Fallback method using regex for partial HTML fragments.
     *
     * @param  string  $content
     * @param  array  $fieldValues
     * @return string
     */
    protected function replaceCodeTagsWithRegex(string $content, array $fieldValues): string
    {
        $getFieldValue = function ($fieldCode) use ($fieldValues) {
            if (!isset($fieldValues[$fieldCode])) {
                return '';
            }
            $value = $fieldValues[$fieldCode];
            return is_null($value) ? '' : (string) $value;
        };
        
        $escapeHtml = function ($value) {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        };
        
        // Patterns for <span data-template-field data-field="...">
        $patterns = [
            // data-template-field first, then data-field
            '/<span[^>]*\s+data-template-field(?:=["\']true["\'])?[^>]*\s+data-field=["\']([^"\']+)["\'][^>]*>(.*?)<\/span>/is',
            // data-field first, then data-template-field
            '/<span[^>]*\s+data-field=["\']([^"\']+)["\'][^>]*\s+data-template-field(?:=["\']true["\'])?[^>]*>(.*?)<\/span>/is',
        ];
        
        $result = $content;
        foreach ($patterns as $pattern) {
            $result = preg_replace_callback($pattern, function ($matches) use ($getFieldValue, $escapeHtml) {
                $fieldCode = trim($matches[1]);
                if (!$fieldCode) {
                    return $matches[0]; // Return original if no field code
                }
                return $escapeHtml($getFieldValue($fieldCode));
            }, $result);
        }
        
        return $result;
    }

    /**
     * Build full HTML document with header and footer.
     *
     * @param  string  $content
     * @param  ServiceDocumentTemplate  $template
     * @param  string|null  $locale
     * @return string
     */
    protected function buildHtmlDocument(string $content, ServiceDocumentTemplate $template, ?string $locale = null): string
    {
        // Use provided locale or fallback to app locale
        $locale = $locale ?? app()->getLocale();
        
        // Get locale direction
        $localeModel = core()->getAllLocales()->firstWhere('code', $locale);
        $direction = $localeModel->direction ?? (in_array($locale, ['ar', 'he', 'fa']) ? 'rtl' : 'ltr');

        // Get channel code for config data
        $channelCode = core()->getRequestedChannelCode();

        // Get header settings from system config
        $headerLeft = core()->getConfigData('documents.official.header.header_left', $channelCode, $locale) ?? '';
        $headerCenter = core()->getConfigData('documents.official.header.header_center', $channelCode, $locale) ?? '';
        $headerRight = core()->getConfigData('documents.official.header.header_right', $channelCode, $locale) ?? '';

        // Build header HTML
        $headerHtml = $this->buildHeaderHtml($headerLeft, $headerCenter, $headerRight, $template, $direction, $locale);

        // Get footer text from system config (fallback to template footer_text)
        $footerText = core()->getConfigData('documents.official.footer.footer_text', $channelCode, $locale);
        
        // Fallback to template footer_text if system config is empty
        if (empty($footerText)) {
            $templateTranslation = $template->translate($locale);
            $footerText = $templateTranslation?->footer_text ?? $template->footer_text ?? '';
        }

        $footerHtml = $footerText 
            ? '<div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #666;">' . $footerText . '</div>'
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
            margin-bottom: 30px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 20px;
        }
        .header-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .header-left, .header-center, .header-right {
            display: table-cell;
            vertical-align: top;
            padding: 0 10px;
        }
        .header-left {
            text-align: left;
            width: 33.33%;
        }
        .header-center {
            text-align: center;
            width: 33.33%;
        }
        .header-right {
            text-align: right;
            width: 33.33%;
        }
        .header-logo {
            max-width: 200px;
            max-height: 100px;
            margin: 0 auto;
            display: block;
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
        /* Field placeholder styling - for display before replacement */
        code.field-placeholder {
            display: inline-block;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #86efac;
            border-radius: 6px;
            padding: 4px 10px;
            margin: 0 2px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            font-weight: 600;
            color: #166534;
            box-shadow: 0 2px 4px rgba(34, 197, 94, 0.1);
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            pointer-events: none;
            contenteditable: false;
            position: relative;
        }
        code.field-placeholder::before {
            content: '[';
            color: #16a34a;
            font-weight: bold;
        }
        code.field-placeholder::after {
            content: ']';
            color: #16a34a;
            font-weight: bold;
        }
        code.field-placeholder:hover {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #4ade80;
        }
    </style>
</head>
<body>
    <div class="header">
        {$headerHtml}
    </div>
    <div class="content">
        {$content}
    </div>
    {$footerHtml}
</body>
</html>
HTML;
    }

    /**
     * Build header HTML with three parts (left, center, right).
     *
     * @param  string  $headerLeft
     * @param  string  $headerCenter
     * @param  string  $headerRight
     * @param  ServiceDocumentTemplate  $template
     * @param  string  $direction
     * @param  string  $locale
     * @return string
     */
    protected function buildHeaderHtml(string $headerLeft, string $headerCenter, string $headerRight, ServiceDocumentTemplate $template, string $direction, string $locale): string
    {
        // If no system config values, fallback to template header_image
        if (empty($headerLeft) && empty($headerCenter) && empty($headerRight)) {
            if ($template->header_image) {
                $headerImage = $this->buildImageHtml($template->header_image, '', false);
                return '<div style="text-align: center;">' . $headerImage . '</div>';
            }
            
            return '';
        }

        // Get translated placeholders
        $originalLocale = app()->getLocale();
        app()->setLocale($locale);
        
        $leftPlaceholder = trans('Admin::app.configuration.index.documents.official.header.header-left-placeholder');
        $centerPlaceholder = trans('Admin::app.configuration.index.documents.official.header.header-center-placeholder');
        $rightPlaceholder = trans('Admin::app.configuration.index.documents.official.header.header-right-placeholder');
        
        // Restore original locale
        app()->setLocale($originalLocale);

        // Build left part
        $leftHtml = '';
        if (!empty($headerLeft)) {
            $leftHtml = '<div class="header-left">' . $headerLeft . '</div>';
        } else {
            // Show translated placeholder if empty
            $leftHtml = '<div class="header-left" style="color: #999; font-style: italic;">' . e($leftPlaceholder) . '</div>';
        }

        // Build center part (logo/image)
        $centerHtml = '';
        if (!empty($headerCenter)) {
            $centerHtml = $this->buildImageHtml($headerCenter, 'header-center');
        } elseif ($template->header_image) {
            // Fallback to template header_image
            $centerHtml = $this->buildImageHtml($template->header_image, 'header-center');
        } else {
            // Show translated placeholder if empty
            $centerHtml = '<div class="header-center" style="color: #999; font-style: italic; text-align: center;">' . e($centerPlaceholder) . '</div>';
        }

        // Build right part
        $rightHtml = '';
        if (!empty($headerRight)) {
            $rightHtml = '<div class="header-right">' . $headerRight . '</div>';
        } else {
            // Show translated placeholder if empty
            $rightHtml = '<div class="header-right" style="color: #999; font-style: italic;">' . e($rightPlaceholder) . '</div>';
        }

        // Build header row
        return '<div class="header-row">' . $leftHtml . $centerHtml . $rightHtml . '</div>';
    }

    /**
     * Build image HTML with base64 encoding (similar to invoice PDF).
     *
     * @param  string  $imagePath
     * @param  string  $cssClass
     * @param  bool  $wrapInDiv
     * @return string
     */
    protected function buildImageHtml(string $imagePath, string $cssClass = '', bool $wrapInDiv = true): string
    {
        try {
            $imgTag = '';
            
            // Check if it's a full URL
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                // For external URLs, use direct src
                $imgTag = '<img src="' . e($imagePath) . '" class="header-logo" />';
            } else {
                // Try to get the file path
                $filePath = null;
                
                // Check if it's an absolute path starting with /
                if (substr($imagePath, 0, 1) === '/') {
                    $filePath = public_path($imagePath);
                } else {
                    // Try storage path
                    $storagePath = storage_path('app/public/' . $imagePath);
                    if (file_exists($storagePath)) {
                        $filePath = $storagePath;
                    } else {
                        // Try public path
                        $publicPath = public_path('storage/' . $imagePath);
                        if (file_exists($publicPath)) {
                            $filePath = $publicPath;
                        }
                    }
                }

                // If file exists, use base64 encoding (like invoice PDF)
                if ($filePath && file_exists($filePath)) {
                    $imageData = base64_encode(file_get_contents($filePath));
                    $imageInfo = getimagesize($filePath);
                    $mimeType = $imageInfo ? $imageInfo['mime'] : 'image/png';
                    
                    $imgTag = '<img src="data:' . $mimeType . ';base64,' . $imageData . '" class="header-logo" />';
                } else {
                    // Fallback to asset URL
                    $imgTag = '<img src="' . asset($imagePath) . '" class="header-logo" />';
                }
            }

            // Wrap in div if requested
            if ($wrapInDiv && !empty($cssClass)) {
                return '<div class="' . $cssClass . '">' . $imgTag . '</div>';
            }

            return $imgTag;
        } catch (\Exception $e) {
            // If anything fails, use asset URL as fallback
            $imgTag = '<img src="' . asset($imagePath) . '" class="header-logo" />';
            
            if ($wrapInDiv && !empty($cssClass)) {
                return '<div class="' . $cssClass . '">' . $imgTag . '</div>';
            }

            return $imgTag;
        }
    }

    /**
     * Check if service has file or image fields.
     *
     * @param  ServiceRequest  $serviceRequest
     * @return bool
     */
    public function hasFileOrImageFields(ServiceRequest $serviceRequest): bool
    {
        $service = $serviceRequest->service;

        if (! $service) {
            return false;
        }

        $pivotRelations = ServiceAttributeGroupService::with([
            'fields.attributeType',
        ])
        ->where('service_id', $service->id)
        ->get();

        foreach ($pivotRelations as $pivot) {
            foreach ($pivot->fields as $field) {
                if ($field->attributeType && in_array($field->attributeType->code, ['file', 'image'])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Generate Word document for service request.
     *
     * @param  ServiceRequest  $serviceRequest
     * @return string|null Path to generated Word file
     */
    public function generateWordDocument(ServiceRequest $serviceRequest): ?string
    {
        if (! $serviceRequest->service) {
            \Log::warning('DocumentTemplateService: Service not found for request', [
                'service_request_id' => $serviceRequest->id,
            ]);

            return null;
        }

        $wordService = new WordDocumentService();

        return $wordService->generateEditableWordFromTemplate($serviceRequest);
    }

    /**
     * Generate and download Word document (same approach as PDF - using view).
     *
     * @param  ServiceRequest  $serviceRequest
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateAndDownloadWord(ServiceRequest $serviceRequest)
    {
        try {
            $service = $serviceRequest->service;
            $template = $service->documentTemplate;

            if (! $template || ! $template->is_active) {
                throw new \Exception('Template not found or inactive');
            }

            // Get request locale (fallback to app locale)
            $requestLocale = $serviceRequest->locale ?? app()->getLocale();
            
            // Get template content for request locale
            $templateTranslation = $template->translate($requestLocale);
            $templateContent = $templateTranslation?->template_content ?? $template->template_content;

            // Get field values and replace placeholders (same as PDF)
            $fieldValues = $this->getFieldValues($serviceRequest);
            $content = $this->replacePlaceholders($templateContent, $fieldValues);

            // Get footer text for the specified locale
            $templateTranslation = $template->translate($requestLocale);
            $footerText = $templateTranslation?->footer_text ?? $template->footer_text;
            
            // Get locale model for direction
            $localeModel = core()->getAllLocales()->firstWhere('code', $requestLocale);
            
            // Generate HTML using view (same as PDF)
            $html = view('admin::service-requests.word', compact('serviceRequest', 'template', 'content', 'requestLocale', 'footerText', 'localeModel'))->render();
            
            // Generate temp file
            $fileName = 'document-' . $serviceRequest->increment_id . '-' . now()->format('Y-m-d') . '.doc';
            $tempPath = sys_get_temp_dir() . '/' . uniqid('word_') . '_' . $fileName;
            
            // Save HTML as .doc (Word can open HTML files)
            file_put_contents($tempPath, $html);
            
            // Verify file was created
            if (!file_exists($tempPath) || filesize($tempPath) === 0) {
                throw new \Exception('Word file was not generated properly');
            }
            
            \Log::info('Word document generated successfully', [
                'service_request_id' => $serviceRequest->id,
                'file_size' => filesize($tempPath),
            ]);
            
            // Return as download and delete after sending
            return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            \Log::error('DocumentTemplateService: Failed to generate and download Word', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            throw $e;
        }
    }
}

