<?php

namespace Najaz\Admin\Http\Controllers\Admin\Citizens;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Citizens\CitizenDateGrid;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Citizen\Repositories\CitizenTypeRepository;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Core\Rules\PhoneNumber;

class CitizenController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected CitizenRepository $citizenRepository,
        protected CitizenTypeRepository $citizenTypeRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(CitizenDateGrid::class)->process();
        }

        $citizenTypes = $this->citizenTypeRepository->all();

        return view('admin::citizens.citizens.index', compact('citizenTypes'));
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $citizen = $this->citizenRepository->with('citizenType')->findOrFail($id);
        $citizenTypes = $this->citizenTypeRepository->all();

        return view('admin::citizens.citizens.view', compact('citizen', 'citizenTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('citizens::admin.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'first_name'       => 'string|required',
            'middle_name'      => 'string|required',
            'last_name'        => 'string|required',
            'gender'           => 'required',
            'email'            => 'nullable|email|unique:citizens,email',
            'date_of_birth'    => 'required|date|before:today',
            'national_id'      => 'required|unique:citizens,national_id',
            'phone'            => ['required', 'unique:citizens,phone', new PhoneNumber],
            'citizen_type_id'  => 'required|exists:citizen_types,id',
        ]);

        $data = request()->only([
            'first_name',
            'last_name',
            'middle_name',
            'gender',
            'email',
            'date_of_birth',
            'national_id',
            'phone',
            'citizen_type_id',
        ]);

        $data['status'] = 1;
        $data['is_verified'] = 0;
        $data['identity_verification_status'] = 0;

        $citizen = $this->citizenRepository->create($data);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.citizens.index.create.create-success'),
            'data'    => $citizen,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        // $resource = $this->repository->findOrFail($id);

        return view('citizens::admin.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'first_name'       => 'string|required',
            'middle_name'      => 'string|required',
            'last_name'        => 'string|required',
            'gender'           => 'required',
            'email'            => 'nullable|email|unique:citizens,email,'.$id,
            'date_of_birth'    => 'required|date|before:today',
            'national_id'      => 'required|unique:citizens,national_id,'.$id,
            'phone'            => ['required', 'unique:citizens,phone,'.$id, new PhoneNumber],
            'citizen_type_id'  => 'required|exists:citizen_types,id',
            'status'           => 'nullable|boolean',
            'is_verified'      => 'nullable|boolean',
        ]);

        $data = request()->only([
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'email',
            'date_of_birth',
            'national_id',
            'phone',
            'citizen_type_id',
            'status',
            'is_verified',
        ]);

        if (empty($data['phone'])) {
            $data['phone'] = null;
        }

        if (empty($data['email'])) {
            $data['email'] = null;
        }

        $citizen = $this->citizenRepository->update($data, $id);

        $citizen = $citizen->fresh(['citizenType']);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.citizens.view.edit.update-success'),
            'data'    => [
                'citizen' => $citizen,
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->citizenRepository->findOrFail($id);

        $this->citizenRepository->delete($id);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.citizens.view.delete-success'),
        ]);
    }

    /**
     * Mass delete resources.
     */
    public function massDestroy(): JsonResponse
    {
        // TODO: Implement mass delete logic

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.citizens.index.datagrid.delete-success'),
        ]);
    }

    /**
     * Mass update resources.
     */
    public function massUpdate(): JsonResponse
    {
        // TODO: Implement mass update logic

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.citizens.index.datagrid.update-success'),
        ]);
    }
}
