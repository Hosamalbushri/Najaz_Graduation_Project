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
    ],
];

