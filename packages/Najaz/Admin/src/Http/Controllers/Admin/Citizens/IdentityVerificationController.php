<?php

namespace Najaz\Admin\Http\Controllers\Admin\Citizens;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Citizens\IdentityVerificationDataGrid;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Citizen\Repositories\IdentityVerificationRepository;
use Webkul\Admin\Http\Controllers\Controller;

class IdentityVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected IdentityVerificationRepository $identityVerificationRepository,
        protected CitizenRepository $citizenRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(IdentityVerificationDataGrid::class)->process();
        }

        return view('admin::citizens.identity-verifications.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $verification = $this->identityVerificationRepository
            ->with(['citizen.citizenType', 'reviewer'])
            ->findOrFail($id);

        return view('admin::citizens.identity-verifications.show', compact('verification'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(): JsonResponse
    {
        $this->validate(request(), [
            'citizen_id'  => 'required|exists:citizens,id',
            'documents'   => 'nullable|array',
            'documents.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
            'notes'       => 'nullable|string',
        ]);

        $data = request()->only(['citizen_id', 'notes']);
        $data['status'] = 'pending';

        // Handle file uploads
        if (request()->hasFile('documents')) {
            $documents = [];
            $citizenId = $data['citizen_id'];
            foreach (request()->file('documents') as $file) {
                $path = $file->store("citizens/{$citizenId}/identity-verifications", 'public');
                $documents[] = $path;
            }
            $data['documents'] = $documents;
        }

        $verification = $this->identityVerificationRepository->create($data);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.identity-verifications.index.create-success'),
            'data'    => $verification,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(int $id): JsonResponse
    {
        $this->validate(request(), [
            'status' => 'required|in:pending,approved,rejected,needs_more_info',
            'notes'  => 'nullable|string',
        ]);

        $verification = $this->identityVerificationRepository->findOrFail($id);

        $data = request()->only(['status', 'notes']);

        // If status is being changed to approved/rejected, set reviewer info
        if (in_array($data['status'], ['approved', 'rejected', 'needs_more_info'])) {
            $data['reviewed_by'] = auth()->guard('admin')->id();
            $data['reviewed_at'] = now();
        }

        // If approved, update citizen's identity_verification_status
        if ($data['status'] == 'approved') {
            $citizen = $verification->citizen;
            $citizen->identity_verification_status = 1;
            $citizen->save();
        }

        $verification = $this->identityVerificationRepository->update($data, $id);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.identity-verifications.index.update-success'),
            'data'    => $verification->fresh(['citizen', 'reviewer']),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $verification = $this->identityVerificationRepository->findOrFail($id);

        // Delete associated documents
        if ($verification->documents) {
            foreach ($verification->documents as $document) {
                Storage::disk('public')->delete($document);
            }
        }

        $this->identityVerificationRepository->delete($id);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.identity-verifications.index.delete-success'),
        ]);
    }
}
