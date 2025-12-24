<?php

return [
    [
        'key'   => 'dashboard',
        'name'  => 'Admin::app.components.layouts.sidebar.dashboard',
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
        'key'   => 'services.categories',
        'name'  => 'Admin::app.components.layouts.sidebar.services.categories',
        'route' => 'admin.services.categories.index',
        'sort'  => 2,
        'icon'  => 'icon-category',
    ],
    [
        'key'   => 'services.attribute-groups',
        'name'  => 'Admin::app.components.layouts.sidebar.services.attribute-groups',
        'route' => 'admin.attribute-groups.index',
        'sort'  => 3,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'services.attribute-groups.list',
        'name'  => 'Admin::app.components.layouts.sidebar.services.attribute-groups',
        'route' => 'admin.attribute-groups.index',
        'sort'  => 3,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'services.attribute-groups.attribute-types',
        'name'  => 'Admin::app.components.layouts.sidebar.services.attribute-types',
        'route' => 'admin.attribute-types.index',
        'sort'  => 3,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'services.document-templates',
        'name'  => 'Admin::app.components.layouts.sidebar.services.document-templates',
        'route' => 'admin.services.document-templates.index',
        'sort'  => 4,
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
    [
        'key'   => 'reporting',
        'name'  => 'Admin::app.components.layouts.sidebar.reporting',
        'route' => 'admin.reporting.citizens.index',
        'sort'  => 5,
        'icon'  => 'icon-report',
        'icon-class' => 'report-icon',
    ],
    [
        'key'   => 'reporting.citizens',
        'name'  => 'Admin::app.components.layouts.sidebar.citizens',
        'route' => 'admin.reporting.citizens.index',
        'sort'  => 1,
        'icon'  => '',
    ],
    [
        'key'   => 'reporting.services',
        'name'  => 'Admin::app.components.layouts.sidebar.services.title',
        'route' => 'admin.reporting.services.index',
        'sort'  => 2,
        'icon'  => '',
    ],

    /**
     * Settings.
     */
    [
        'key'        => 'settings',
        'name'       => 'Admin::app.components.layouts.sidebar.settings',
        'route'      => 'admin.settings.locales.index',
        'sort'       => 6,
        'icon'       => 'icon-settings',
        'icon-class' => 'settings-icon',
    ],
    [
        'key'        => 'settings.locales',
        'name'       => 'Admin::app.components.layouts.sidebar.locales',
        'route'      => 'admin.settings.locales.index',
        'sort'       => 1,
        'icon'       => '',
    ],
    [
        'key'        => 'settings.channels',
        'name'       => 'Admin::app.components.layouts.sidebar.channels',
        'route'      => 'admin.settings.channels.index',
        'sort'       => 2,
        'icon'       => '',
    ],
    [
        'key'        => 'settings.users',
        'name'       => 'Admin::app.components.layouts.sidebar.users',
        'route'      => 'admin.settings.users.index',
        'sort'       => 3,
        'icon'       => '',
    ],
    [
        'key'        => 'settings.roles',
        'name'       => 'Admin::app.components.layouts.sidebar.roles',
        'route'      => 'admin.settings.roles.index',
        'sort'       => 4,
        'icon'       => '',
    ],
    [
        'key'        => 'settings.data_transfer',
        'name'       => 'Admin::app.components.layouts.sidebar.data-transfer',
        'route'      => 'admin.settings.data_transfer.imports.index',
        'sort'       => 5,
        'icon'       => '',
    ],
    [
        'key'        => 'settings.data_transfer.imports',
        'name'       => 'Admin::app.components.layouts.sidebar.imports',
        'route'      => 'admin.settings.data_transfer.imports.index',
        'sort'       => 1,
        'icon'       => '',
    ],

    /**
     * Configuration.
     */
    [
        'key'        => 'configuration',
        'name'       => 'Admin::app.components.layouts.sidebar.configure',
        'route'      => 'admin.configuration.index',
        'sort'       => 7,
        'icon'       => 'icon-configuration',
    ],
];
