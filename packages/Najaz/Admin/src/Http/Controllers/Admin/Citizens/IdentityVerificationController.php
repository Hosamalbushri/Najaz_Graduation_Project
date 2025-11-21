<?php

namespace Najaz\Admin\Http\Controllers\Admin\Citizens;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Najaz\Admin\DataGrids\Citizens\IdentityVerificationDataGrid;
use Najaz\Citizen\Repositories\CitizenRepository;
use Najaz\Citizen\Repositories\IdentityVerificationRepository;
use Najaz\Admin\Http\Controllers\Controller;

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

        // Format face video for x-admin::media.videos component
        $faceVideoArray = [];
        if ($verification->face_video) {
            $faceVideoArray[] = [
                'id' => 'face_video_1',
                'url' => asset('storage/' . $verification->face_video),
                'path' => $verification->face_video,
            ];
        }

        return view('admin::citizens.identity-verifications.show', compact('verification', 'faceVideoArray'));
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
            'face_video'  => 'nullable|file|mimes:mp4,mov,avi,webm|max:10240', // 10MB max for video
            'notes'       => 'nullable|string',
        ]);

        $data = request()->only(['citizen_id', 'notes']);
        $data['status'] = 'pending';

        // Check if citizen already has an identity verification
        $existingVerification = $this->identityVerificationRepository
            ->where('citizen_id', $data['citizen_id'])
            ->first();

        if ($existingVerification) {
            return new JsonResponse([
                'message' => trans('Admin::app.citizens.identity-verifications.index.already-exists'),
                'data'    => $existingVerification,
            ], 422);
        }

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

        // Handle face video upload
        if (request()->hasFile('face_video')) {
            $citizenId = $data['citizen_id'];
            $video = request()->file('face_video');
            $data['face_video'] = $video->store("citizens/{$citizenId}/identity-verifications/videos", 'public');
        }

        $verification = $this->identityVerificationRepository->create($data);

        Event::dispatch('identity.verification.created', $verification);

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
        $status = request()->input('status');

        $rules = [
            'status' => 'required|in:approved,rejected',
        ];

        // Notes is required when status is rejected
        if ($status === 'rejected') {
            $rules['notes'] = 'required|string';
        } else {
            $rules['notes'] = 'nullable|string';
        }

        $this->validate(request(), $rules);

        $data = request()->only(['status', 'notes']);

        $verification = $this->identityVerificationRepository->updateVerificationStatus($data, $id);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.identity-verifications.index.update-success'),
            'data'    => $verification,
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

        // Delete face video if exists
        if ($verification->face_video) {
            Storage::disk('public')->delete($verification->face_video);
        }

        $this->identityVerificationRepository->delete($id);

        return new JsonResponse([
            'message' => trans('Admin::app.citizens.identity-verifications.index.delete-success'),
        ]);
    }
}
