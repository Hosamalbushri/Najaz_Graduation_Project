<?php

return [
    'citizens' => [
        'auth' => [
            'unauthenticated'   => 'You must be logged in as a citizen.',
            'not-activated'     => 'Your account is not active.',
            'verify-first'      => 'Please verify your account before signing in.',
            'identity-pending'  => 'Your identity verification is still pending approval.',
        ],

        'login' => [
            'invalid-creds'   => 'The provided credentials are incorrect.',
            'success'         => 'Citizen signed in successfully.',
            'logout-success'  => 'Citizen signed out successfully.',
        ],

        'registration' => [
            'success' => 'Citizen registered successfully.',
        ],

        'identity_verification' => [
            'submitted' => 'Identity verification request submitted successfully.',
        ],

        'service_request' => [
            'created'                => 'Service request created successfully.',
            'updated'                => 'Service request updated successfully.',
            'cancelled'              => 'Service request cancelled successfully.',
            'not_found'              => 'Service request not found.',
            'cannot_update'          => 'Service request can only be updated when status is pending or in_progress.',
            'cannot_cancel'          => 'Service request can only be cancelled when status is pending or in_progress.',
            'service_not_accessible' => 'This service is not accessible for your citizen type.',
            'missing_required_fields' => 'The following required fields are missing: :fields',
            'invalid_fields'         => 'The following fields have invalid values: :fields',
            'unknown_fields'         => 'The following fields are not part of this service form: :fields',
            'create_error'           => 'Failed to create service request: :message',
            'update_error'           => 'Failed to update service request: :message',
            'cancel_error'           => 'Failed to cancel service request: :message',
        ],
    ],
];

