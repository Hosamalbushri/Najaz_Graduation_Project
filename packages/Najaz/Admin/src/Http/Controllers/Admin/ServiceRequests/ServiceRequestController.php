<?php

namespace Najaz\Admin\Http\Controllers\Admin\ServiceRequests;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Admin\Traits\PDFHandler;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Request\Models\ServiceRequestProxy;
use Najaz\Request\Models\ServiceRequestStatusReason;
use Najaz\Request\Repositories\ServiceRequestAdminNoteRepository;
use Najaz\Request\Repositories\ServiceRequestCustomTemplateRepository;
use Najaz\Request\Repositories\ServiceRequestRepository;
use Najaz\Service\Services\DocumentTemplateService;

class ServiceRequestController extends Controller
{
    use PDFHandler;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ServiceRequestRepository $serviceRequestRepository,
        protected CitizenRepository $citizenRepository,
        protected ServiceRequestAdminNoteRepository $adminNoteRepository,
        protected ServiceRequestCustomTemplateRepository $customTemplateRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(\Najaz\Admin\DataGrids\ServiceRequests\ServiceRequestDataGrid::class)->process();
        }

        return view('admin::service-requests.index');
    }

    /**
     * Show the view for the specified resource.
     */
    public function view(int $id)
    {
        $request = $this->serviceRequestRepository->with([
            'service.documentTemplate',
            'service.attributeGroups.fields.attributeType',
            'citizen',
            'beneficiaries',
            'formData',
            'adminNotes.admin',
            'customTemplate',
            'statusReasons',
        ])->findOrFail($id);

        // Generate document content if template exists and is active
        $documentContent = null;
        $template = $request->service->documentTemplate;

        if ($template && $template->is_active) {
            try {
                $documentService = new DocumentTemplateService;
                
                // Use generateDocumentContent to get content (which handles custom template replacement)
                $documentContent = $documentService->generateDocumentContent($request);
            } catch (\Exception $e) {
                \Log::error('Error generating document content in view: '.$e->getMessage());
            }
        }

        // Build field labels map for translations - use custom service fields
        $fieldLabelsMap = [];
        $locale = app()->getLocale();

        if ($request->service && $request->service->attributeGroups) {
            // Load custom service fields from ServiceAttributeGroupService
            $pivotIds = $request->service->attributeGroups->pluck('pivot.id')->filter();
            $pivotRelations = collect();
            
            if ($pivotIds->isNotEmpty()) {
                $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                    'fields.translations',
                    'attributeGroup.translations',
                ])->whereIn('id', $pivotIds)->get()->keyBy('id');
            }

            foreach ($request->service->attributeGroups as $group) {
                $pivotId = $group->pivot->id ?? null;
                $pivotRelation = $pivotId ? $pivotRelations->get($pivotId) : null;
                $groupCode = $group->pivot->custom_code ?? $group->code;

                // Use custom service fields if available, otherwise fall back to template fields
                $fieldsToUse = $pivotRelation && $pivotRelation->fields && $pivotRelation->fields->isNotEmpty()
                    ? $pivotRelation->fields
                    : ($group->fields ?? collect());

                foreach ($fieldsToUse as $field) {
                    $fieldTranslation = $field->translate($locale);
                    $fieldLabel = $fieldTranslation?->label ?? $field->code;

                    // Map both flat and nested field codes
                    $fieldLabelsMap[$field->code] = $fieldLabel;
                    $fieldLabelsMap[$groupCode.'.'.$field->code] = $fieldLabel;
                }
            }
        }

        // Build national ID to citizen ID map
        $nationalIdToCitizenMap = [];
        $nationalIdFieldCodes = ['national_id', 'citizen_id', 'nationalid', 'citizenid', 'national_id_card', 'id_number', 'idnumber', 'national_number', 'identity_number'];

        // Helper function to check if field is national ID field
        $isNationalIdField = function ($fieldCode) use ($nationalIdFieldCodes) {
            $fieldCodeLower = strtolower($fieldCode);
            
            // Check exact match
            if (in_array($fieldCodeLower, $nationalIdFieldCodes)) {
                return true;
            }
            
            // Check partial match (e.g., "group_code_national_id_card")
            foreach ($nationalIdFieldCodes as $pattern) {
                if (str_contains($fieldCodeLower, $pattern)) {
                    return true;
                }
            }
            
            return false;
        };

        // Collect all national IDs from form data
        $nationalIds = [];
        foreach ($request->formData as $formData) {
            if ($formData->fields_data && is_array($formData->fields_data)) {
                foreach ($formData->fields_data as $fieldCode => $fieldValue) {
                    if ($isNationalIdField($fieldCode) && ! empty($fieldValue)) {
                        $nationalId = preg_replace('/[^0-9]/', '', (string) $fieldValue);
                        if (! empty($nationalId)) {
                            $nationalIds[] = $nationalId;
                        }
                    }
                }
            }
        }

        // Find citizens by national IDs
        if (! empty($nationalIds)) {
            $citizens = $this->citizenRepository->getModel()
                ->whereIn('national_id', array_unique($nationalIds))
                ->get();

            foreach ($citizens as $citizen) {
                $nationalIdToCitizenMap[$citizen->national_id] = $citizen->id;
            }
        }

        // Get locale name from locale code
        $localeName = $request->locale;
        if ($request->locale) {
            $locale = core()->getAllLocales()->where('code', $request->locale)->first();
            if ($locale) {
                $localeName = $locale->name;
            }
        }

        // Get uploaded files for custom template
        $uploadedFiles = [];
        if ($request->service && $request->service->attributeGroups) {
            $uploadedFiles = $this->customTemplateRepository->getUploadedFiles($request);
        }

        // Build file/image fields map to identify them in view
        $fileImageFieldsMap = [];
        if ($request->service && $request->service->attributeGroups) {
            // Get pivot IDs from the collection (pivot is available on belongsToMany)
            $pivotIds = $request->service->attributeGroups->map(function ($group) {
                return $group->pivot->id ?? null;
            })->filter()->toArray();
            
            $pivotRelations = collect();
            
            if (!empty($pivotIds)) {
                $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                    'fields.translations',
                    'fields.attributeType.translations',
                ])->whereIn('id', $pivotIds)->get()->keyBy('id');
            }

            foreach ($request->service->attributeGroups as $group) {
                // Access pivot data safely
                $pivotId = isset($group->pivot) && isset($group->pivot->id) ? $group->pivot->id : null;
                $pivotRelation = $pivotId ? ($pivotRelations->get($pivotId) ?? null) : null;
                
                $fieldsToUse = $pivotRelation && $pivotRelation->fields && $pivotRelation->fields->isNotEmpty()
                    ? $pivotRelation->fields
                    : ($group->fields ?? collect());

                foreach ($fieldsToUse as $field) {
                    // Get field type - prefer direct 'type' attribute, fallback to attributeType->code
                    $fieldType = null;
                    
                    if (isset($field->type) && !empty($field->type)) {
                        $fieldType = $field->type;
                    } else {
                        if (!$field->relationLoaded('attributeType')) {
                            $field->load('attributeType');
                        }
                        
                        if ($field->attributeType) {
                            $fieldType = $field->attributeType->code;
                        }
                    }
                    
                    // Mark file/image fields
                    if ($fieldType && in_array($fieldType, ['file', 'image'])) {
                        $fileImageFieldsMap[$field->code] = [
                            'type' => $fieldType,
                            'label' => $field->translate($locale)?->label ?? $field->code,
                        ];
                    }
                }
            }
        }

        // Helper function to check if field is file/image
        $isFileImageField = function ($fieldCode) use ($fileImageFieldsMap) {
            return isset($fileImageFieldsMap[$fieldCode]);
        };

        // Collect all file/image fields from all form data for display
        $allFileImageFields = [];
        
        // Debug: Log available file/image field codes
        \Log::info('ServiceRequestController::view - Available file/image field codes', [
            'file_image_fields_map_keys' => array_keys($fileImageFieldsMap),
            'form_data_count' => $request->formData->count(),
        ]);
        
        foreach ($request->formData as $formDataItem) {
            if ($formDataItem->fields_data && is_array($formDataItem->fields_data)) {
                // Debug each form data item
                \Log::info('ServiceRequestController::view - Processing form data', [
                    'group_code' => $formDataItem->group_code,
                    'group_name' => $formDataItem->group_name,
                    'fields_data_keys' => array_keys($formDataItem->fields_data),
                ]);
                
                foreach ($formDataItem->fields_data as $fieldCode => $fieldValue) {
                    // Check if field is file/image type (even if empty value)
                    $isFileImage = $isFileImageField($fieldCode);
                    
                    \Log::info('ServiceRequestController::view - Checking field', [
                        'field_code' => $fieldCode,
                        'is_file_image' => $isFileImage,
                        'field_value_type' => gettype($fieldValue),
                        'field_value_empty' => empty($fieldValue),
                    ]);
                    
                    if ($isFileImage) {
                        $allFileImageFields[] = [
                            'field_code' => $fieldCode,
                            'field_label' => $fileImageFieldsMap[$fieldCode]['label'] ?? $fieldLabelsMap[$fieldCode] ?? $fieldCode,
                            'field_type' => $fileImageFieldsMap[$fieldCode]['type'] ?? 'file',
                            'file_path' => $fieldValue ?? null,
                            'group_name' => $formDataItem->group_name,
                            'group_code' => $formDataItem->group_code,
                        ];
                    }
                }
            }
        }

        // Log for debugging
        \Log::info('ServiceRequestController::view - File/Image fields debugging', [
            'request_id' => $request->id,
            'service_id' => $request->service_id,
            'file_image_fields_map_keys' => array_keys($fileImageFieldsMap),
            'file_image_fields_map' => $fileImageFieldsMap,
            'all_file_image_fields_count' => count($allFileImageFields),
            'all_file_image_fields' => $allFileImageFields,
            'form_data_fields' => $request->formData->map(function ($item) {
                return [
                    'group_code' => $item->group_code,
                    'fields_data_keys' => is_array($item->fields_data) ? array_keys($item->fields_data) : [],
                ];
            })->toArray(),
        ]);

        // If AJAX request, return JSON data for Vue component
        if (request()->ajax()) {
            return response()->json([
                'data' => [
                    'request' => $request,
                    'documentContent' => $documentContent,
                    'template' => $template,
                    'fieldLabelsMap' => $fieldLabelsMap,
                    'nationalIdToCitizenMap' => $nationalIdToCitizenMap,
                    'localeName' => $localeName,
                    'uploadedFiles' => $uploadedFiles,
                    'fileImageFieldsMap' => $fileImageFieldsMap,
                    'allFileImageFields' => $allFileImageFields,
                ],
            ]);
        }

        return view('admin::service-requests.view', compact('request', 'documentContent', 'template', 'fieldLabelsMap', 'nationalIdToCitizenMap', 'localeName', 'isNationalIdField', 'uploadedFiles', 'fileImageFieldsMap', 'isFileImageField', 'allFileImageFields'));
    }

    /**
     * Update status action for the specified resource.
     */
    public function updateStatus(int $id)
    {
        $validatedData = $this->validate(request(), [
            'status'           => 'required|string|in:pending,in_progress,completed,rejected,canceled,needs_revision',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
            'revision_reason'  => 'required_if:status,needs_revision|nullable|string',
        ]);

        try {
            $updateData = ['status' => $validatedData['status']];

            // Save rejection reason to the new table (preserve history)
            if ($validatedData['status'] === 'rejected' && !empty($validatedData['rejection_reason'])) {
                ServiceRequestStatusReason::create([
                    'service_request_id' => $id,
                    'reason_type' => 'rejection',
                    'reason' => $validatedData['rejection_reason'],
                ]);
            }

            // Save revision reason to the new table (preserve history)
            if ($validatedData['status'] === 'needs_revision' && !empty($validatedData['revision_reason'])) {
                ServiceRequestStatusReason::create([
                    'service_request_id' => $id,
                    'reason_type' => 'revision',
                    'reason' => $validatedData['revision_reason'],
                ]);
            }

            // Set completed_at if status is completed
            if ($validatedData['status'] === 'completed') {
                $updateData['completed_at'] = now();
            }

            $request = $this->serviceRequestRepository->update($updateData, $id);

            // Return JSON response for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'message' => trans('Admin::app.service-requests.view.status-update-success'),
                    'data'    => $request,
                ]);
            }

            session()->flash('success', trans('Admin::app.service-requests.view.status-update-success'));

            return redirect()->route('admin.service-requests.view', $request->id);

        } catch (\Exception $e) {
            // Return JSON response for AJAX requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Cancel action for the specified resource.
     */
    public function cancel(int $id)
    {
        try {
            $request = $this->serviceRequestRepository->cancelRequest($id);

            session()->flash('success', trans('Admin::app.service-requests.view.cancel-success'));

            return redirect()->route('admin.service-requests.view', $request->id);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Add admin notes to the request.
     */
    public function addNotes(int $id)
    {
        $validatedData = $this->validate(request(), [
            'admin_notes'      => 'required|string',
            'citizen_notified' => 'sometimes|boolean',
        ]);

        try {
            $this->adminNoteRepository->create([
                'service_request_id' => $id,
                'note'               => $validatedData['admin_notes'],
                'citizen_notified'   => $validatedData['citizen_notified'] ?? false,
                'admin_id'           => auth()->guard('admin')->id(),
            ]);

            session()->flash('success', trans('Admin::app.service-requests.view.notes-success'));

            return redirect()->route('admin.service-requests.view', $id);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Result of search service request.
     */
    public function search(): JsonResponse
    {
        $requests = $this->serviceRequestRepository->with('service')->scopeQuery(function ($query) {
            return $query->where('increment_id', 'like', '%'.urldecode(request()->input('query')).'%')
                ->orWhere('status', 'like', '%'.urldecode(request()->input('query')).'%')
                ->orWhere('citizen_first_name', 'like', '%'.urldecode(request()->input('query')).'%')
                ->orWhere('citizen_last_name', 'like', '%'.urldecode(request()->input('query')).'%')
                ->orWhere('citizen_national_id', 'like', '%'.urldecode(request()->input('query')).'%')
                ->orWhereRaw('CONCAT(citizen_first_name, " ", citizen_last_name) LIKE ?', ['%'.urldecode(request()->input('query')).'%'])
                ->orderBy('created_at', 'desc');
        })->paginate(10);

        foreach ($requests as $key => $request) {
            $requests[$key]['formatted_created_at'] = core()->formatDate($request->created_at, 'd M Y');
            $requests[$key]['citizen_full_name'] = trim($request->citizen_first_name.' '.$request->citizen_middle_name.' '.$request->citizen_last_name);
            
            // Add service base_image if service exists
            if ($request->service && $request->service->base_image) {
                $requests[$key]['service_base_image'] = $request->service->base_image;
            }
        }

        return response()->json($requests);
    }

    /**
     * Print and download the document for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function printDocument(int $id)
    {
        try {
            $serviceRequest = ServiceRequestProxy::modelClass()::with(['service.documentTemplate'])
                ->findOrFail($id);

            $template = $serviceRequest->service->documentTemplate;

            if (! $template || ! $template->is_active) {
                session()->flash('error', trans('Admin::app.service-requests.view.template-not-found'));

                return redirect()->back();
            }

            // Generate document content using DocumentTemplateService
            $documentService = new DocumentTemplateService;
            
            // Generate document content only (without HTML wrapper)
            $content = $documentService->generateDocumentContent($serviceRequest);

            return $this->downloadPDF(
                view('admin::service-requests.pdf', compact('serviceRequest', 'content'))->render(),
                'document-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y')
            );
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Preview and download the document without stamp for the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function previewDocument(int $id)
    {
        try {
            $serviceRequest = ServiceRequestProxy::modelClass()::with(['service.documentTemplate'])
                ->findOrFail($id);

            $template = $serviceRequest->service->documentTemplate;

            if (! $template || ! $template->is_active) {
                session()->flash('error', trans('Admin::app.service-requests.view.template-not-found'));

                return redirect()->back();
            }

            // Generate document content using DocumentTemplateService
            $documentService = new DocumentTemplateService;
            
            // Generate document content only (without HTML wrapper)
            $content = $documentService->generateDocumentContent($serviceRequest);

            return $this->downloadPDF(
                view('admin::service-requests.pdf-preview', compact('serviceRequest', 'content'))->render(),
                'document-preview-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y')
            );
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Get document content for the specified resource by locale.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocumentContent(int $id): JsonResponse
    {
        try {
            $serviceRequest = $this->serviceRequestRepository->with([
                'service.documentTemplate',
            ])->findOrFail($id);

            $template = $serviceRequest->service->documentTemplate;

            if (! $template || ! $template->is_active) {
                return new JsonResponse([
                    'success' => false,
                    'message' => trans('Admin::app.service-requests.view.template-not-found'),
                ], 404);
            }

            // Generate document content using DocumentTemplateService (which handles custom template replacement)
            $documentService = new DocumentTemplateService;
            $documentContent = $documentService->generateDocumentContent($serviceRequest);
            
            // Get locale from request or use request's locale
            $locale = request()->input('locale', $serviceRequest->locale ?? app()->getLocale());

            return new JsonResponse([
                'success' => true,
                'content' => $documentContent,
                'locale' => $locale,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get document content', [
                'service_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return new JsonResponse([
                'success' => false,
                'message' => trans('Admin::app.service-requests.view.document-content-error'),
            ], 500);
        }
    }
}
