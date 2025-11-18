<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\DocumentTemplateDataGrid;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Service\Models\Service;
use Najaz\Service\Models\ServiceDocumentTemplateProxy;
use Najaz\Service\Repositories\ServiceRepository;

class DocumentTemplateController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
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

        // Get service IDs that already have templates
        $servicesWithTemplates = ServiceDocumentTemplateProxy::modelClass()::query()
            ->pluck('service_id')
            ->toArray();

        // Filter services that don't have templates
        $servicesWithoutTemplates = $allServices->reject(function ($service) use ($servicesWithTemplates) {
            return in_array($service->id, $servicesWithTemplates);
        })->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
            ];
        })->values();

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

        $service = $this->serviceRepository->findOrFail(request()->input('service_id'));

        // Check if service already has a template
        $existingTemplate = ServiceDocumentTemplateProxy::modelClass()::query()
            ->where('service_id', $service->id)
            ->first();

        if ($existingTemplate) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.document-templates.service-already-has-template'),
                'error' => true,
            ], 422);
        }

        // Create empty template
        $template = ServiceDocumentTemplateProxy::modelClass()::create([
            'service_id'       => $service->id,
            'template_content' => '',
            'used_fields'       => [],
            'header_image'     => null,
            'footer_text'      => null,
            'is_active'        => true,
        ]);

        // Build available fields list
        $currentLocale = app()->getLocale();
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);
        $template->available_fields = $availableFields;
        $template->save();

        return new JsonResponse([
            'message' => trans('Admin::app.services.document-templates.create-success'),
            'data'    => $template->fresh(),
            'redirect' => route('admin.services.document-templates.edit', $template->id),
        ]);
    }

    /**
     * Show the form for editing the specified document template.
     */
    public function edit(int $id): View
    {
        $template = ServiceDocumentTemplateProxy::modelClass()::with('service')->findOrFail($id);
        $service = $template->service;

        // Build available fields list
        $currentLocale = app()->getLocale();
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);

        return view('admin::services.document-templates.edit', [
            'template' => $template,
            'service' => $service,
            'availableFields' => $availableFields,
        ]);
    }

    /**
     * Update the specified document template.
     */
    public function update(int $id): JsonResponse
    {
        $template = ServiceDocumentTemplateProxy::modelClass()::findOrFail($id);

        $this->validate(request(), [
            'template_content' => 'required|string',
            'used_fields'     => 'nullable|array',
            'header_image'    => 'nullable|string|max:2048',
            'footer_text'     => 'nullable|string|max:500',
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

        $template->update([
            'template_content' => request()->input('template_content'),
            'used_fields'      => $usedFields,
            'header_image'     => request()->input('header_image'),
            'footer_text'      => request()->input('footer_text'),
            'is_active'        => request()->input('is_active', true),
        ]);

        // Update available fields list
        $service = $template->service;
        $currentLocale = app()->getLocale();
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);
        $template->available_fields = $availableFields;
        $template->save();

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
        $template = ServiceDocumentTemplateProxy::modelClass()::findOrFail($id);
        $template->delete();

        return new JsonResponse([
            'message' => trans('Admin::app.services.document-templates.delete-success'),
        ]);
    }

    /**
     * Build available fields list for document template.
     */
    protected function buildAvailableFieldsForTemplate(Service $service, string $locale): array
    {
        $fields = [];

        // Citizen fields
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.fields.citizen'),
            'code' => 'citizen_first_name',
            'label' => trans('Admin::app.services.document-templates.fields.citizen_first_name'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.fields.citizen'),
            'code' => 'citizen_middle_name',
            'label' => trans('Admin::app.services.document-templates.fields.citizen_middle_name'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.fields.citizen'),
            'code' => 'citizen_last_name',
            'label' => trans('Admin::app.services.document-templates.fields.citizen_last_name'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.fields.citizen'),
            'code' => 'citizen_national_id',
            'label' => trans('Admin::app.services.document-templates.fields.citizen_national_id'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.fields.citizen'),
            'code' => 'citizen_type_name',
            'label' => trans('Admin::app.services.document-templates.fields.citizen_type_name'),
        ];

        // Request fields
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.fields.request'),
            'code' => 'request_increment_id',
            'label' => trans('Admin::app.services.document-templates.fields.request_increment_id'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.fields.request'),
            'code' => 'request_date',
            'label' => trans('Admin::app.services.document-templates.fields.request_date'),
        ];
        $fields[] = [
            'group' => trans('Admin::app.services.document-templates.fields.request'),
            'code' => 'current_date',
            'label' => trans('Admin::app.services.document-templates.fields.current_date'),
        ];

        // Service attribute group fields
        $service->load('attributeGroups.fields');
        foreach ($service->attributeGroups as $group) {
            $groupCode = $group->pivot->custom_code ?? $group->code;
            $groupTranslation = $group->translate($locale);
            $groupName = $group->pivot->custom_name ?? ($groupTranslation?->name ?? $group->code ?? $groupCode);

            foreach ($group->fields as $field) {
                $fieldCode = $field->code;
                $fieldTranslation = $field->translate($locale);
                $fieldLabel = $fieldTranslation?->label ?? $fieldCode;

                // Add nested field (group.field)
                $fields[] = [
                    'group' => $groupName,
                    'code' => $groupCode . '.' . $fieldCode,
                    'label' => $groupName . ' - ' . $fieldLabel,
                ];
            }
        }

        return $fields;
    }
}

