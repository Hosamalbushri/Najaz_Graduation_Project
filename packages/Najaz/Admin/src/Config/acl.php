<?php

return [
    [
        'key'   => 'admin',
        'name'  => 'Admin',
        'route' => 'admin.admin.index',
        'sort'  => 2
    ],
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
];