<?php

namespace Najaz\Admin\Http\Controllers\Admin\Citizens;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Najaz\Admin\DataGrids\Citizens\CitizenTypeDateGrid;
use Najaz\Citizen\Repositories\CitizenTypeRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Rules\Code;

class CitizenTypesController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected CitizenTypeRepository $citizenTypeRepository) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CitizenTypeDateGrid::class)->process();
        }

        return view('admin::citizens.types.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'code' => ['required', 'unique:citizen_types,code', new Code],
            'name' => 'required',
        ]);

        Event::dispatch('citizen.citizen_type.create.before');

        $data = array_merge(request()->only([
            'code',
            'name',
        ]), [
            'is_user_defined' => 1,
        ]);

        $citizenType = $this->citizenTypeRepository->create($data);

        Event::dispatch('citizen.citizen_type.create.after', $citizenType);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.types.index.create.success'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'code' => ['required', 'unique:citizen_types,code,'.$id, new Code],
            'name' => 'required',
        ]);

        Event::dispatch('citizen.citizen_type.update.before', $id);

        $citizenType = $this->citizenTypeRepository->update(request()->only([
            'code',
            'name',
        ]), $id);

        Event::dispatch('citizen.citizen_type.update.after', $citizenType);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.types.index.edit.success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $citizenType = $this->citizenTypeRepository->findOrFail($id);

        if (! $citizenType->is_user_defined) {
            return new JsonResponse([
                'message' => trans('Admin::app.citizens.types.index.edit.type-default'),
            ], 400);
        }

        if ($citizenType->citizens->count()) {
            return new JsonResponse([
                'message' => trans('Admin::app.citizens.types.index.edit.citizen-associate'),
            ], 400);
        }

        try {
            Event::dispatch('citizen.citizen_type.delete.before', $id);

            $this->citizenTypeRepository->delete($id);

            Event::dispatch('citizen.citizen_type.delete.after', $id);

            return new JsonResponse([
                'message' => trans('Admin::app.citizens.types.index.edit.delete-success'),
            ]);
        } catch (\Exception $e) {
        }

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.types.index.edit.delete-failed'),
        ], 500);
    }
}
