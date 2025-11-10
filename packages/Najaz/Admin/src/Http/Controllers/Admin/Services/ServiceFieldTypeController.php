<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\ServiceFieldTypeDataGrid;
use Najaz\Service\Enums\ServiceFieldTypeEnum;
use Najaz\Service\Repositories\ServiceFieldTypeRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Rules\Code;

class ServiceFieldTypeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ServiceFieldTypeRepository $serviceFieldTypeRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ServiceFieldTypeDataGrid::class)->process();
        }

        return view('admin::services.field-types.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $fieldTypes = ServiceFieldTypeEnum::getValues();
        $locales = core()->getAllLocales();

        return view('admin::services.field-types.create', compact('fieldTypes', 'locales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): \Illuminate\Http\RedirectResponse
    {
        $this->validate(request(), [
            'code'          => ['required', 'unique:service_field_types,code', new Code],
            'type'          => 'required|in:'.implode(',', ServiceFieldTypeEnum::getValues()),
            'name'          => 'required|array',
            'name.*'        => 'required|string|max:255',
        ]);

        $data = [
            'code'            => request()->input('code'),
            'type'            => request()->input('type'),
            'is_user_defined' => 1,
        ];

        $fieldType = $this->serviceFieldTypeRepository->create($data);

        // Save translations
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'name' => request()->input("name.{$locale->code}"),
            ];

            $fieldType->translateOrNew($locale->code)->fill($translationData)->save();
        }

        session()->flash('success', trans('Admin::app.services.field-types.create-success'));

        return redirect()->route('admin.field-types.index');

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $fieldType = $this->serviceFieldTypeRepository->with('translations')->findOrFail($id);
        $fieldTypes = ServiceFieldTypeEnum::getValues();
        $locales = core()->getAllLocales();

        return view('admin::services.field-types.edit', compact('fieldType', 'fieldTypes', 'locales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): \Illuminate\Http\RedirectResponse
    {
        $this->validate(request(), [
            'name'          => 'required|array',
            'name.*'        => 'required|string|max:255',
        ]);

        $fieldType = $this->serviceFieldTypeRepository->findOrFail($id);

        // Update translations
        foreach (core()->getAllLocales() as $locale) {
            $translationData = [
                'name' => request()->input("name.{$locale->code}"),
            ];

            $fieldType->translateOrNew($locale->code)->fill($translationData)->save();
        }
        session()->flash('success', trans('Admin::app.services.field-types.update-success'));

        return redirect()->route('admin.field-types.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $fieldType = $this->serviceFieldTypeRepository->findOrFail($id);

        if (! $fieldType->is_user_defined) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.field-types.delete-default-error'),
            ], 400);
        }

        $this->serviceFieldTypeRepository->delete($id);

        return new JsonResponse([
            'message' => trans('Admin::app.services.field-types.delete-success'),
        ]);
    }

    /**
     * Mass delete field types.
     */
    public function massDestroy(): JsonResponse
    {
        $indices = request()->input('indices', []);

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($indices as $id) {
            $fieldType = $this->serviceFieldTypeRepository->find($id);

            // تخطي الحقول الثابتة
            if ($fieldType && ! $fieldType->is_user_defined) {
                $skippedCount++;

                continue;
            }

            $this->serviceFieldTypeRepository->delete($id);
            $deletedCount++;
        }

        if ($skippedCount > 0) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.field-types.index.datagrid.partial-delete-success', [
                    'deleted' => $deletedCount,
                    'skipped' => $skippedCount,
                ]),
            ], 200);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.field-types.index.datagrid.mass-delete-success'),
        ]);
    }
}
