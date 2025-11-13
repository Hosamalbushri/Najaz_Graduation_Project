<?php

namespace Najaz\Admin\Http\Controllers\Admin\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Services\ServiceAttributeTypeDataGrid;
use Najaz\Service\Enums\ServiceAttributeTypeEnum;
use Najaz\Service\Enums\ValidationEnum;
use Najaz\Service\Repositories\ServiceAttributeTypeOptionRepository;
use Najaz\Service\Repositories\ServiceAttributeTypeRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Rules\Code;

class ServiceAttributeTypeController extends Controller
{
    public function __construct(
        protected ServiceAttributeTypeRepository $serviceAttributeTypeRepository,
        protected ServiceAttributeTypeOptionRepository $serviceAttributeTypeOptionRepository
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
        $validations = ValidationEnum::getValues();

        return view('admin::services.attribute-types.create', compact('attributeTypes', 'locales', 'validations'));
    }

    public function store(): RedirectResponse
    {
        if (request()->input('position') === '') {
            request()->merge(['position' => null]);
        }

        if (! request()->filled('validation')) {
            request()->merge(['validation' => null]);
        }

        $this->validate(request(), [
            'code'   => ['required', 'unique:service_attribute_types,code', new Code],
            'type'   => 'required|in:' . implode(',', ServiceAttributeTypeEnum::getValues()),
            'default_name' => 'required|string|max:255',
            'name'   => 'required|array',
            'name.*' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0',
            'default_value' => 'nullable|string',
            'validation' => 'nullable|in:' . implode(',', ValidationEnum::getValues()),
            'regex' => 'nullable|required_if:validation,regex|string',
            'is_required' => 'nullable|boolean',
            'is_unique' => 'nullable|boolean',
        ]);

        $type = request()->input('type');

        if ($this->requiresOptions($type)) {
            $this->validateOptions();
        }

        $validation = request()->input('validation');
        $regex = $validation === 'regex' ? request()->input('regex') : null;

        $data = [
            'code'            => request()->input('code'),
            'type'            => request()->input('type'),
            'default_name'    => request()->input('default_name'),
            'is_user_defined' => 1,
            'position'        => request()->input('position'),
            'default_value'   => request()->input('default_value'),
            'validation'      => $validation,
            'regex'           => $regex,
            'is_required'     => request()->boolean('is_required'),
            'is_unique'       => request()->boolean('is_unique'),
        ];

        $attributeType = $this->serviceAttributeTypeRepository->create($data);

        foreach (core()->getAllLocales() as $locale) {
            $attributeType->translateOrNew($locale->code)->fill([
                'name' => request()->input("name.{$locale->code}"),
            ])->save();
        }

        if ($this->requiresOptions($type)) {
            $this->serviceAttributeTypeOptionRepository->syncOptions(
                $attributeType,
                request()->input('options', [])
            );
        }

        session()->flash('success', trans('Admin::app.services.attribute-types.create-success'));

        return redirect()->route('admin.attribute-types.index');
    }

    public function edit(int $id): View
    {
        $attributeType = $this->serviceAttributeTypeRepository
            ->with(['translations', 'options.translations'])
            ->findOrFail($id);
        $attributeTypes = ServiceAttributeTypeEnum::getValues();
        $locales = core()->getAllLocales();
        $validations = ValidationEnum::getValues();

        return view('admin::services.attribute-types.edit', compact('attributeType', 'attributeTypes', 'locales', 'validations'));
    }

    public function update(int $id): RedirectResponse
    {
        if (request()->input('position') === '') {
            request()->merge(['position' => null]);
        }

        if (! request()->filled('validation')) {
            request()->merge(['validation' => null]);
        }

        $this->validate(request(), [
            'default_name' => 'required|string|max:255',
            'name'   => 'required|array',
            'name.*' => 'required|string|max:255',
            'position' => 'nullable|integer|min:0',
            'default_value' => 'nullable|string',
            'validation' => 'nullable|in:' . implode(',', ValidationEnum::getValues()),
            'regex' => 'nullable|required_if:validation,regex|string',
            'is_required' => 'nullable|boolean',
            'is_unique' => 'nullable|boolean',
        ]);

        $attributeType = $this->serviceAttributeTypeRepository->findOrFail($id);

        if ($this->requiresOptions($attributeType->type)) {
            $this->validateOptions();
        }

        $validation = request()->input('validation');
        $regex = $validation === 'regex' ? request()->input('regex') : null;

        $this->serviceAttributeTypeRepository->update([
            'position'      => request()->input('position'),
            'default_value' => request()->input('default_value'),
            'default_name'  => request()->input('default_name'),
            'validation'    => $validation,
            'regex'         => $regex,
            'is_required'   => request()->boolean('is_required'),
            'is_unique'     => request()->boolean('is_unique'),
        ], $attributeType->id);

        foreach (core()->getAllLocales() as $locale) {
            $attributeType->translateOrNew($locale->code)->fill([
                'name' => request()->input("name.{$locale->code}"),
            ])->save();
        }

        if ($this->requiresOptions($attributeType->type)) {
            $this->serviceAttributeTypeOptionRepository->syncOptions(
                $attributeType,
                request()->input('options', [])
            );
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

    /**
     * Determine if the attribute type requires options.
     */
    protected function requiresOptions(?string $type): bool
    {
        if (! $type) {
            return false;
        }

        return in_array($type, [
            ServiceAttributeTypeEnum::SELECT->value,
            ServiceAttributeTypeEnum::MULTISELECT->value,
            ServiceAttributeTypeEnum::CHECKBOX->value,
        ], true);
    }

    /**
     * Validate option payload.
     */
    protected function validateOptions(): void
    {
        $rules = [
            'options' => 'required|array|min:1',
            'options.*.id' => 'nullable|integer|exists:service_attribute_type_options,id',
            'options.*.admin_name' => 'required|string|max:255',
            'options.*.label' => 'required|array',
            'options.*.sort_order' => 'nullable|integer',
        ];

        foreach (core()->getAllLocales() as $locale) {
            $rules["options.*.label.{$locale->code}"] = 'required|string|max:255';
        }

        request()->validate($rules);
    }
}


