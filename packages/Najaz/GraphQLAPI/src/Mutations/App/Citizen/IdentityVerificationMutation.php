<?php

namespace Najaz\GraphQLAPI\Mutations\App\Citizen;

use Illuminate\Http\UploadedFile;
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

        $data = $this->validateInput($args);

        $documents = $this->storeDocuments($citizen->id, $args['documents'] ?? []);

        $payload = [
            'citizen_id' => $citizen->id,
            'notes'      => $data['notes'] ?? null,
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

        return [
            'success'      => true,
            'message'      => trans('najaz_graphql::app.citizens.identity_verification.submitted'),
            'verification' => $verification,
        ];
    }

    protected function validateInput(array $input): array
    {
        return Validator::make($input, [
            'notes'       => ['nullable', 'string'],
            'documents'   => ['nullable', 'array'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'face_video'  => ['nullable', 'file', 'mimes:mp4,mov,avi,webm', 'max:10240'], // 10MB max for video
        ])->validated();
    }

    /**
     * Store uploaded documents under the citizen's folder.
     */
    protected function storeDocuments(int $citizenId, array $documents): array
    {
        $paths = [];

        foreach ($documents as $document) {
            if ($document instanceof UploadedFile) {
                $paths[] = $document->store("citizens/{$citizenId}/identity-verifications", 'public');
            }
        }

        return $paths;
    }
}

