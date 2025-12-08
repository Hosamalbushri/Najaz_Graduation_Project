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
        $template = $this->documentTemplateRepository->with(['service', 'translations'])->findOrFail($id);
        $service = $template->service;

        // Load service with attribute groups and translations (same as ServiceController::edit)
        $service = $this->serviceRepository
            ->withAttributeGroupsForEdit()
            ->with(['translations', 'citizenTypes'])
            ->findOrFail($service->id);

        // Load pivot relations with translations and fields (eager loading)
        $pivotIds = $service->attributeGroups->pluck('pivot.id')->filter();
        if ($pivotIds->isNotEmpty()) {
            $pivotRelations = \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'translations',
                'fields.translations',
                'fields.attributeType.translations',
                'fields.options.translations',
            ])->whereIn('id', $pivotIds)->get()->keyBy('id');

            // Attach loaded pivot relations to groups (same as ServiceRepository::findForEdit)
            foreach ($service->attributeGroups as $group) {
                $pivotId = $group->pivot->id ?? null;
                if ($pivotId && isset($pivotRelations[$pivotId])) {
                    // Replace pivot with loaded relation
                    $group->setRelation('pivot', $pivotRelations[$pivotId]);
                }
            }
        }

        return view('admin::services.document-templates.edit', [
            'template' => $template,
            'service'  => $service,
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
            'locale'          => 'required|string',
        ]);

        // Get current locale
        $locale = request()->input('locale', app()->getLocale());

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
            'used_fields' => $usedFields,
            'header_image',
            'footer_text',
            'is_active',
        ]);

        $data['locale'] = $locale;

        $template = $this->documentTemplateRepository->updateTemplateWithAvailableFields($id, $data);

        return new JsonResponse([
            'message' => trans('Admin::app.services.document-templates.update-success'),
            'data'    => $template->fresh(['translations']),
        ]);
    }

    /**
     * Get available fields for template based on locale.
     */
    public function getAvailableFields(int $id): JsonResponse
    {
        $template = $this->documentTemplateRepository->with('service')->findOrFail($id);
        $service = $template->service;

        // Get locale from request or use default
        $localeCode = request()->input('locale', app()->getLocale());
        $locale = core()->getAllLocales()->firstWhere('code', $localeCode) 
            ?? core()->getCurrentLocale();

        // Build available fields list
        $availableFields = $this->documentTemplateRepository->buildAvailableFieldsForTemplate($service, $locale->code);

        return new JsonResponse([
            'availableFields' => $availableFields,
            'locale'          => $locale->code,
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
