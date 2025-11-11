<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\ServiceAttributeTypeDataGrid;
use Najaz\Service\Enums\ServiceAttributeTypeEnum;
use Najaz\Service\Repositories\ServiceAttributeTypeRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Rules\Code;

class ServiceAttributeTypeController extends Controller
{
    public function __construct(
        protected ServiceAttributeTypeRepository $serviceAttributeTypeRepository
    ) {}

    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ServiceAttributeTypeDataGrid::class)->process();
        }

        return view('admin::services.attribute-types.index');
    }

    public function create(): View
    {
        $attributeTypes = ServiceAttributeTypeEnum::getValues();
        $locales = core()->getAllLocales();

        return view('admin::services.attribute-types.create', compact('attributeTypes', 'locales'));
    }

    public function store(): RedirectResponse
    {
        $this->validate(request(), [
            'code'   => ['required', 'unique:service_attribute_types,code', new Code],
            'type'   => 'required|in:' . implode(',', ServiceAttributeTypeEnum::getValues()),
            'name'   => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $data = [
            'code'            => request()->input('code'),
            'type'            => request()->input('type'),
            'is_user_defined' => 1,
        ];

        $attributeType = $this->serviceAttributeTypeRepository->create($data);

        foreach (core()->getAllLocales() as $locale) {
            $attributeType->translateOrNew($locale->code)->fill([
                'name' => request()->input("name.{$locale->code}"),
            ])->save();
        }

        session()->flash('success', trans('Admin::app.services.attribute-types.create-success'));

        return redirect()->route('admin.attribute-types.index');
    }

    public function edit(int $id): View
    {
        $attributeType = $this->serviceAttributeTypeRepository->with('translations')->findOrFail($id);
        $attributeTypes = ServiceAttributeTypeEnum::getValues();
        $locales = core()->getAllLocales();

        return view('admin::services.attribute-types.edit', compact('attributeType', 'attributeTypes', 'locales'));
    }

    public function update(int $id): RedirectResponse
    {
        $this->validate(request(), [
            'name'   => 'required|array',
            'name.*' => 'required|string|max:255',
        ]);

        $attributeType = $this->serviceAttributeTypeRepository->findOrFail($id);

        foreach (core()->getAllLocales() as $locale) {
            $attributeType->translateOrNew($locale->code)->fill([
                'name' => request()->input("name.{$locale->code}"),
            ])->save();
        }

        session()->flash('success', trans('Admin::app.services.attribute-types.update-success'));

        return redirect()->route('admin.attribute-types.index');
    }

    public function destroy(int $id): JsonResponse
    {
        $attributeType = $this->serviceAttributeTypeRepository->findOrFail($id);

        if (! $attributeType->is_user_defined) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.attribute-types.delete-default-error'),
            ], 400);
        }

        $this->serviceAttributeTypeRepository->delete($id);

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-types.delete-success'),
        ]);
    }

    public function massDestroy(): JsonResponse
    {
        $indices = request()->input('indices', []);

        $deletedCount = 0;
        $skippedCount = 0;

        foreach ($indices as $id) {
            $attributeType = $this->serviceAttributeTypeRepository->find($id);

            if ($attributeType && ! $attributeType->is_user_defined) {
                $skippedCount++;
                continue;
            }

            $this->serviceAttributeTypeRepository->delete($id);
            $deletedCount++;
        }

        if ($skippedCount > 0) {
            return new JsonResponse([
                'message' => trans('Admin::app.services.attribute-types.index.datagrid.partial-delete-success', [
                    'deleted' => $deletedCount,
                    'skipped' => $skippedCount,
                ]),
            ]);
        }

        return new JsonResponse([
            'message' => trans('Admin::app.services.attribute-types.index.datagrid.mass-delete-success'),
        ]);
    }
}


