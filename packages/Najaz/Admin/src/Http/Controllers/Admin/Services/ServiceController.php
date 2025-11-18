<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\ServiceDataGrid;
use Najaz\Citizen\Models\CitizenTypeProxy;
use Najaz\Service\Models\Service;
use Najaz\Service\Models\ServiceAttributeGroupProxy;
use Najaz\Service\Models\ServiceDocumentTemplateProxy;
use Najaz\Service\Repositories\ServiceRepository;
use Najaz\Admin\Http\Controllers\Controller;

class ServiceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceRepository $serviceRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ServiceDataGrid::class)->process();
        }

        return view('admin::services.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $currentLocale = app()->getLocale();

        $attributeGroups = ServiceAttributeGroupProxy::modelClass()::with([
            'translations',
            'fields.translations',
            'fields.attributeType.translations',
        ])->orderBy('sort_order')->get();

        return view('admin::services.create', [
            'attributeGroupOptions'        => $this->formatAttributeGroupsForFrontend($attributeGroups, $currentLocale),
            'serviceGroupInitialSelection' => $this->buildServiceGroupInitialSelection(
                $this->prepareServiceGroupsForFrontend(null, $currentLocale)
            ),
            'citizenTypeTree'              => $this->buildCitizenTypeTree(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
            'image'       => 'nullable|string|max:2048',
            'sort_order'  => 'nullable|integer|min:0',
            'citizen_type_ids'   => 'nullable|array',
            'citizen_type_ids.*' => 'integer|exists:citizen_types,id',
        ]);

        $data = request()->only([
            'name',
            'description',
            'status',
            'image',
            'sort_order',
        ]);

        $service = $this->serviceRepository->create($data);

        $this->serviceRepository->syncAttributeGroups(
            request()->input('service_attribute_groups'),
            $service
        );

        $this->serviceRepository->syncCitizenTypes(request()->input('citizen_type_ids', []), $service);


        return new JsonResponse([
            'message' => trans('Admin::app.services.services.create-success'),
            'redirect_to' => route('admin.services.edit', $service->id),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $currentLocale = app()->getLocale();

        $service = $this->serviceRepository->with([
            'attributeGroups.translations',
            'attributeGroups.fields.translations',
            'attributeGroups.fields.attributeType.translations',
            'citizenTypes',
        ])->findOrFail($id);

        $attributeGroups = ServiceAttributeGroupProxy::modelClass()::with([
            'translations',
            'fields.translations',
            'fields.attributeType.translations',
        ])->orderBy('sort_order')->get();

        $documentTemplate = $service->documentTemplate;

        // Build available fields list from service attribute groups
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);

        return view('admin::services.edit', [
            'service'          => $service,
            'documentTemplate' => $documentTemplate,
            'availableFields'  => $availableFields,
            'attributeGroupOptions'        => $this->formatAttributeGroupsForFrontend($attributeGroups, $currentLocale),
            'serviceGroupInitialSelection' => $this->buildServiceGroupInitialSelection(
                $this->prepareServiceGroupsForFrontend($service, $currentLocale)
            ),
            'citizenTypeTree'  => $this->buildCitizenTypeTree(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'nullable|boolean',
            'image'       => 'nullable|string|max:2048',
            'sort_order'  => 'nullable|integer|min:0',
            'citizen_type_ids'   => 'nullable|array',
            'citizen_type_ids.*' => 'integer|exists:citizen_types,id',
        ]);

        $data = request()->only([
            'name',
            'description',
            'status',
            'image',
            'sort_order',
        ]);

        $service = $this->serviceRepository->update($data, $id);

        $this->serviceRepository->syncAttributeGroups(
            request()->input('service_attribute_groups'),
            $service
        );

        $this->serviceRepository->syncCitizenTypes(request()->input('citizen_type_ids', []), $service);


        return new JsonResponse([
            'message' => trans('Admin::app.services.services.update-success'),
            'data'    => $service->fresh(['attributeGroups', 'citizenTypes']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->serviceRepository->findOrFail($id);

        $this->serviceRepository->delete($id);

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.delete-success'),
        ]);
    }



    protected function buildCitizenTypeTree(): array
    {
        return CitizenTypeProxy::modelClass()::orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($type) => [
                'id'       => $type->id,
                'key'      => (string) $type->id,
                'name'     => $type->name,
                'children' => [],
            ])
            ->values()
            ->toArray();
    }

    /**
     * Mass delete services.
     */
    public function massDestroy(): JsonResponse
    {
        $indices = request()->input('indices', []);

        foreach ($indices as $id) {
            $this->serviceRepository->delete($id);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.index.datagrid.mass-delete-success'),
        ]);
    }

    /**
     * Mass update services.
     */
    public function massUpdate(): JsonResponse
    {
        $indices = request()->input('indices', []);
        $value = request()->input('value');

        foreach ($indices as $id) {
            $this->serviceRepository->update([
                'status' => $value,
            ], $id);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.index.datagrid.mass-update-success'),
        ]);
    }

    protected function formatAttributeGroupsForFrontend(Collection $attributeGroups, string $locale): array
    {
        return $attributeGroups
            ->filter(fn ($group) => ($group->fields ?? collect())->count())
            ->map(function ($group) use ($locale) {
                $translation = $group->translate($locale);
                $supportsNotification = $group->group_type === 'citizen'
                    && ($group->fields ?? collect())->contains(
                        fn ($field) => strtolower($field->code ?? '') === 'id_number'
                    );

                return [
                    'id'                    => $group->id,
                    'code'                  => $group->code,
                    'group_type'            => $group->group_type ?? 'general',
                    'name'                  => $translation?->name ?? $group->code,
                    'description'           => $translation?->description,
                    'sort_order'            => $group->sort_order ?? 0,
                    'is_notifiable'         => false,
                    'supports_notification' => $supportsNotification,
                    'fields'                => $group->fields->map(function ($field) use ($locale) {
                        $fieldTranslation = $field->translate($locale);
                        $attributeType = $field->attributeType;
                        $attributeTypeTranslation = $attributeType?->translate($locale);

                        return [
                            'id'                  => $field->id,
                            'code'                => $field->code,
                            'label'               => $fieldTranslation?->label ?? $field->code,
                            'type'                => $field->type,
                            'attribute_type_name' => $attributeTypeTranslation?->name ?? $attributeType?->code ?? '',
                            'sort_order'          => $field->sort_order ?? 0,
                        ];
                    })->values(),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function prepareServiceGroupsForFrontend(?Service $service, string $locale): Collection
    {
        $serviceGroups = ($service?->attributeGroups ?? collect())
            ->filter(fn ($group) => ($group->fields ?? collect())->count())
            ->map(function ($group) use ($locale) {
                $translation = $group->translate($locale);
                $supportsNotification = $group->group_type === 'citizen'
                    && ($group->fields ?? collect())->contains(
                        fn ($field) => strtolower($field->code ?? '') === 'id_number'
                    );

                return [
                    'service_attribute_group_id' => $group->id,
                    'template_id'                => $group->id,
                    'code'                       => $group->pivot->custom_code ?? $group->code,
                    'group_type'                 => $group->group_type ?? 'general',
                    'name'                       => $group->pivot->custom_name ?? ($translation?->name ?? $group->code),
                    'description'                => $translation?->description,
                    'sort_order'                 => $group->pivot->sort_order ?? 0,
                    'is_notifiable'              => (bool) ($group->pivot->is_notifiable ?? false),
                    'supports_notification'      => $supportsNotification,
                    'pivot_uid'                  => $group->pivot->pivot_uid ?? '',
                    'fields'                     => $group->fields->map(function ($field) use ($locale) {
                        $fieldTranslation = $field->translate($locale);
                        $attributeType = $field->attributeType;
                        $attributeTypeTranslation = $attributeType?->translate($locale);

                        return [
                            'service_attribute_field_id' => $field->id,
                            'template_field_id'          => $field->id,
                            'code'                       => $field->code,
                            'label'                      => $fieldTranslation?->label ?? $field->code,
                            'type'                       => $field->type,
                            'attribute_type_name'        => $attributeTypeTranslation?->name ?? $attributeType?->code ?? '',
                            'sort_order'                 => $field->sort_order ?? 0,
                        ];
                    })->values()->toArray(),
                ];
            })->values();

        $oldGroupsInput = old('service_attribute_groups');

        if (is_array($oldGroupsInput)) {
            $serviceGroups = collect($oldGroupsInput)->map(function ($group, $index) {
                $groupId = isset($group['service_attribute_group_id']) ? (int) $group['service_attribute_group_id'] : 0;

                if (! $groupId) {
                    return null;
                }

                $fields = collect($group['fields'] ?? [])->filter();

                if ($fields->isEmpty()) {
                    return null;
                }

                return [
                    'service_attribute_group_id' => $groupId,
                    'template_id'                => isset($group['template_id']) ? (int) $group['template_id'] : $groupId,
                    'code'                       => $group['code'] ?? null,
                    'group_type'                 => $group['group_type'] ?? 'general',
                    'name'                       => $group['name'] ?? null,
                    'description'                => $group['description'] ?? null,
                    'sort_order'                 => isset($group['sort_order']) ? (int) $group['sort_order'] : $index,
                    'is_notifiable'              => ! empty($group['is_notifiable']),
                    'fields'                     => $fields->values(),
                    'supports_notification'      => ! empty($group['supports_notification']),
                    'pivot_uid'                  => $group['pivot_uid'] ?? '',
                ];
            })->filter()->values();
        }

        return $serviceGroups;
    }

    protected function buildServiceGroupInitialSelection(Collection $serviceGroups): array
    {
        return [
            'groups' => $serviceGroups->values()->toArray(),
            'fields' => [],
        ];
    }

    /**
     * Store or update document template for a service.
     */
    public function storeDocumentTemplate(int $id): JsonResponse
    {
        $this->validate(request(), [
            'template_content' => 'required|string',
            'used_fields'     => 'nullable|array',
            'header_image'    => 'nullable|string|max:2048',
            'footer_text'     => 'nullable|string|max:500',
            'is_active'       => 'nullable|boolean',
        ]);

        $service = $this->serviceRepository->findOrFail($id);

        // Get used_fields and ensure it's an array
        $usedFields = request()->input('used_fields', []);
        
        // Debug: Log the received data
        \Log::info('Received used_fields:', ['used_fields' => $usedFields, 'type' => gettype($usedFields)]);
        
        // Handle JSON string if sent as string
        if (is_string($usedFields)) {
            $usedFields = json_decode($usedFields, true) ?? [];
        }
        
        if (! is_array($usedFields)) {
            $usedFields = [];
        }

        \Log::info('Processed used_fields:', ['used_fields' => $usedFields]);

        $template = ServiceDocumentTemplateProxy::modelClass()::updateOrCreate(
            ['service_id' => $service->id],
            [
                'template_content' => request()->input('template_content'),
                'used_fields'      => $usedFields,
                'header_image'     => request()->input('header_image'),
                'footer_text'      => request()->input('footer_text'),
                'is_active'        => request()->input('is_active', true),
            ]
        );

        \Log::info('Template saved:', ['template_id' => $template->id, 'used_fields' => $template->used_fields]);

        // Build available fields list
        $currentLocale = app()->getLocale();
        $availableFields = $this->buildAvailableFieldsForTemplate($service, $currentLocale);
        $template->available_fields = $availableFields;
        $template->save();

        return new JsonResponse([
            'message' => trans('Admin::app.services.services.document-template.save-success'),
            'data'    => $template->fresh(),
        ]);
    }

    /**
     * Build available fields list for document template.
     */
    protected function buildAvailableFieldsForTemplate(Service $service, string $locale): array
    {
        $service->load(['attributeGroups.fields.translations', 'attributeGroups.translations']);

        $fields = [];

        // Add citizen basic fields
        $fields[] = [
            'code'  => 'citizen_first_name',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_first_name'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'citizen_middle_name',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_middle_name'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'citizen_last_name',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_last_name'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'citizen_national_id',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_national_id'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'citizen_type_name',
            'label' => trans('Admin::app.services.services.document-template.fields.citizen_type_name'),
            'group' => 'citizen',
        ];
        $fields[] = [
            'code'  => 'request_increment_id',
            'label' => trans('Admin::app.services.services.document-template.fields.request_increment_id'),
            'group' => 'request',
        ];
        $fields[] = [
            'code'  => 'request_date',
            'label' => trans('Admin::app.services.services.document-template.fields.request_date'),
            'group' => 'request',
        ];
        $fields[] = [
            'code'  => 'current_date',
            'label' => trans('Admin::app.services.services.document-template.fields.current_date'),
            'group' => 'system',
        ];

        // Add fields from service attribute groups
        foreach ($service->attributeGroups as $group) {
            $groupCode = $group->pivot->custom_code ?? $group->code;
            $groupTranslation = $group->translate($locale);
            $groupName = $group->pivot->custom_name ?? ($groupTranslation?->name ?? $group->code);

            foreach ($group->fields as $field) {
                $fieldTranslation = $field->translate($locale);
                $fieldLabel = $fieldTranslation?->label ?? $field->code;

                $fields[] = [
                    'code'  => $groupCode . '.' . $field->code,
                    'label' => $groupName . ' - ' . $fieldLabel,
                    'group' => $groupCode,
                ];

                // Also add flat field code
                $fields[] = [
                    'code'  => $field->code,
                    'label' => $fieldLabel,
                    'group' => $groupCode,
                ];
            }
        }

        return $fields;
    }
}
