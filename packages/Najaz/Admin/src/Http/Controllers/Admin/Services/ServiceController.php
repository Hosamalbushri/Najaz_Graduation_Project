<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\ServiceDataGrid;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Admin\Http\Requests\ServiceForm;
use Najaz\Citizen\Models\CitizenTypeProxy;
use Najaz\Service\Models\Service;
use Najaz\Service\Models\ServiceAttributeGroupProxy;
use Najaz\Service\Models\ServiceAttributeGroupService;
use Najaz\Service\Models\ServiceDocumentTemplateProxy;
use Najaz\Service\Repositories\ServiceRepository;

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

        return view('admin::services.services.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin::services.services.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceForm $serviceForm): RedirectResponse
    {
        $locale = core()->getRequestedLocaleCode();

        $data = $serviceForm->only([
            'category_id',
            'status',
            'image',
            'sort_order',
            'citizen_type_ids',
        ]);

        $data['locale'] = $locale;
        $data[$locale] = $serviceForm->input($locale, []);

        // If no locale-specific data, use direct input
        if (empty($data[$locale])) {
            $data[$locale] = [
                'name'        => $serviceForm->input('name'),
                'description' => $serviceForm->input('description'),
            ];
        }

        $service = $this->serviceRepository->create($data);

        $this->serviceRepository->syncCitizenTypes($serviceForm->input('citizen_type_ids', []), $service);

        session()->flash('success', trans('Admin::app.services.services.create-success'));

        return redirect()->route('admin.services.edit', $service->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $service = $this->serviceRepository
            ->withAttributeGroupsForEdit()
            ->with(['translations', 'citizenTypes'])
            ->findOrFail($id);

        // Load pivot relations with translations and fields (eager loading)
        $pivotIds = $service->attributeGroups->pluck('pivot.id')->filter();
        if ($pivotIds->isNotEmpty()) {
            \Najaz\Service\Models\ServiceAttributeGroupService::with([
                'translations',
                'fields.translations',
                'fields.attributeType.translations',
                'fields.options.translations',
            ])->whereIn('id', $pivotIds)->get();
        }

        return view('admin::services.services.edit', compact('service'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceForm $serviceForm, int $id): RedirectResponse
    {
        $locale = core()->getRequestedLocaleCode();

        $data = $serviceForm->only([
            'category_id',
            'status',
            'image',
            'sort_order',
            'citizen_type_ids',
        ]);

        $data['locale'] = $locale;
        $data[$locale] = $serviceForm->input($locale, []);

        $service = $this->serviceRepository->update($data, $id);

        $this->serviceRepository->syncCitizenTypes($serviceForm->input('citizen_type_ids', []), $service);

        session()->flash('success', trans('Admin::app.services.services.update-success'));

        return redirect()->route('admin.services.edit', $service->id);
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
     * Uses custom service fields (service_attribute_group_service_fields) instead of template fields.
     */
    protected function buildAvailableFieldsForTemplate(Service $service, string $locale): array
    {
        return $this->serviceRepository->getAvailableFieldsForTemplate($service, $locale);
    }

    /**
     * Result of search services.
     */
    public function search(): JsonResponse
    {
        $query = trim(request()->input('query'));

        if (empty($query)) {
            return response()->json([
                'data' => [],
            ]);
        }

        $locale = app()->getLocale();

        $services = $this->serviceRepository->scopeQuery(function ($q) use ($query, $locale) {
            return $q->whereHas('translations', function ($translationQuery) use ($query, $locale) {
                $translationQuery->where('locale', $locale)
                    ->where(function ($subQuery) use ($query) {
                        $subQuery->where('name', 'like', "%{$query}%")
                            ->orWhere('description', 'like', "%{$query}%");
                    });
            })->orderBy('created_at', 'desc');
        })->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])->paginate(10);

        foreach ($services as $key => $service) {
            $translation = $service->translate($locale);
            $services[$key]['name'] = $translation?->name ?? '';
            $services[$key]['image_url'] = $service->image ? \Storage::url($service->image) : null;
        }

        return response()->json($services);
    }

}
