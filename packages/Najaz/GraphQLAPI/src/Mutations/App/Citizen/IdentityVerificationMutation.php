<?php

namespace Najaz\GraphQLAPI\Mutations\App\Citizen;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Najaz\Citizen\Repositories\IdentityVerificationRepository;

class IdentityVerificationMutation
{
    public function __construct(
        protected IdentityVerificationRepository $identityVerificationRepository,
    ) {}

    /**
     * Citizen-facing identity verification request.
     *
     * Citizen ID is derived from the authenticated guard (citizen-api).
     */
    public function store($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        // Check if citizen already has an identity verification
        $existingVerification = $this->identityVerificationRepository
            ->where('citizen_id', $citizen->id)
            ->first();

        if ($existingVerification) {
            return [
                'success'      => false,
                'message'      => trans('najaz_graphql::app.citizens.identity_verification.already_exists'),
                'verification' => $existingVerification,
            ];
        }

        $data = $this->validateInput($args);

        $documents = $this->storeDocuments($citizen->id, $args);

        $payload = [
            'citizen_id' => $citizen->id,
            'status'     => 'pending',
            'documents'  => $documents ?: null,
        ];

        // Handle face video upload
        if (isset($args['face_video']) && $args['face_video'] instanceof UploadedFile) {
            $video = $args['face_video'];
            $payload['face_video'] = $video->store("citizens/{$citizen->id}/identity-verifications/videos", 'public');
        }

        $verification = $this->identityVerificationRepository
            ->create($payload)
            ->fresh(['citizen']);

        Event::dispatch('identity.verification.created', $verification);

        return [
            'success'      => true,
            'message'      => trans('najaz_graphql::app.citizens.identity_verification.submitted'),
            'verification' => $verification,
        ];
    }

    protected function validateInput(array $input): array
    {
        $rules = [
            'face_video' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:10240'], // 10MB max for video
        ];

        // Support both new format (front_document, back_document) and old format (documents array)
        if (isset($input['front_document']) || isset($input['back_document'])) {
            // New format: require both front and back documents
            $rules['front_document'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
            $rules['back_document'] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        } else {
            // Old format: require exactly 2 documents
            $rules['documents'] = ['required', 'array', 'size:2'];
            $rules['documents.*'] = ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
        }

        return Validator::make($input, $rules)->validated();
    }

    /**
     * Store uploaded documents under the citizen's folder.
     * Only two documents: front (first) and back (second).
     * Supports both new format (front_document, back_document) and old format (documents array).
     */
    protected function storeDocuments(int $citizenId, array $args): array
    {
        $paths = [];

        // New format: front_document and back_document
        if (isset($args['front_document']) && $args['front_document'] instanceof UploadedFile) {
            $paths[] = $args['front_document']->store("citizens/{$citizenId}/identity-verifications", 'public');
        }

        if (isset($args['back_document']) && $args['back_document'] instanceof UploadedFile) {
            $paths[] = $args['back_document']->store("citizens/{$citizenId}/identity-verifications", 'public');
        }

        // Old format: documents array (first is front, second is back)
        if (isset($args['documents']) && is_array($args['documents'])) {
            foreach ($args['documents'] as $document) {
            if ($document instanceof UploadedFile) {
                $paths[] = $document->store("citizens/{$citizenId}/identity-verifications", 'public');
                }
            }
        }

        return $paths;
    }

    /**
     * Update identity verification documents (only documents and face video).
     * Allowed only when status is pending or rejected.
     */
    public function update($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $id = (int) $args['id'];

        $verification = $this->identityVerificationRepository->findOrFail($id);

        // Check if the verification belongs to the authenticated citizen
        if ($verification->citizen_id !== $citizen->id) {
            return [
                'success'      => false,
                'message'      => trans('najaz_graphql::app.citizens.identity_verification.unauthorized'),
                'verification' => null,
            ];
        }

        // Only allow update if status is pending or rejected
        if (!in_array($verification->status, ['pending', 'rejected'])) {
            return [
                'success'      => false,
                'message'      => trans('najaz_graphql::app.citizens.identity_verification.update_not_allowed'),
                'verification' => $verification,
            ];
        }

        $data = $this->validateUpdateInput($args);

        // Check if at least one file is being updated
        $hasDocuments = (isset($args['front_document']) && $args['front_document'] instanceof UploadedFile) ||
                       (isset($args['back_document']) && $args['back_document'] instanceof UploadedFile) ||
                       (isset($args['documents']) && is_array($args['documents']) && !empty($args['documents']));
        $hasVideo = isset($args['face_video']) && $args['face_video'] instanceof UploadedFile;

        if (!$hasDocuments && !$hasVideo) {
            return [
                'success'      => false,
                'message'      => trans('najaz_graphql::app.citizens.identity_verification.no_files_to_update'),
                'verification' => $verification,
            ];
        }

        $payload = [];

        // Handle documents update
        $documents = $this->storeDocuments($citizen->id, $args);
        if (!empty($documents)) {
            // Delete old documents
            if (!empty($verification->documents)) {
                foreach ($verification->documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
            $payload['documents'] = $documents;
        }

        // Handle face video update
        if ($hasVideo) {
            // Delete old video if exists
            if ($verification->face_video) {
                Storage::disk('public')->delete($verification->face_video);
            }
            $video = $args['face_video'];
            $payload['face_video'] = $video->store("citizens/{$citizen->id}/identity-verifications/videos", 'public');
        }

        // Reset status to pending if it was rejected
        if ($verification->status === 'rejected') {
            $payload['status'] = 'pending';
            $payload['notes'] = null; // Clear rejection notes
        }

        $verification = $this->identityVerificationRepository
            ->update($payload, $id)
            ->fresh(['citizen']);

        return [
            'success'      => true,
            'message'      => trans('najaz_graphql::app.citizens.identity_verification.updated'),
            'verification' => $verification,
        ];
    }

    /**
     * Delete identity verification request.
     * Allowed only when status is pending.
     */
    public function delete($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        $id = (int) $args['id'];

        $verification = $this->identityVerificationRepository->findOrFail($id);

        // Check if the verification belongs to the authenticated citizen
        if ($verification->citizen_id !== $citizen->id) {
            return [
                'success' => false,
                'message' => trans('najaz_graphql::app.citizens.identity_verification.unauthorized'),
            ];
        }

        // Only allow delete if status is pending
        if ($verification->status !== 'pending') {
            return [
                'success' => false,
                'message' => trans('najaz_graphql::app.citizens.identity_verification.delete_not_allowed'),
            ];
        }

        // Delete associated documents
        if (!empty($verification->documents)) {
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
            'message' => trans('najaz_graphql::app.citizens.identity_verification.deleted'),
        ];
    }

    protected function validateUpdateInput(array $input): array
    {
        $rules = [
            'face_video' => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:10240'], // 10MB max for video
        ];

        // Support both new format (front_document, back_document) and old format (documents array)
        // Documents are optional in update (user can update only video or only documents)
        if (isset($input['front_document']) || isset($input['back_document'])) {
            // New format: if one is provided, both must be provided
            if (isset($input['front_document']) || isset($input['back_document'])) {
                $rules['front_document'] = ['required_with:back_document', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
                $rules['back_document'] = ['required_with:front_document', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
            }
        } else {
            // Old format: if documents array is provided, it must contain exactly 2 documents
            if (isset($input['documents'])) {
                $rules['documents'] = ['array', 'size:2'];
                $rules['documents.*'] = ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'];
            }
        }

        return Validator::make($input, $rules)->validated();
    }
}

