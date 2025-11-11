<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\ServiceDataGrid;
use Najaz\Service\Models\ServiceAttributeGroupProxy;
use Najaz\Service\Repositories\ServiceRepository;
use Webkul\Admin\Http\Controllers\Controller;

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
        $attributeGroups = ServiceAttributeGroupProxy::modelClass()::with([
            'translations',
            'fields.translations',
            'fields.attributeType.translations',
        ])->orderBy('sort_order')->get();

        return view('admin::services.create', [
            'attributeGroups' => $attributeGroups,
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
            'image'       => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ]);

        $data = request()->only([
            'name',
            'description',
            'price',
            'status',
            'image',
            'sort_order',
        ]);

        $service = $this->serviceRepository->create($data);


        return new JsonResponse([
            'message' => trans('Admin::app.services.services.create-success'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $service = $this->serviceRepository->with([
            'attributeGroups',
        ])->findOrFail($id);

        $attributeGroups = ServiceAttributeGroupProxy::modelClass()::with([
            'translations',
            'fields.translations',
            'fields.attributeType.translations',
        ])->orderBy('sort_order')->get();

        return view('admin::services.edit', [
            'service'          => $service,
            'attributeGroups'  => $attributeGroups,
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
            'image'       => 'nullable|string',
            'sort_order'  => 'nullable|integer',
        ]);

        $data = request()->only([
            'name',
            'description',
            'price',
            'status',
            'image',
            'sort_order',
        ]);

        $service = $this->serviceRepository->update($data, $id);


        return new JsonResponse([
            'message' => trans('Admin::app.services.services.update-success'),
            'data'    => $service->fresh(['attributeGroups']),
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

    /**
     * Returns the customizable options of the service.
     */
    public function customizableOptions(int $id): JsonResponse
    {
        $service = $this->serviceRepository->findOrFail($id);

        return new JsonResponse([
            'data' => $service->customizable_options()->get(),
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
}
