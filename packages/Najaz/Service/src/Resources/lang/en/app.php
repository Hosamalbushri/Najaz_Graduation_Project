<?php

return [
    'importers' => [
        'services' => [
            'title' => 'Services',

            'validation' => [
                'errors' => [
                    'service-id-not-found' => 'Service ID: \'%s\' not found in the system.',
                    'invalid-category-id'   => 'Category ID is invalid or not found.',
                    'missing-translation'   => 'At least one translation must be provided for the service name.',
                ],
            ],
        ],
    ],
];

