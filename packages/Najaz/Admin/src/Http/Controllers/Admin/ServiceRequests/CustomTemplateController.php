<?php

namespace Najaz\Admin\Http\Controllers\Admin\ServiceRequests;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Request\Repositories\ServiceRequestCustomTemplateRepository;
use Najaz\Request\Repositories\ServiceRequestRepository;

class CustomTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected ServiceRequestRepository $serviceRequestRepository,
        protected ServiceRequestCustomTemplateRepository $customTemplateRepository
    ) {}

    /**
     * Copy template content from the original service template.
     */
    public function copyFromOriginal(int $id): JsonResponse
    {
        try {
            $serviceRequest = $this->serviceRequestRepository->with(['service.documentTemplate'])
                ->findOrFail($id);

            $locale = request()->input('locale', $serviceRequest->locale ?? app()->getLocale());

            $customTemplate = $this->customTemplateRepository->copyFromOriginalTemplate($serviceRequest, $locale);

            return response()->json([
                'success' => true,
                'message' => trans('Admin::app.service-requests.custom-template.copy-success'),
                'data' => $customTemplate,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store or update custom template.
     */
    public function store(int $id): JsonResponse
    {
        try {
            $this->validate(request(), [
                'template_content' => 'nullable|string',
            ]);

            $serviceRequest = $this->serviceRequestRepository->findOrFail($id);
            $locale = request()->input('locale', $serviceRequest->locale ?? app()->getLocale());

            $data = [
                'template_content' => request()->input('template_content'),
            ];

            $customTemplate = $this->customTemplateRepository->createOrUpdate($id, $locale, $data);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => trans('Admin::app.service-requests.custom-template.success'),
                    'data' => $customTemplate,
                ]);
            }

            session()->flash('success', trans('Admin::app.service-requests.custom-template.success'));

            return response()->json([
                'success' => true,
                'redirect' => route('admin.service-requests.view', $id),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save custom template', [
                'service_request_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => trans('Admin::app.service-requests.custom-template.error'),
            ], 500);
        }
    }

    /**
     * Get uploaded files for the service request.
     */
    public function getUploadedFiles(int $id): JsonResponse
    {
        try {
            $serviceRequest = $this->serviceRequestRepository->with([
                'formData',
                'service.attributeGroups.fields.attributeType',
            ])->findOrFail($id);

            $files = $this->customTemplateRepository->getUploadedFiles($serviceRequest);

            return response()->json([
                'success' => true,
                'data' => $files,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview a specific file.
     */
    public function previewFile(int $id, string $fieldCode)
    {
        try {
            $serviceRequest = $this->serviceRequestRepository->with(['formData'])
                ->findOrFail($id);

            $filePath = null;

            // Find the file in form data
            foreach ($serviceRequest->formData as $formData) {
                if ($formData->fields_data && is_array($formData->fields_data)) {
                    if (isset($formData->fields_data[$fieldCode])) {
                        $filePath = $formData->fields_data[$fieldCode];
                        break;
                    }
                }
            }

            if (! $filePath || ! Storage::exists($filePath)) {
                abort(404, 'File not found');
            }

            $mimeType = Storage::mimeType($filePath);
            $fileName = basename($filePath);

            return response()->stream(function () use ($filePath) {
                echo Storage::get($filePath);
            }, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="'.$fileName.'"',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to preview file', [
                'service_request_id' => $id,
                'field_code' => $fieldCode,
                'error' => $e->getMessage(),
            ]);

            abort(500, 'Failed to preview file');
        }
    }
}

