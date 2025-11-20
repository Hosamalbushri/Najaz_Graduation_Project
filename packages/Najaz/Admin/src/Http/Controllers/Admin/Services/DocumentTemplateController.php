<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\DocumentTemplateDataGrid;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Service\Repositories\DocumentTemplateRepository;
use Najaz\Service\Repositories\ServiceRepository;

class DocumentTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
        protected DocumentTemplateRepository $documentTemplateRepository,
    ) {}

    /**
     * Display a listing of document templates.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(DocumentTemplateDataGrid::class)->process();
        }

        // Get all services
        $allServices = $this->serviceRepository->all();

        // Get services that don't have templates
        $servicesWithoutTemplates = $this->documentTemplateRepository->getServicesWithoutTemplates($allServices);

        return view('admin::services.document-templates.index', [
            'services' => $servicesWithoutTemplates,
        ]);
    }

    /**
     * Store a newly created document template.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'service_id' => 'required|exists:services,id',
        ]);

        $serviceId = request()->input('service_id');

        // Check if service already has a template
        if ($this->documentTemplateRepository->serviceHasTemplate($serviceId)) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.document-templates.service-already-has-template'),
                'error'   => true,
            ], 422);
        }

        // Create template
        $template = $this->documentTemplateRepository->createTemplate($serviceId);

        return new JsonResponse([
            'message'  => trans('Admin::app.services.document-templates.create-success'),
            'data'     => $template->fresh(),
            'redirect' => route('admin.services.document-templates.edit', $template->id),
        ]);
    }

    /**
     * Show the form for editing the specified document template.
     */
    public function edit(int $id): View
    {
        $template = $this->documentTemplateRepository->with('service')->findOrFail($id);
        $service = $template->service;

        // Build available fields list
        $currentLocale = app()->getLocale();
        $availableFields = $this->documentTemplateRepository->buildAvailableFieldsForTemplate($service, $currentLocale);

        return view('admin::services.document-templates.edit', [
            'template'        => $template,
            'service'         => $service,
            'availableFields' => $availableFields,
        ]);
    }

    /**
     * Update the specified document template.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'template_content' => 'required|string',
            'used_fields'     => 'nullable|array',
            'header_image'    => 'nullable',
            'footer_text'     => 'nullable|string',
            'is_active'       => 'nullable|boolean',
        ]);

        // Get used_fields and ensure it's an array
        $usedFields = request()->input('used_fields', []);
        if (is_string($usedFields)) {
            $usedFields = json_decode($usedFields, true) ?? [];
        }
        if (! is_array($usedFields)) {
            $usedFields = [];
        }
        $data = request()->only([
            'template_content',
            'used_fields'=>$usedFields,
            'header_image',
            'footer_text',
            'is_active',
        ]);

        $template = $this->documentTemplateRepository->updateTemplateWithAvailableFields($id, $data);

        return new JsonResponse([
            'message' => trans('Admin::app.services.document-templates.update-success'),
            'data'    => $template->fresh(),
        ]);
    }

    /**
     * Remove the specified document template.
     */
    public function destroy(int $id): JsonResponse
    {
        $template = $this->documentTemplateRepository->findOrFail($id);
        $template->delete();

        return new JsonResponse([
            'message' => trans('Admin::app.services.document-templates.delete-success'),
        ]);
    }
}
