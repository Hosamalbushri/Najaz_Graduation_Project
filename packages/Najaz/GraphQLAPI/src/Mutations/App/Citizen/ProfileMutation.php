<?php

namespace Najaz\GraphQLAPI\Mutations\App\Citizen;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Najaz\Citizen\Repositories\CitizenRepository;
use Webkul\Core\Rules\PhoneNumber;

class ProfileMutation
{
    public function __construct(
        protected CitizenRepository $citizenRepository,
    ) {}

    /**
     * Update citizen profile.
     * If identity is verified, identity-related fields cannot be updated.
     */
    public function updateProfile($rootValue, array $args): array
    {
        $citizen = najaz_graphql()->authorize('citizen-api');

        // Check if citizen's identity is verified
        // Check both identity_verification_status field and identityVerification relationship
        $identityVerification = $citizen->identityVerification;
        $isIdentityVerified = ($citizen->identity_verification_status === 1 || $citizen->identity_verification_status === true) 
            || ($identityVerification && $identityVerification->status === 'approved');

        // Check if identity is verified and user tries to update identity fields
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
                if (isset($args[$field])) {
                    $attemptedUpdates[] = $field;
                }
            }

            if (!empty($attemptedUpdates)) {
                return [
                    'success' => false,
                    'message' => trans('najaz_graphql::app.citizens.profile.identity_locked'),
                    'citizen' => null,
                ];
            }
        }

        $data = $this->validateInput($args, $citizen->id, $isIdentityVerified);

        // Handle password update
        if (isset($data['new_password'])) {
            // Verify current password if provided
            if (isset($data['current_password'])) {
                if (!Hash::check($data['current_password'], $citizen->password)) {
                    return [
                        'success' => false,
                        'message' => trans('najaz_graphql::app.citizens.profile.invalid_current_password'),
                        'citizen' => null,
                    ];
                }
            }

            $data['password'] = Hash::make($data['new_password']);
            unset($data['new_password'], $data['current_password'], $data['new_password_confirmation']);
        }

        $citizen = $this->citizenRepository->update($data, $citizen->id)->fresh(['citizenType']);

        return [
            'success' => true,
            'message' => trans('najaz_graphql::app.citizens.profile.updated'),
            'citizen' => $citizen,
        ];
    }

    protected function validateInput(array $input, int $citizenId, bool $isIdentityVerified): array
    {
        $rules = [
            'email' => ['nullable', 'email', 'unique:citizens,email,' . $citizenId],
            'phone' => ['nullable', 'unique:citizens,phone,' . $citizenId, new PhoneNumber],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'current_password' => ['required_with:new_password', 'string'],
        ];

        // If identity is NOT verified, allow updating identity-related fields
        if (!$isIdentityVerified) {
            $rules['first_name'] = ['nullable', 'string'];
            $rules['middle_name'] = ['nullable', 'string'];
            $rules['last_name'] = ['nullable', 'string'];
            $rules['national_id'] = ['nullable', 'string', 'unique:citizens,national_id,' . $citizenId];
            $rules['date_of_birth'] = ['nullable', 'date', 'before:today'];
            $rules['gender'] = ['nullable', 'string'];
        }

        $data = Validator::make($input, $rules)->validated();

        return $data;
    }
}

