<?php

namespace Najaz\Admin\Http\Controllers\Admin\ServiceRequests;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Request\Models\ServiceRequestProxy;
use Najaz\Request\Repositories\ServiceRequestAdminNoteRepository;
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
        protected ServiceRequestAdminNoteRepository $adminNoteRepository
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
        ])->findOrFail($id);

        // Generate document content if template exists and is active
        $documentContent = null;
        $template = $request->service->documentTemplate;

        if ($template && $template->is_active) {
            try {
                $documentService = new DocumentTemplateService;
                $fieldValues = $documentService->getFieldValues($request);
                $documentContent = $documentService->replacePlaceholders($template->template_content, $fieldValues);
            } catch (\Exception $e) {
                \Log::error('Error generating document content in view: '.$e->getMessage());
            }
        }

        // Build field labels map for translations
        $fieldLabelsMap = [];
        $locale = app()->getLocale();

        if ($request->service && $request->service->attributeGroups) {
            foreach ($request->service->attributeGroups as $group) {
                $groupCode = $group->pivot->custom_code ?? $group->code;

                foreach ($group->fields as $field) {
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
        $nationalIdFieldCodes = ['national_id', 'citizen_id', 'nationalid', 'citizenid', 'id_number', 'idnumber', 'national_number', 'identity_number'];

        // Collect all national IDs from form data
        $nationalIds = [];
        foreach ($request->formData as $formData) {
            if ($formData->fields_data && is_array($formData->fields_data)) {
                foreach ($formData->fields_data as $fieldCode => $fieldValue) {
                    $fieldCodeLower = strtolower($fieldCode);
                    if (in_array($fieldCodeLower, $nationalIdFieldCodes) && ! empty($fieldValue)) {
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

        return view('admin::service-requests.view', compact('request', 'documentContent', 'template', 'fieldLabelsMap', 'nationalIdToCitizenMap', 'localeName'));
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

            session()->flash('success', trans('Admin::app.service-requests.view.status-update-success'));

            return redirect()->route('admin.service-requests.view', $request->id);

        } catch (\Exception $e) {
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

            $template = $serviceRequest->service->documentTemplate;

            if (! $template || ! $template->is_active) {
                session()->flash('error', trans('Admin::app.service-requests.view.template-not-found'));

                return redirect()->back();
            }

            // Generate document content using DocumentTemplateService
            $documentService = new DocumentTemplateService;

            // Get field values and replace placeholders
            $fieldValues = $documentService->getFieldValues($serviceRequest);
            $content = $documentService->replacePlaceholders($template->template_content, $fieldValues);

            return $this->downloadPDF(
                view('admin::service-requests.pdf', compact('serviceRequest', 'template', 'content'))->render(),
                'document-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y')
            );
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }
}
