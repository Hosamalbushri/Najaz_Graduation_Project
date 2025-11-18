<?php

namespace Najaz\Admin\Http\Controllers\Admin\ServiceRequests;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Request\Models\ServiceRequestProxy;
use Najaz\Request\Repositories\ServiceRequestRepository;
use Najaz\Service\Services\DocumentTemplateService;

class ServiceRequestController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ServiceRequestRepository $serviceRequestRepository
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
            'citizen',
            'assignedAdmin',
            'beneficiaries',
            'formData'
        ])->findOrFail($id);

        return view('admin::service-requests.view', compact('request'));
    }

    /**
     * Update status action for the specified resource.
     */
    public function updateStatus(int $id): JsonResponse
    {
        $validatedData = $this->validate(request(), [
            'status' => 'required|string|in:pending,in_progress,completed,rejected,cancelled',
        ]);

        try {
            $this->serviceRequestRepository->updateStatus($id, $validatedData['status']);

            session()->flash('success', trans('Admin::app.service-requests.view.status-update-success'));

            return response()->json([
                'message' => trans('Admin::app.service-requests.view.status-update-success'),
            ]);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel action for the specified resource.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $this->serviceRequestRepository->cancelRequest($id);

            session()->flash('success', trans('Admin::app.service-requests.view.cancel-success'));

            return response()->json([
                'message' => trans('Admin::app.service-requests.view.cancel-success'),
            ]);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add admin notes to the request.
     */
    public function addNotes(int $id): JsonResponse
    {
        $validatedData = $this->validate(request(), [
            'admin_notes' => 'required|string',
        ]);

        try {
            $this->serviceRequestRepository->update([
                'admin_notes' => $validatedData['admin_notes'],
            ], $id);

            session()->flash('success', trans('Admin::app.service-requests.view.notes-success'));

            return response()->json([
                'message' => trans('Admin::app.service-requests.view.notes-success'),
            ]);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign request to admin.
     */
    public function assign(int $id): JsonResponse
    {
        $validatedData = $this->validate(request(), [
            'assigned_to' => 'required|integer|exists:admins,id',
        ]);

        try {
            $this->serviceRequestRepository->update([
                'assigned_to' => $validatedData['assigned_to'],
            ], $id);

            session()->flash('success', trans('Admin::app.service-requests.view.assign-success'));

            return response()->json([
                'message' => trans('Admin::app.service-requests.view.assign-success'),
            ]);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
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
     * Download document PDF for service request.
     */
    public function downloadDocument(int $id)
    {
        try {
            $serviceRequest = ServiceRequestProxy::modelClass()::with(['service.documentTemplate'])
                ->findOrFail($id);

            $documentService = new DocumentTemplateService();

            return $documentService->generateAndDownloadPDF($serviceRequest);
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());

            return redirect()->back();
        }
    }
}
