<?php

return [
    [
        'key'   => 'dashboard',
        'name'  => 'admin::app.components.layouts.sidebar.dashboard',
        'route' => 'najaz.admin.dashboard.index',
        'sort'  => 1,
        'icon'  => 'icon-dashboard',
    ],
    [
        'key'   => 'services',
        'name'  => 'Admin::app.components.layouts.sidebar.services.title',
        'route' => 'admin.services.index',
        'sort'  => 2,
        'icon'  => 'icon-product',
    ],
    [
        'key'   => 'services.services',
        'name'  => 'Admin::app.components.layouts.sidebar.services.services',
        'route' => 'admin.services.index',
        'sort'  => 1,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'services.attribute-groups',
        'name'  => 'Admin::app.components.layouts.sidebar.services.attribute-groups',
        'route' => 'admin.attribute-groups.index',
        'sort'  => 2,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'services.attribute-groups.list',
        'name'  => 'Admin::app.components.layouts.sidebar.services.attribute-groups',
        'route' => 'admin.attribute-groups.index',
        'sort'  => 2,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'services.attribute-groups.attribute-types',
        'name'  => 'Admin::app.components.layouts.sidebar.services.attribute-types',
        'route' => 'admin.attribute-types.index',
        'sort'  => 2,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'services.document-templates',
        'name'  => 'Admin::app.components.layouts.sidebar.services.document-templates',
        'route' => 'admin.services.document-templates.index',
        'sort'  => 3,
        'icon'  => 'icon-sales',
    ],

    [
        'key'   => 'citizens',
        'name'  => 'Admin::app.components.layouts.sidebar.citizens',
        'route' => 'admin.citizens.index',
        'sort'  => 3,
        'icon'  => 'custom-icon-users text-xl',
    ],
    [
        'key'   => 'citizens.citizens',
        'name'  => 'Admin::app.components.layouts.sidebar.citizens',
        'route' => 'admin.citizens.index',
        'sort'  => 1,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'citizens.types',
        'name'  => 'Admin::app.components.layouts.sidebar.citizen-types',
        'route' => 'admin.citizens.types.index',
        'sort'  => 2,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'citizens.identity-verifications',
        'name'  => 'Admin::app.components.layouts.sidebar.identity-verifications',
        'route' => 'admin.identity-verifications.index',
        'sort'  => 3,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'service-requests',
        'name'  => 'Admin::app.components.layouts.sidebar.service-requests',
        'route' => 'admin.service-requests.index',
        'sort'  => 4,
        'icon'  => 'icon-sales',
    ],
];
