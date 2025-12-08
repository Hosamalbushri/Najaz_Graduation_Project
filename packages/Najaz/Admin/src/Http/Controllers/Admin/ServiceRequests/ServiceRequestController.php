<?php

namespace Najaz\Admin\Http\Controllers\Admin\ServiceRequests;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Request\Models\ServiceRequestProxy;
use Najaz\Request\Repositories\ServiceRequestAdminNoteRepository;
use Najaz\Request\Repositories\ServiceRequestCustomTemplateRepository;
use Najaz\Request\Repositories\ServiceRequestRepository;
use Najaz\Service\Services\DocumentTemplateService;
use Webkul\Core\Traits\PDFHandler;

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
    public function view(int $id): View
    {
        $request = $this->serviceRequestRepository->with([
            'service.documentTemplate',
            'service.attributeGroups.fields',
            'citizen',
            'beneficiaries',
            'formData',
            'adminNotes.admin',
            'customTemplate',
        ])->findOrFail($id);

        // Generate document content if template exists and is active
        $documentContent = null;
        $template = $request->service->documentTemplate;

        if ($template && $template->is_active) {
            try {
                $documentService = new DocumentTemplateService;
                $fieldValues = $documentService->getFieldValues($request);
                
                // Get template content for request locale
                $requestLocale = $request->locale ?? app()->getLocale();
                $templateTranslation = $template->translate($requestLocale);
                $templateContent = $templateTranslation?->template_content ?? $template->template_content;
                
                $documentContent = $documentService->replacePlaceholders($templateContent, $fieldValues);
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

        return view('admin::service-requests.view', compact('request', 'documentContent', 'template', 'fieldLabelsMap', 'nationalIdToCitizenMap', 'localeName', 'isNationalIdField', 'uploadedFiles'));
    }

    /**
     * Update status action for the specified resource.
     */
    public function updateStatus(int $id)
    {
        $validatedData = $this->validate(request(), [
            'status'           => 'required|string|in:pending,in_progress,completed,rejected,canceled',
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
        ]);

        try {
            $updateData = ['status' => $validatedData['status']];

            // Add rejection reason if status is rejected
            if ($validatedData['status'] === 'rejected') {
                $updateData['rejection_reason'] = $validatedData['rejection_reason'] ?? null;
            } else {
                // Clear rejection reason if status is not rejected
                $updateData['rejection_reason'] = null;
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
        $query = request()->input('query');

        $requests = $this->serviceRequestRepository->scopeQuery(function ($q) use ($query) {
            return $q->where('increment_id', 'like', "%{$query}%")
                ->orWhere('status', 'like', "%{$query}%")
                ->orWhere('citizen_first_name', 'like', "%{$query}%")
                ->orWhere('citizen_last_name', 'like', "%{$query}%")
                ->orWhere('citizen_national_id', 'like', "%{$query}%")
                ->orWhereRaw('CONCAT(citizen_first_name, " ", citizen_last_name) LIKE ?', ["%{$query}%"])
                ->orderBy('created_at', 'desc');
        })->paginate(10);

        foreach ($requests as $key => $request) {
            $requests[$key]['formatted_created_at'] = core()->formatDate($request->created_at, 'd M Y');
            $requests[$key]['citizen_full_name'] = trim($request->citizen_first_name.' '.$request->citizen_middle_name.' '.$request->citizen_last_name);
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

            // Check if there's a final PDF uploaded by admin
            if ($serviceRequest->final_pdf_path && \Storage::exists($serviceRequest->final_pdf_path)) {
                $fileName = 'document-'.$serviceRequest->increment_id.'-'.now()->format('d-m-Y').'.pdf';
                
                return \Storage::download($serviceRequest->final_pdf_path, $fileName);
            }

            $template = $serviceRequest->service->documentTemplate;

            if (! $template || ! $template->is_active) {
                session()->flash('error', trans('Admin::app.service-requests.view.template-not-found'));

                return redirect()->back();
            }

            // Generate document content using DocumentTemplateService
            $documentService = new DocumentTemplateService;
            
            // Get request locale (fallback to app locale)
            $requestLocale = $serviceRequest->locale ?? app()->getLocale();
            
            // Get template content for request locale
            $templateTranslation = $template->translate($requestLocale);
            $templateContent = $templateTranslation?->template_content ?? $template->template_content;
            
            // Get all field values
            $fieldValues = $documentService->getFieldValues($serviceRequest);
            
            // Replace placeholders in template
            $content = $documentService->replacePlaceholders($templateContent, $fieldValues);
            
            // Merge custom template content if available
            $content = $documentService->mergeCustomContent($serviceRequest, $content);

            // Use view to build PDF (like invoice)
            return $this->downloadPDF(
                view('admin::service-requests.pdf', compact('serviceRequest', 'template', 'content', 'requestLocale'))->render(),
                'document-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y')
            );
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }

    /**
     * Download editable Word document for the specified resource.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
     */
    public function downloadEditableWord(int $id)
    {
        try {
            $serviceRequest = ServiceRequestProxy::modelClass()::with(['service.documentTemplate'])
                ->findOrFail($id);

            $template = $serviceRequest->service->documentTemplate;

            if (! $template || ! $template->is_active) {
                session()->flash('error', trans('Admin::app.service-requests.word-document.template-not-found'));

                return redirect()->back();
            }

            // Generate and download Word document directly (same as PDF)
            $documentService = new DocumentTemplateService;
            
            return $documentService->generateAndDownloadWord($serviceRequest);
        } catch (\Exception $e) {
            \Log::error('Failed to download Word document', [
                'service_request_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            session()->flash('error', trans('Admin::app.service-requests.word-document.download-failed'));

            return redirect()->back();
        }
    }

    /**
     * Upload filled PDF document for the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function uploadFilledPDF(int $id)
    {
        try {
            $this->validate(request(), [
                'filled_pdf' => 'required|file|mimes:pdf|max:10240', // 10MB max
            ]);

            $serviceRequest = $this->serviceRequestRepository->findOrFail($id);

            // Delete old PDF if exists
            if ($serviceRequest->final_pdf_path && \Storage::exists($serviceRequest->final_pdf_path)) {
                \Storage::delete($serviceRequest->final_pdf_path);
            }

            // Store the new PDF
            $file = request()->file('filled_pdf');
            $directory = 'service_requests/'.$serviceRequest->id;
            $filename = 'final-'.$serviceRequest->increment_id.'.pdf';
            $path = $file->storeAs($directory, $filename);

            // Update service request
            $serviceRequest->final_pdf_path = $path;
            $serviceRequest->filled_by_admin_id = auth()->guard('admin')->id();
            $serviceRequest->filled_at = now();
            $serviceRequest->save();

            if (request()->expectsJson()) {
                return new JsonResponse([
                    'message' => trans('Admin::app.service-requests.word-document.upload-success'),
                    'data' => [
                        'path' => $path,
                        'filled_at' => $serviceRequest->filled_at->format('Y-m-d H:i:s'),
                        'filled_by' => auth()->guard('admin')->user()->name ?? '',
                    ],
                ]);
            }

            session()->flash('success', trans('Admin::app.service-requests.word-document.upload-success'));

            return redirect()->back();
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (request()->expectsJson()) {
                return new JsonResponse([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Failed to upload PDF document', [
                'service_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            if (request()->expectsJson()) {
                return new JsonResponse([
                    'message' => trans('Admin::app.service-requests.word-document.upload-failed'),
                ], 500);
            }

            session()->flash('error', trans('Admin::app.service-requests.word-document.upload-failed'));

            return redirect()->back();
        }
    }
}
