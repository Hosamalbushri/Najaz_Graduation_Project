<?php

namespace Najaz\GraphQLAPI\Mutations\Admin\Citizen;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Najaz\Citizen\Repositories\IdentityVerificationRepository;

class IdentityVerificationMutation
{
    public function __construct(
        protected IdentityVerificationRepository $identityVerificationRepository,
    ) {}

    /**
     * Create an identity verification record.
     */
    public function store($rootValue, array $args): array
    {
        $data = $this->validateStore($args);

        $documents = $this->handleDocuments($data['citizen_id'], $args['documents'] ?? []);

        $payload = [
            'citizen_id' => $data['citizen_id'],
            'notes'      => $data['notes'] ?? null,
            'status'     => 'pending',
            'documents'  => $documents ?: null,
        ];

        // Handle face video upload
        if (isset($args['face_video']) && $args['face_video'] instanceof UploadedFile) {
            $video = $args['face_video'];
            $payload['face_video'] = $video->store("citizens/{$data['citizen_id']}/identity-verifications/videos", 'public');
        }

        $verification = $this->identityVerificationRepository
            ->create($payload)
            ->fresh(['citizen', 'reviewer']);

        return $this->response($verification, trans('Admin::app.citizens.identity-verifications.index.create-success'));
    }

    /**
     * Update an identity verification.
     */
    public function update($rootValue, array $args): array
    {
        $id = (int) $args['id'];

        $data = $this->validateUpdate($args);

        $verification = $this->identityVerificationRepository->findOrFail($id);

        $payload = [
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? $verification->notes,
        ];

        if (in_array($payload['status'], ['approved', 'rejected', 'needs_more_info'], true)) {
            $payload['reviewed_by'] = Auth::guard('admin')->id();
            $payload['reviewed_at'] = now();
        }

        if ($payload['status'] === 'approved') {
            $citizen = $verification->citizen;
            $citizen->identity_verification_status = 1;
            $citizen->save();
        }

        $verification = $this->identityVerificationRepository
            ->update($payload, $id)
            ->fresh(['citizen', 'reviewer']);

        return $this->response($verification, trans('Admin::app.citizens.identity-verifications.index.update-success'));
    }

    /**
     * Delete an identity verification.
     */
    public function delete($rootValue, array $args): array
    {
        $id = (int) $args['id'];

        $verification = $this->identityVerificationRepository->findOrFail($id);

        if (! empty($verification->documents)) {
            foreach ($verification->documents as $document) {
                Storage::disk('public')->delete($document);
            }
        }

        // Delete face video if exists
        if ($verification->face_video) {
            Storage::disk('public')->delete($verification->face_video);
        }

        $this->identityVerificationRepository->delete($id);

        return [
            'success' => true,
            'message' => trans('Admin::app.citizens.identity-verifications.index.delete-success'),
        ];
    }

    protected function validateStore(array $input): array
    {
        return Validator::make($input, [
            'citizen_id'  => ['required', 'exists:citizens,id'],
            'notes'       => ['nullable', 'string'],
            'documents'   => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'face_video'  => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:10240'], // 10MB max for video
        ])->validated();
    }

    protected function validateUpdate(array $input): array
    {
        $data = Validator::make($input, [
            'status' => ['required', Rule::in(['PENDING', 'APPROVED', 'REJECTED', 'NEEDS_MORE_INFO'])],
            'notes'  => ['nullable', 'string'],
        ])->validated();

        $data['status'] = $this->normalizeStatus($data['status']);

        return $data;
    }

    /**
     * Save uploaded documents and return their paths.
     */
    protected function handleDocuments(int $citizenId, array $documents): array
    {
        $paths = [];

        foreach ($documents as $document) {
            if ($document instanceof UploadedFile) {
                $paths[] = $document->store("citizens/{$citizenId}/identity-verifications", 'public');
            }
        }

        return $paths;
    }

    protected function normalizeStatus(string $status): string
    {
        return strtolower($status);
    }

    protected function response($verification, string $message): array
    {
        return [
            'success'      => true,
            'message'      => $message,
            'verification' => $verification,
        ];
    }
}

