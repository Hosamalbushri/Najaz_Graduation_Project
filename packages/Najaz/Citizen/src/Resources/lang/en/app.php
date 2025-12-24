<?php

return [
    'importers' => [
        'citizens' => [
            'title' => 'Citizens',

            'validation' => [
                'errors' => [
                    'national-id-not-found' => 'National ID: \'%s\' not found in the system.',
                    'duplicate-national-id' => 'National ID: \'%s\' is found more than once in the import file.',
                    'duplicate-email'        => 'Email: \'%s\' is found more than once in the import file.',
                    'duplicate-phone'       => 'Phone: \'%s\' is found more than once in the import file.',
                    'invalid-citizen-type'   => 'Citizen type is invalid or not found.',
                ],
            ],
        ],
    ],
];

