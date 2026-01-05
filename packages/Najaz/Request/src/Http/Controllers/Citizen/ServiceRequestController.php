<?php

namespace Najaz\Request\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use Najaz\Admin\Traits\PDFHandler;
use Najaz\Request\Models\ServiceRequestProxy;
use Najaz\Request\Repositories\ServiceRequestRepository;
use Najaz\Service\Services\DocumentTemplateService;
use Webkul\GraphQLAPI\Validators\CustomException;

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
    ) {}

    /**
     * Print service request document (PDF).
     * Only available for completed requests.
     * Accessible by requester or beneficiaries.
     */
    public function printDocument(int $id)
    {
        try {
            // Get authenticated citizen
            $citizen = auth('citizen-api')->user();

            if (! $citizen) {
                abort(401, trans('najaz_graphql::app.citizens.auth.unauthenticated'));
            }

            // Load service request with relationships
            $serviceRequest = ServiceRequestProxy::modelClass()::with(['service.documentTemplate', 'beneficiaries'])
                ->findOrFail($id);

            // Check if citizen has access (requester or beneficiary)
            if (! $this->serviceRequestRepository->canCitizenAccess($serviceRequest, $citizen)) {
                abort(403, trans('najaz_graphql::app.citizens.service_request.not_found'));
            }

            // Check if request is completed
            if ($serviceRequest->status !== 'completed' || ! $serviceRequest->completed_at) {
                abort(400, trans('najaz_graphql::app.citizens.service_request.document_not_available'));
            }

            // Check if template exists and is active
            $template = $serviceRequest->service->documentTemplate;
            if (! $template || ! $template->is_active) {
                abort(404, trans('najaz_graphql::app.citizens.service_request.template_not_found'));
            }

            // Generate document content using DocumentTemplateService
            $documentService = new DocumentTemplateService;
            $content = $documentService->generateDocumentContent($serviceRequest);

            // Generate and download PDF
            return $this->downloadPDF(
                view('admin::service-requests.pdf', compact('serviceRequest', 'content'))->render(),
                'document-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y')
            );
        } catch (\Exception $e) {
            if ($e instanceof CustomException || $e->getCode() >= 400) {
                abort($e->getCode() ?: 500, $e->getMessage());
            }

            abort(500, trans('najaz_graphql::app.citizens.service_request.print_error', ['error' => $e->getMessage()]));
        }
    }
}

