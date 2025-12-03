<?php

namespace Najaz\Service\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Najaz\Request\Models\ServiceRequest;
use Najaz\Request\Models\ServiceRequestFormData;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Najaz\Service\Models\ServiceDocumentTemplate;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html;
use PhpOffice\PhpWord\Style\Font;

class WordDocumentService
{
    /**
     * Generate editable Word document from template.
     *
     * @param  ServiceRequest  $serviceRequest
     * @return string|null Path to generated Word file
     */
    public function generateEditableWordFromTemplate(ServiceRequest $serviceRequest): ?string
    {
        $service = $serviceRequest->service;

        if (! $service) {
            \Log::warning('WordDocumentService: Service not found', [
                'service_request_id' => $serviceRequest->id,
            ]);

            return null;
        }

        $template = $service->documentTemplate;

        if (! $template || ! $template->is_active) {
            \Log::warning('WordDocumentService: Template not found or inactive', [
                'service_request_id' => $serviceRequest->id,
            ]);

            return null;
        }

        try {
            // Get field values (text fields only, leave file/image fields empty)
            $fieldValues = $this->getTextFieldValues($serviceRequest);

            // Create PhpWord instance
            $phpWord = new PhpWord();
            
            // Set document properties
            $properties = $phpWord->getDocInfo();
            $properties->setCreator('Najaz System');
            $properties->setTitle('Service Request Document - '.$serviceRequest->increment_id);
            $properties->setDescription('Editable document for service request');
            $properties->setCreated(time());

            // Set default font
            $phpWord->setDefaultFontName('Arial');
            $phpWord->setDefaultFontSize(12);

            // Add section
            $section = $phpWord->addSection([
                'marginTop' => 1000,
                'marginBottom' => 1000,
                'marginLeft' => 1000,
                'marginRight' => 1000,
            ]);

            // Add header if exists
            if ($template->header_image) {
                $header = $section->addHeader();
                $imagePath = Storage::path($template->header_image);
                if (file_exists($imagePath)) {
                    $header->addImage($imagePath, [
                        'width' => 150,
                        'height' => 50,
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                    ]);
                }
            }

            // Replace placeholders and convert HTML to Word content
            $content = $this->replacePlaceholders($template->template_content, $fieldValues, $serviceRequest);
            
            // Add content to section
            $this->addHtmlContent($section, $content);

            // Add footer if exists
            if ($template->footer_text) {
                $footer = $section->addFooter();
                $footer->addText($template->footer_text, ['size' => 10], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            }

            // Generate filename and path
            $filename = 'editable-'.$serviceRequest->increment_id.'.docx';
            $directory = 'service_requests/'.$serviceRequest->id;
            $fullPath = $directory.'/'.$filename;

            // Ensure directory exists
            if (! Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Save the Word document
            $tempPath = storage_path('app/'.$fullPath);
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($tempPath);

            \Log::info('WordDocumentService: Word document generated', [
                'service_request_id' => $serviceRequest->id,
                'path' => $fullPath,
            ]);

            return $fullPath;
        } catch (\Exception $e) {
            \Log::error('WordDocumentService: Failed to generate Word document', [
                'service_request_id' => $serviceRequest->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Get text field values (exclude file/image fields).
     *
     * @param  ServiceRequest  $serviceRequest
     * @return array
     */
    protected function getTextFieldValues(ServiceRequest $serviceRequest): array
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

        // Load service attribute groups with fields
        $service = $serviceRequest->service;

        if (! $service) {
            return $values;
        }
        $pivotRelations = ServiceAttributeGroupService::with([
            'attributeGroup.translations',
            'fields.translations',
            'fields.attributeType.translations',
            'fields.options.translations',
        ])
        ->where('service_id', $service->id)
        ->get();

        // Create a map of group codes
        $groupCodeMap = [];
        foreach ($pivotRelations as $pivot) {
            $groupCode = $pivot->custom_code ?? $pivot->attributeGroup->code;
            $groupCodeMap[$groupCode] = $pivot;
        }

        // Get form data
        $formDataRecords = ServiceRequestFormData::where('service_request_id', $serviceRequest->id)
            ->orderBy('sort_order')
            ->get();

        $formData = [];
        foreach ($formDataRecords as $record) {
            $groupCode = $record->group_code;
            $fieldsData = $record->fields_data;

            if (is_string($fieldsData)) {
                $fieldsData = json_decode($fieldsData, true) ?? [];
            }

            if (is_array($fieldsData) && ! empty($fieldsData)) {
                $formData[$groupCode] = $fieldsData;
            }
        }

        // Extract field values (exclude file/image fields)
        foreach ($formData as $groupCode => $groupFields) {
            if (is_array($groupFields)) {
                $pivotRelation = $groupCodeMap[$groupCode] ?? null;
                
                foreach ($groupFields as $fieldCode => $fieldValue) {
                    // Check if this is a file/image field
                    $isFileField = false;
                    if ($pivotRelation) {
                        $field = $pivotRelation->fields->firstWhere('code', $fieldCode);
                        if ($field && in_array($field->attributeType->code, ['file', 'image'])) {
                            $isFileField = true;
                        }
                    }

                    // Only add non-file fields
                    if (! $isFileField) {
                        $stringValue = is_null($fieldValue) ? '' : (string) $fieldValue;
                        
                        // Add nested fields
                        $nestedKey = $groupCode.'.'.$fieldCode;
                        $values[$nestedKey] = $stringValue;

                        // Add flat fields
                        if (! isset($values[$fieldCode])) {
                            $values[$fieldCode] = $stringValue;
                        }
                    }
                }
            }
        }

        return $values;
    }

    /**
     * Replace placeholders in template content.
     * For file/image fields, add clear placeholder text.
     *
     * @param  string  $content
     * @param  array  $fieldValues
     * @param  ServiceRequest  $serviceRequest
     * @return string
     */
    protected function replacePlaceholders(string $content, array $fieldValues, ServiceRequest $serviceRequest): string
    {
        // Get file/image fields to add special placeholders
        $fileFields = $this->getFileImageFields($serviceRequest);

        // Replace <code data-field="..."> tags
        $content = preg_replace_callback(
            '/<code[^>]*data-field=["\']([^"\']+)["\'][^>]*>.*?<\/code>/is',
            function ($matches) use ($fieldValues, $fileFields) {
                $fieldCode = trim($matches[1]);
                
                // Check if it's a file/image field
                if (in_array($fieldCode, $fileFields)) {
                    return '[أدخل البيانات من الملف: '.$fieldCode.']';
                }
                
                // Return value if exists
                if (isset($fieldValues[$fieldCode])) {
                    return htmlspecialchars($fieldValues[$fieldCode], ENT_QUOTES, 'UTF-8');
                }
                
                return '';
            },
            $content
        );

        // Replace {{field_code}} placeholders
        $content = preg_replace_callback(
            '/\{\{([^}]+)\}\}/',
            function ($matches) use ($fieldValues, $fileFields) {
                $fieldCode = trim($matches[1]);
                
                // Check if it's a file/image field
                if (in_array($fieldCode, $fileFields)) {
                    return '[أدخل البيانات من الملف: '.$fieldCode.']';
                }
                
                // Return value if exists
                if (isset($fieldValues[$fieldCode])) {
                    return $fieldValues[$fieldCode];
                }
                
                return '';
            },
            $content
        );

        return $content;
    }

    /**
     * Get file/image field codes for the service request.
     *
     * @param  ServiceRequest  $serviceRequest
     * @return array
     */
    protected function getFileImageFields(ServiceRequest $serviceRequest): array
    {
        $fileFields = [];
        $service = $serviceRequest->service;

        if (! $service) {
            return $fileFields;
        }

        $pivotRelations = ServiceAttributeGroupService::with([
            'fields.attributeType',
        ])
        ->where('service_id', $service->id)
        ->get();

        foreach ($pivotRelations as $pivot) {
            foreach ($pivot->fields as $field) {
                if ($field->attributeType && in_array($field->attributeType->code, ['file', 'image'])) {
                    $fileFields[] = $field->code;
                }
            }
        }

        return $fileFields;
    }

    /**
     * Add HTML content to Word section.
     * Simplified version - converts basic HTML to Word elements.
     *
     * @param  \PhpOffice\PhpWord\Element\Section  $section
     * @param  string  $html
     * @return void
     */
    protected function addHtmlContent($section, string $html): void
    {
        // Remove HTML tags and add as text with basic formatting
        // Strip complex HTML and keep text only
        $html = strip_tags($html, '<p><br><strong><em><u><h1><h2><h3><ul><ol><li>');
        
        try {
            // Try to use Html::addHtml if available
            Html::addHtml($section, $html, false, false);
        } catch (\Exception $e) {
            // Fallback: just add text
            $text = strip_tags($html);
            $lines = explode("\n", $text);
            foreach ($lines as $line) {
                $line = trim($line);
                if (! empty($line)) {
                    $section->addText($line);
                }
            }
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
        if (! $serviceRequest->service) {
            return false;
        }

        $fileFields = $this->getFileImageFields($serviceRequest);

        return count($fileFields) > 0;
    }
}

