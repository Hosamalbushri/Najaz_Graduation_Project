<?php

namespace Najaz\GraphQLAPI\Mutations\Admin\Citizen;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Najaz\Citizen\Repositories\IdentityVerificationRepository;

class IdentityVerificationMutation
{
    public function __construct(
        protected IdentityVerificationRepository $identityVerificationRepository,
    ) {}

    /**
     * Update identity verification status (approve/reject).
     * Admin can only update status and notes, not documents.
     */
    public function update($rootValue, array $args): array
    {
        $id = (int) $args['id'];

        $data = $this->validateUpdate($args);

        $payload = [
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? null,
        ];

        $verification = $this->identityVerificationRepository
            ->updateVerificationStatus($payload, $id);

        return $this->response($verification, trans('Admin::app.citizens.identity-verifications.index.update-success'));
    }


    protected function validateUpdate(array $input): array
    {
        $status = $input['status'] ?? null;
        $normalizedStatus = $status ? $this->normalizeStatus($status) : null;

        $rules = [
            'status' => ['required', Rule::in(['APPROVED', 'REJECTED'])],
        ];

        // Notes is required when status is rejected
        if ($normalizedStatus === 'rejected') {
            $rules['notes'] = ['required', 'string'];
        } else {
            $rules['notes'] = ['nullable', 'string'];
        }

        $data = Validator::make($input, $rules)->validated();

        $data['status'] = $this->normalizeStatus($data['status']);

        return $data;
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

