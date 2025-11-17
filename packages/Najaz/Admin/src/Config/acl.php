<?php

return [
    [
        'key'   => 'identity-verifications',
        'name'  => 'Identity Verifications',
        'route' => 'admin.identity-verifications.index',
        'sort'  => 3,
        'icon'  => 'icon-sales',
        'children' => [
            [
                'key'   => 'identity-verifications.view',
                'name'  => 'View Identity Verifications',
                'route' => 'admin.identity-verifications.index',
                'sort'  => 1,
            ],
            [
                'key'   => 'identity-verifications.create',
                'name'  => 'Create Identity Verification',
                'route' => 'admin.identity-verifications.store',
                'sort'  => 2,
            ],
            [
                'key'   => 'identity-verifications.update',
                'name'  => 'Update Identity Verification',
                'route' => 'admin.identity-verifications.update',
                'sort'  => 3,
            ],
            [
                'key'   => 'identity-verifications.delete',
                'name'  => 'Delete Identity Verification',
                'route' => 'admin.identity-verifications.delete',
                'sort'  => 4,
            ],
        ],
    ],
    [
        'key'   => 'services',
        'name'  => 'Services',
        'route' => 'admin.services.index',
        'sort'  => 4,
        'children' => [
            [
                'key'   => 'services.create',
                'name'  => 'Create Service',
                'route' => 'admin.services.create',
                'sort'  => 1,
            ],
            [
                'key'   => 'services.edit',
                'name'  => 'Edit Service',
                'route' => 'admin.services.edit',
                'sort'  => 2,
            ],
            [
                'key'   => 'services.delete',
                'name'  => 'Delete Service',
                'route' => 'admin.services.delete',
                'sort'  => 3,
            ],
        ],
    ],
    [
        'key'   => 'service-requests',
        'name'  => 'Service Requests',
        'route' => 'admin.service-requests.index',
        'sort'  => 5,
        'children' => [
            [
                'key'   => 'service-requests.view',
                'name'  => 'View Service Requests',
                'route' => 'admin.service-requests.index',
                'sort'  => 1,
            ],
            [
                'key'   => 'service-requests.update',
                'name'  => 'Update Service Request',
                'route' => 'admin.service-requests.update-status',
                'sort'  => 2,
            ],
            [
                'key'   => 'service-requests.cancel',
                'name'  => 'Cancel Service Request',
                'route' => 'admin.service-requests.cancel',
                'sort'  => 3,
            ],
        ],
    ],
];
