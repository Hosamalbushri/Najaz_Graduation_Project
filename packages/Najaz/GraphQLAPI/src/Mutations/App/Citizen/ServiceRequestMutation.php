<?php

namespace Najaz\GraphQLAPI\Mutations\App\Citizen;

use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Najaz\Request\Models\ServiceRequestProxy;
use Najaz\Request\Repositories\ServiceRequestRepository;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Najaz\Service\Models\ServiceProxy;
use Najaz\Service\Services\DocumentTemplateService;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Webkul\GraphQLAPI\Validators\CustomException;

class ServiceRequestMutation extends Controller
{
    public function __construct(
        protected ServiceRequestRepository $serviceRequestRepository,
    ) {}

    /**
     * Create a new service request.
     */
    public function store($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        // Handle file uploads first and merge file paths into form_data
        if (isset($args['files']) && is_array($args['files']) && !empty($args['files'])) {
            $filePaths = $this->processFileUploads($args['service_id'], $citizen->id, $args['files']);
            // Merge file paths into form_data (both nested and flat structures)
            $args['form_data'] = $this->mergeFilePathsIntoFormData($args['form_data'] ?? [], $filePaths);
        }

        najaz_graphql()->validate($args, [
            'service_id'  => ['required', 'integer', 'exists:services,id'],
            'form_data'  => ['required', 'array'],
            'notes'      => ['nullable', 'string'],
        ]);

        $request = $this->serviceRequestRepository->createWithValidation($args, $citizen->id);

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.service_request.created'),
            'request' => $request,
        ];
    }

    /**
     * Update a service request (only if pending or in_progress).
     */
    public function update($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $request = $this->serviceRequestRepository->findOrFail($args['id']);

        if ($request->citizen_id !== $citizen->id) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.not_found')
            );
        }

        if (! in_array($request->status, ['pending', 'in_progress'])) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.cannot_update')
            );
        }

        // Handle file uploads if provided
        if (isset($args['files']) && is_array($args['files']) && !empty($args['files'])) {
            $filePaths = $this->processFileUploads($request->service_id, $citizen->id, $args['files'], $request->id);
            
            // Get existing form_data or initialize empty array
            $existingFormData = $args['form_data'] ?? [];
            
            // Merge file paths into form_data
            $args['form_data'] = $this->mergeFilePathsIntoFormData($existingFormData, $filePaths);
        }

        najaz_graphql()->validate($args, [
            'form_data' => ['nullable', 'array'],
            'notes'     => ['nullable', 'string'],
        ]);

        $data = array_filter([
            'form_data' => $args['form_data'] ?? null,
            'notes'     => $args['notes'] ?? null,
        ], fn ($value) => $value !== null);

        $request = $this->serviceRequestRepository->updateRequest($data, $request->id);

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.service_request.updated'),
            'request' => $request,
        ];
    }

    /**
     * Cancel a service request.
     */
    public function cancel($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $request = $this->serviceRequestRepository->findOrFail($args['id']);

        if ($request->citizen_id !== $citizen->id) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.not_found')
            );
        }

        if (! in_array($request->status, ['pending', 'in_progress'])) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.cannot_cancel')
            );
        }

        $this->serviceRequestRepository->cancelRequest($request->id);

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.service_request.cancelled'),
        ];
    }

    /**
     * Process file uploads for service request fields.
     *
     * @param  int  $serviceId
     * @param  int  $citizenId
     * @param  array  $files
     * @param  int|null  $requestId  Optional request ID for updating existing request (to delete old files)
     * @return array  Array of field_code => file_path
     */
    protected function processFileUploads(int $serviceId, int $citizenId, array $files, ?int $requestId = null): array
    {
        // Load service to get field definitions
        $service = ServiceProxy::modelClass()::with([
            'attributeGroups.fields.attributeType',
        ])->findOrFail($serviceId);

        // Load custom service fields from ServiceAttributeGroupService
        $pivotIds = $service->attributeGroups->pluck('pivot.id')->filter();
        $pivotRelations = collect();
        if ($pivotIds->isNotEmpty()) {
            $pivotRelations = ServiceAttributeGroupService::with([
                'fields.translations',
                'fields.attributeType.translations',
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');
        }

        // Build map of field codes to field definitions
        $fileFieldsMap = [];
        $allFieldCodes = [];
        foreach ($service->attributeGroups as $group) {
            $pivotId = $group->pivot->id ?? null;
            $pivotRelation = $pivotId ? ($pivotRelations[$pivotId] ?? null) : null;
            $fieldsToUse = $pivotRelation && $pivotRelation->fields->isNotEmpty()
                ? $pivotRelation->fields
                : ($group->fields ?? collect());

            foreach ($fieldsToUse as $field) {
                // Get field type - prefer direct 'type' attribute, fallback to attributeType->code
                $fieldType = null;
                
                // First try direct 'type' attribute (for ServiceAttributeGroupServiceField)
                if (isset($field->type) && !empty($field->type)) {
                    $fieldType = $field->type;
                } else {
                    // Fallback to attributeType relationship
                    if (!$field->relationLoaded('attributeType')) {
                        $field->load('attributeType');
                    }
                    
                    if ($field->attributeType) {
                        $fieldType = $field->attributeType->code;
                    }
                }
                
                $allFieldCodes[] = [
                    'code' => $field->code,
                    'type' => $fieldType ?? 'unknown',
                    'label' => $field->translate(app()->getLocale())?->label ?? $field->code,
                ];
                
                // Only add file/image fields to the map
                if ($fieldType && in_array($fieldType, ['file', 'image'])) {
                    $fileFieldsMap[$field->code] = $field;
                }
            }
        }

        // Log all fields and file fields for debugging
        \Log::info('ServiceRequestMutation::processFileUploads - Available fields', [
            'service_id' => $serviceId,
            'all_fields' => $allFieldCodes,
            'file_fields' => array_keys($fileFieldsMap),
        ]);

        $filePaths = [];

        foreach ($files as $fileInput) {
            $fieldCode = $fileInput['field_code'] ?? null;
            $file = $fileInput['file'] ?? null;

            if (!$fieldCode || !$file instanceof UploadedFile) {
                continue;
            }

            // Validate that field exists and is file/image type
            if (!isset($fileFieldsMap[$fieldCode])) {
                // Get available file/image field codes for better error message
                $availableFileFields = array_keys($fileFieldsMap);
                $availableFieldsList = !empty($availableFileFields) 
                    ? implode(', ', $availableFileFields)
                    : trans('najaz_graphql::app.citizens.service_request.no_file_fields_available');
                
                $errorMessage = trans('najaz_graphql::app.citizens.service_request.invalid_file_field', ['field' => $fieldCode]);
                $errorMessage .= ' '.trans('najaz_graphql::app.citizens.service_request.available_file_fields', ['fields' => $availableFieldsList]);
                
                \Log::warning('ServiceRequestMutation::processFileUploads - Invalid file field', [
                    'requested_field' => $fieldCode,
                    'available_file_fields' => $availableFileFields,
                    'all_fields' => $allFieldCodes,
                ]);
                
                throw new CustomException($errorMessage);
            }

            $field = $fileFieldsMap[$fieldCode];

            // Validate file against field validation rules
            $this->validateFileAgainstField($file, $field);

            // If updating existing request, delete old file if exists
            if ($requestId) {
                $this->deleteOldFileForField($requestId, $fieldCode);
            }

            // Store file
            $directory = "service_requests/{$citizenId}/{$serviceId}/{$fieldCode}";
            $path = $file->store($directory, 'public');
            $filePaths[$fieldCode] = $path;
        }

        return $filePaths;
    }

    /**
     * Validate uploaded file against field validation rules.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  \Najaz\Service\Models\ServiceAttributeGroupServiceField  $field
     * @return void
     * @throws \Webkul\GraphQLAPI\Validators\CustomException
     */
    protected function validateFileAgainstField(UploadedFile $file, $field): void
    {
        $rules = [];
        $validationRules = $field->validation_rules ?? [];

        // Parse validation rules (can be array with 'validation' key or string)
        $validationString = '';
        if (is_array($validationRules) && isset($validationRules['validation'])) {
            $validationString = $validationRules['validation'];
        } elseif (is_string($validationRules)) {
            $validationString = $validationRules;
        }

        // Parse mimes rule
        if (preg_match('/mimes:([^|]+)/', $validationString, $matches)) {
            $extensions = array_map('trim', explode(',', $matches[1]));
            $rules[] = 'mimes:'.implode(',', $extensions);
        } else {
            // Default mimes based on field type
            if ($field->attributeType->code === 'image') {
                $rules[] = 'mimes:jpg,jpeg,png,gif,webp';
            } else {
                $rules[] = 'mimes:pdf,doc,docx,xls,xlsx,txt';
            }
        }

        // Parse max size rule
        if (preg_match('/max:(\d+)/', $validationString, $matches)) {
            $rules[] = 'max:'.$matches[1];
        } else {
            // Default max size: 5MB
            $rules[] = 'max:5120';
        }

        $validator = Validator::make(['file' => $file], ['file' => implode('|', $rules)]);

        if ($validator->fails()) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.file_validation_failed', [
                    'field' => $field->translate(app()->getLocale())?->label ?? $field->code,
                    'errors' => $validator->errors()->first('file'),
                ])
            );
        }
    }

    /**
     * Merge file paths into form_data structure.
     * Handles both nested (group-based) and flat structures.
     *
     * @param  array  $formData
     * @param  array  $filePaths
     * @return array
     */
    protected function mergeFilePathsIntoFormData(array $formData, array $filePaths): array
    {
        foreach ($filePaths as $fieldCode => $filePath) {
            // Try to find the field in nested structure first (formData[groupCode][fieldCode])
            $found = false;
            foreach ($formData as $groupCode => $groupData) {
                if (is_array($groupData) && array_key_exists($fieldCode, $groupData)) {
                    $formData[$groupCode][$fieldCode] = $filePath;
                    $found = true;
                    break;
                }
            }

            // If not found in nested structure, check if fieldCode itself is a key in formData (flat structure)
            // This handles the case where formData has direct field codes as keys
            if (!$found && isset($formData[$fieldCode])) {
                $formData[$fieldCode] = $filePath;
                $found = true;
            }

            // If still not found, add to flat structure
            if (!$found) {
                $formData[$fieldCode] = $filePath;
            }
        }

        return $formData;
    }

    /**
     * Delete old file for a specific field in a service request.
     *
     * @param  int  $requestId
     * @param  string  $fieldCode
     * @return void
     */
    protected function deleteOldFileForField(int $requestId, string $fieldCode): void
    {
        $request = $this->serviceRequestRepository->findOrFail($requestId);
        
        // Load form data to find existing file path
        $request->load('formData');
        
        foreach ($request->formData as $formDataRecord) {
            $fieldsData = $formDataRecord->fields_data ?? [];
            
            if (is_array($fieldsData) && isset($fieldsData[$fieldCode])) {
                $oldFilePath = $fieldsData[$fieldCode];
                
                if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
                    Storage::disk('public')->delete($oldFilePath);
                }
                
                break;
            }
        }
    }

    /**
     * Print service request document (PDF) for citizen.
     * Only available for completed requests.
     */
    public function printDocument($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $serviceRequest = ServiceRequestProxy::modelClass()::with(['service.documentTemplate', 'beneficiaries'])
            ->findOrFail($args['id']);

        // Check if citizen has access (requester or beneficiary)
        if (! $this->serviceRequestRepository->canCitizenAccess($serviceRequest, $citizen)) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.not_found')
            );
        }

        // Check if request is completed
        if ($serviceRequest->status !== 'completed' || ! $serviceRequest->completed_at) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.document_not_available')
            );
        }

        // Check if template exists and is active
        $template = $serviceRequest->service->documentTemplate;
        if (! $template || ! $template->is_active) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.template_not_found')
            );
        }

        try {
            // Generate document content using DocumentTemplateService
            $documentService = new DocumentTemplateService;
            $content = $documentService->generateDocumentContent($serviceRequest);

            // Generate PDF HTML content
            $pdfHtml = view('admin::service-requests.pdf', compact('serviceRequest', 'content'))->render();

            // Generate PDF as string
            $direction = core()->getCurrentLocale()->direction ?? 'ltr';
            $pdfContent = '';

            if ($direction === 'rtl') {
                $mPDF = $this->makeMpdfForRtl();
                $mPDF->SetDirectionality('rtl');
                $mPDF->SetDisplayMode('fullpage');
                $mPDF->WriteHTML($pdfHtml);
                $pdfContent = $mPDF->Output('', 'S');
            } else {
                // For LTR, use DomPDF
                $html = mb_convert_encoding($pdfHtml, 'HTML-ENTITIES', 'UTF-8');
                $html = $this->adjustArabicAndPersianContent($html);
                
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                    ->setPaper('A4', 'portrait')
                    ->set_option('defaultFont', 'DejaVu Sans');
                
                $pdfContent = $pdf->output();
            }

            // Convert PDF to base64
            $base64Content = base64_encode($pdfContent);

            $filename = 'document-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y').'.pdf';

            return [
                'success' => true,
                'filename' => $filename,
                'base64_content' => $base64Content,
                'mime_type' => 'application/pdf',
            ];
        } catch (\Exception $e) {
            throw new CustomException(
                trans('najaz_graphql::app.citizens.service_request.print_error', ['error' => $e->getMessage()])
            );
        }
    }

    /**
     * Make mPDF instance for RTL documents.
     */
    protected function makeMpdfForRtl(): Mpdf
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs      = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData          = $defaultFontConfig['fontdata'];

        $customFontDir = public_path('fonts');

        $hasXBRiyaz = file_exists($customFontDir . '/XB Riyaz.ttf')
            && file_exists($customFontDir . '/XB RiyazBd.ttf');

        $hasXBZar = file_exists($customFontDir . '/XB Zar.ttf')
            && file_exists($customFontDir . '/XB ZarBd.ttf');

        $hasAmiri = file_exists($customFontDir . '/Amiri-Regular.ttf')
            && file_exists($customFontDir . '/Amiri-Bold.ttf');

        $hasNoto = file_exists($customFontDir . '/NotoNaskhArabic-Regular.ttf')
            && file_exists($customFontDir . '/NotoNaskhArabic-Bold.ttf');

        $extraFontData = [];
        $defaultFont   = 'dejavusans';

        if ($hasXBRiyaz) {
            $defaultFont = 'xbriyaz';
            $extraFontData['xbriyaz'] = [
                'R' => 'XB Riyaz.ttf',
                'B' => 'XB RiyazBd.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        } elseif ($hasXBZar) {
            $defaultFont = 'xbzar';
            $extraFontData['xbzar'] = [
                'R' => 'XB Zar.ttf',
                'B' => 'XB ZarBd.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        } elseif ($hasAmiri) {
            $defaultFont = 'amiri';
            $extraFontData['amiri'] = [
                'R' => 'Amiri-Regular.ttf',
                'B' => 'Amiri-Bold.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        } elseif ($hasNoto) {
            $defaultFont = 'notonaskharabic';
            $extraFontData['notonaskharabic'] = [
                'R' => 'NotoNaskhArabic-Regular.ttf',
                'B' => 'NotoNaskhArabic-Bold.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ];
        }

        $tempDir = storage_path('app/mpdf-temp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
        }

        return new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left'   => 0,
            'margin_right'  => 0,
            'margin_top'    => 0,
            'margin_bottom' => 0,
            'tempDir' => $tempDir,
            'fontDir'  => array_merge($fontDirs, [$customFontDir]),
            'fontdata' => $fontData + $extraFontData,
            'default_font' => $defaultFont,
            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
        ]);
    }

    /**
     * Adjust arabic and persian content for DomPDF.
     */
    protected function adjustArabicAndPersianContent(string $html): string
    {
        $arabic = new \ArPHP\I18N\Arabic;
        $p = $arabic->arIdentify($html);

        for ($i = count($p) - 1; $i >= 0; $i -= 2) {
            $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]));
            $html = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
        }

        return $html;
    }
}
