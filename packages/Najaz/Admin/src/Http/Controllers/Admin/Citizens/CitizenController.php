<?php

namespace Najaz\Admin\Http\Controllers\Admin\Citizens;

use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Citizens\CitizenDateGrid;
use Najaz\Admin\DataGrids\Citizens\View\CitizenBeneficiaryServiceRequestDataGrid;
use Najaz\Admin\DataGrids\Citizens\View\CitizenIdentityVerificationDataGrid;
use Najaz\Admin\DataGrids\Citizens\View\CitizenServiceRequestDataGrid;
use Najaz\Admin\Http\Controllers\Controller;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Citizen\Repositories\CitizenTypeRepository;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Core\Rules\PhoneNumber;

class CitizenController extends Controller
{
    /**
     * Ajax request for service requests.
     */
    public const SERVICE_REQUESTS = 'service-requests';

    /**
     * Ajax request for beneficiary service requests.
     */
    public const BENEFICIARY_SERVICE_REQUESTS = 'beneficiary-service-requests';

    /**
     * Ajax request for identity verification.
     */
    public const IDENTITY_VERIFICATION = 'identity-verification';

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
    public function show(int $id)
    {
        $citizen = $this->citizenRepository->with([
            'citizenType',
            'identityVerification.reviewer',
            'serviceRequests.service',
            'serviceRequestsAsBeneficiary.service',
            'notes.admin',
        ])->findOrFail($id);
        
        $citizenTypes = $this->citizenTypeRepository->all();

        if (request()->ajax()) {
            switch (request()->query('type')) {
                case self::SERVICE_REQUESTS:
                    return datagrid(CitizenServiceRequestDataGrid::class)->process();

                case self::BENEFICIARY_SERVICE_REQUESTS:
                    return datagrid(CitizenBeneficiaryServiceRequestDataGrid::class)->process();

                case self::IDENTITY_VERIFICATION:
                    return datagrid(CitizenIdentityVerificationDataGrid::class)->process();
            }
        }

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
        $citizen = $this->citizenRepository->with('identityVerification')->findOrFail($id);

        // Check if identity is verified
        $isIdentityVerified = ($citizen->identity_verification_status === 1 || $citizen->identity_verification_status === true)
            || ($citizen->identityVerification && $citizen->identityVerification->status === 'approved');

        // If identity is verified, prevent updating identity-related fields
        if ($isIdentityVerified) {
            $identityFields = [
                'first_name',
                'middle_name',
                'last_name',
                'national_id',
                'date_of_birth',
                'gender',
            ];

            $attemptedUpdates = [];
            foreach ($identityFields as $field) {
                if (request()->has($field)) {
                    $attemptedUpdates[] = $field;
                }
            }

            if (!empty($attemptedUpdates)) {
                return new JsonResponse([
                    'message' => trans('Admin::app.citizens.citizens.view.edit.identity-locked'),
                    'errors'  => [
                        'identity_verified' => [trans('Admin::app.citizens.citizens.view.edit.identity-locked-message')],
                    ],
                ], 422);
            }
        }

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

        // If identity is verified, exclude identity fields from update
        if ($isIdentityVerified) {
            $data = array_diff_key($data, array_flip([
                'first_name',
                'middle_name',
                'last_name',
                'national_id',
                'date_of_birth',
                'gender',
            ]));
        }

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
    public function destroy(int $id)
    {
        try {
        $this->citizenRepository->delete($id);

            session()->flash('success', trans('Admin::app.citizens.citizens.view.delete-success'));
            return redirect()->route('admin.citizens.index');

        } catch (\Exception $e) {
            $statusCode = str_contains($e->getMessage(), 'delete-has-relationships') ? 422 : 500;
            session()->flash('error', trans('Admin::app.citizens.citizens.view.delete-failed'));
            return redirect()->route('admin.citizens.view', $id);
        }
    }

    /**
     * Mass delete resources.
     */
    public function massDestroy(): JsonResponse
    {
        $indices = request()->input('indices', []);

        if (empty($indices) || ! is_array($indices)) {
            return new JsonResponse([
                'message' => trans('Admin::app.citizens.citizens.view.delete-failed'),
            ], 422);
        }

        try {
            $citizens = $this->citizenRepository->findWhereIn('id', $indices);

            if ($citizens->isEmpty()) {
                return new JsonResponse([
                    'message' => trans('Admin::app.citizens.citizens.view.delete-failed'),
                ], 422);
            }

            /**
             * Delete citizens. The repository will automatically check for relationships.
             */
            foreach ($citizens as $citizen) {
                $this->citizenRepository->delete($citizen->id);
            }

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.citizens.index.datagrid.delete-success'),
        ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    /**
     * Mass update resources.
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest): JsonResponse
    {
        $indices = $massUpdateRequest->input('indices');
        $status = $massUpdateRequest->input('value');

        try {
            $citizens = $this->citizenRepository->findWhereIn('id', $indices);

            if ($citizens->isEmpty()) {
                return new JsonResponse([
                    'message' => trans('Admin::app.citizens.citizens.index.datagrid.update-success'),
                ], 422);
            }

            /**
             * Update status for selected citizens.
             */
            foreach ($citizens as $citizen) {
                $this->citizenRepository->update([
                    'status' => $status,
                ], $citizen->id);
            }

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.citizens.index.datagrid.update-success'),
        ]);
        } catch (\Exception $exception) {
            return new JsonResponse([
                'message' => $exception->getMessage() ?: trans('Admin::app.citizens.citizens.index.datagrid.update-success'),
            ], 500);
        }
    }

    /**
     * Store a note for the citizen.
     */
    public function storeNotes(int $id)
    {
        $this->validate(request(), [
            'note' => 'string|required',
        ]);

        $citizen = $this->citizenRepository->findOrFail($id);

        $citizen->notes()->create([
            'citizen_id'       => $id,
            'note'             => request()->input('note'),
            'citizen_notified' => request()->input('citizen_notified', 0),
            'admin_id'         => auth()->guard('admin')->id(),
        ]);

        session()->flash('success', trans('Admin::app.citizens.citizens.view.note-created-success'));

        return redirect()->route('admin.citizens.view', $id);
    }
}
