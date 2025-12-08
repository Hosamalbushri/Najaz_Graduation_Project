<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    |
    | All ACLs related to dashboard will be placed here.
    |
    */
    [
        'key'   => 'dashboard',
        'name'  => 'Admin::app.acl.dashboard',
        'route' => 'najaz.admin.dashboard.index',
        'sort'  => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Citizens
    |--------------------------------------------------------------------------
    |
    | All ACLs related to citizens will be placed here.
    |
    */
    [
        'key'   => 'citizens',
        'name'  => 'Admin::app.acl.citizens',
        'route' => 'admin.citizens.index',
        'sort'  => 2,
    ], [
        'key'   => 'citizens.citizens',
        'name'  => 'Admin::app.acl.citizens',
        'route' => 'admin.citizens.index',
        'sort'  => 1,
    ], [
        'key'   => 'citizens.citizens.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.citizens.store',
        'sort'  => 1,
    ], [
        'key'   => 'citizens.citizens.view',
        'name'  => 'Admin::app.acl.view',
        'route' => 'admin.citizens.view',
        'sort'  => 2,
    ], [
        'key'   => 'citizens.citizens.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.citizens.citizen.update',
        'sort'  => 3,
    ], [
        'key'   => 'citizens.citizens.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.citizens.citizen.delete',
        'sort'  => 4,
    ], [
        'key'   => 'citizens.citizens.mass-delete',
        'name'  => 'Admin::app.acl.mass-delete',
        'route' => 'admin.citizens.mass_delete',
        'sort'  => 5,
    ], [
        'key'   => 'citizens.citizens.mass-update',
        'name'  => 'Admin::app.acl.mass-update',
        'route' => 'admin.citizens.mass_update',
        'sort'  => 6,
    ], [
        'key'   => 'citizens.citizens.add-note',
        'name'  => 'Admin::app.acl.add-note',
        'route' => 'admin.citizen.note.store',
        'sort'  => 7,
    ], [
        'key'   => 'citizens.identity-verifications',
        'name'  => 'Admin::app.acl.identity-verifications',
        'route' => 'admin.identity-verifications.index',
        'sort'  => 2,
    ], [
        'key'   => 'citizens.identity-verifications.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.identity-verifications.store',
        'sort'  => 1,
    ], [
        'key'   => 'citizens.identity-verifications.view',
        'name'  => 'Admin::app.acl.view',
        'route' => 'admin.identity-verifications.view',
        'sort'  => 2,
    ], [
        'key'   => 'citizens.identity-verifications.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.identity-verifications.update',
        'sort'  => 3,
    ], [
        'key'   => 'citizens.identity-verifications.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.identity-verifications.delete',
        'sort'  => 4,
    ], [
        'key'   => 'citizens.types',
        'name'  => 'Admin::app.acl.citizen-types',
        'route' => 'admin.citizens.types.index',
        'sort'  => 3,
    ], [
        'key'   => 'citizens.types.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.citizens.types.store',
        'sort'  => 1,
    ], [
        'key'   => 'citizens.types.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.citizens.types.update',
        'sort'  => 2,
    ], [
        'key'   => 'citizens.types.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.citizens.types.delete',
        'sort'  => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    |
    | All ACLs related to services will be placed here.
    |
    */
    [
        'key'   => 'services',
        'name'  => 'Admin::app.acl.services',
        'route' => 'admin.services.index',
        'sort'  => 3,
    ], [
        'key'   => 'services.services',
        'name'  => 'Admin::app.acl.services',
        'route' => 'admin.services.index',
        'sort'  => 1,
    ], [
        'key'   => 'services.services.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.services.create',
        'sort'  => 1,
    ], [
        'key'   => 'services.services.store',
        'name'  => 'Admin::app.acl.store',
        'route' => 'admin.services.store',
        'sort'  => 2,
    ], [
        'key'   => 'services.services.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.services.edit',
        'sort'  => 3,
    ], [
        'key'   => 'services.services.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.services.update',
        'sort'  => 4,
    ], [
        'key'   => 'services.services.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.services.delete',
        'sort'  => 5,
    ], [
        'key'   => 'services.services.mass-delete',
        'name'  => 'Admin::app.acl.mass-delete',
        'route' => 'admin.services.mass_delete',
        'sort'  => 6,
    ], [
        'key'   => 'services.services.mass-update',
        'name'  => 'Admin::app.acl.mass-update',
        'route' => 'admin.services.mass_update',
        'sort'  => 7,
    ], [
        'key'   => 'services.categories',
        'name'  => 'Admin::app.acl.categories',
        'route' => 'admin.services.categories.index',
        'sort'  => 2,
    ], [
        'key'   => 'services.categories.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.services.categories.create',
        'sort'  => 1,
    ], [
        'key'   => 'services.categories.store',
        'name'  => 'Admin::app.acl.store',
        'route' => 'admin.services.categories.store',
        'sort'  => 2,
    ], [
        'key'   => 'services.categories.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.services.categories.edit',
        'sort'  => 3,
    ], [
        'key'   => 'services.categories.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.services.categories.update',
        'sort'  => 4,
    ], [
        'key'   => 'services.categories.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.services.categories.delete',
        'sort'  => 5,
    ], [
        'key'   => 'services.categories.mass-delete',
        'name'  => 'Admin::app.acl.mass-delete',
        'route' => 'admin.services.categories.mass_delete',
        'sort'  => 6,
    ], [
        'key'   => 'services.categories.mass-update',
        'name'  => 'Admin::app.acl.mass-update',
        'route' => 'admin.services.categories.mass_update',
        'sort'  => 7,
    ], [
        'key'   => 'services.attribute-groups',
        'name'  => 'Admin::app.acl.attribute-groups',
        'route' => 'admin.attribute-groups.index',
        'sort'  => 3,
    ], [
        'key'   => 'services.attribute-groups.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.attribute-groups.store',
        'sort'  => 1,
    ], [
        'key'   => 'services.attribute-groups.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.attribute-groups.edit',
        'sort'  => 2,
    ], [
        'key'   => 'services.attribute-groups.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.attribute-groups.delete',
        'sort'  => 3,
    ], [
        'key'   => 'services.attribute-types',
        'name'  => 'Admin::app.acl.attribute-types',
        'route' => 'admin.attribute-types.index',
        'sort'  => 4,
    ], [
        'key'   => 'services.attribute-types.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.attribute-types.create',
        'sort'  => 1,
    ], [
        'key'   => 'services.attribute-types.store',
        'name'  => 'Admin::app.acl.store',
        'route' => 'admin.attribute-types.store',
        'sort'  => 2,
    ], [
        'key'   => 'services.attribute-types.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.attribute-types.edit',
        'sort'  => 3,
    ], [
        'key'   => 'services.attribute-types.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.attribute-types.update',
        'sort'  => 4,
    ], [
        'key'   => 'services.attribute-types.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.attribute-types.delete',
        'sort'  => 5,
    ], [
        'key'   => 'services.attribute-types.mass-delete',
        'name'  => 'Admin::app.acl.mass-delete',
        'route' => 'admin.attribute-types.mass_delete',
        'sort'  => 6,
    ], [
        'key'   => 'services.document-templates',
        'name'  => 'Admin::app.acl.document-templates',
        'route' => 'admin.services.document-templates.index',
        'sort'  => 5,
    ], [
        'key'   => 'services.document-templates.store',
        'name'  => 'Admin::app.acl.store',
        'route' => 'admin.services.document-templates.store',
        'sort'  => 1,
    ], [
        'key'   => 'services.document-templates.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.services.document-templates.edit',
        'sort'  => 2,
    ], [
        'key'   => 'services.document-templates.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.services.document-templates.update',
        'sort'  => 3,
    ], [
        'key'   => 'services.document-templates.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.services.document-templates.delete',
        'sort'  => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Requests
    |--------------------------------------------------------------------------
    |
    | All ACLs related to service requests will be placed here.
    |
    */
    [
        'key'   => 'service-requests',
        'name'  => 'Admin::app.acl.service-requests',
        'route' => 'admin.service-requests.index',
        'sort'  => 4,
    ], [
        'key'   => 'service-requests.view',
        'name'  => 'Admin::app.acl.view',
        'route' => 'admin.service-requests.view',
        'sort'  => 1,
    ], [
        'key'   => 'service-requests.update-status',
        'name'  => 'Admin::app.acl.update-status',
        'route' => 'admin.service-requests.update-status',
        'sort'  => 2,
    ], [
        'key'   => 'service-requests.cancel',
        'name'  => 'Admin::app.acl.cancel',
        'route' => 'admin.service-requests.cancel',
        'sort'  => 3,
    ], [
        'key'   => 'service-requests.add-notes',
        'name'  => 'Admin::app.acl.add-notes',
        'route' => 'admin.service-requests.add-notes',
        'sort'  => 4,
    ], [
        'key'   => 'service-requests.print',
        'name'  => 'Admin::app.acl.print',
        'route' => 'admin.service-requests.print',
        'sort'  => 5,
    ], [
        'key'   => 'service-requests.download-word',
        'name'  => 'Admin::app.acl.download-word',
        'route' => 'admin.service-requests.download-word',
        'sort'  => 6,
    ], [
        'key'   => 'service-requests.upload-pdf',
        'name'  => 'Admin::app.acl.upload-pdf',
        'route' => 'admin.service-requests.upload-pdf',
        'sort'  => 7,
    ], [
        'key'   => 'service-requests.custom-template',
        'name'  => 'Admin::app.acl.custom-template',
        'route' => 'admin.service-requests.custom-template.store',
        'sort'  => 8,
    ], [
        'key'   => 'service-requests.custom-template.view',
        'name'  => 'Admin::app.acl.view',
        'route' => 'admin.service-requests.view',
        'sort'  => 1,
    ], [
        'key'   => 'service-requests.custom-template.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.service-requests.custom-template.store',
        'sort'  => 2,
    ], [
        'key'   => 'service-requests.custom-template.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.service-requests.custom-template.store',
        'sort'  => 3,
    ], [
        'key'   => 'service-requests.custom-template.copy',
        'name'  => 'Admin::app.acl.copy',
        'route' => 'admin.service-requests.custom-template.copy',
        'sort'  => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    |
    | All ACLs related to settings will be placed here.
    |
    */
    [
        'key'   => 'settings',
        'name'  => 'Admin::app.acl.settings',
        'route' => 'admin.settings.users.index',
        'sort'  => 5,
    ], [
        'key'   => 'settings.locales',
        'name'  => 'Admin::app.acl.locales',
        'route' => 'admin.settings.locales.index',
        'sort'  => 1,
    ], [
        'key'   => 'settings.locales.store',
        'name'  => 'Admin::app.acl.store',
        'route' => 'admin.settings.locales.store',
        'sort'  => 1,
    ], [
        'key'   => 'settings.locales.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.settings.locales.edit',
        'sort'  => 2,
    ], [
        'key'   => 'settings.locales.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.settings.locales.update',
        'sort'  => 3,
    ], [
        'key'   => 'settings.locales.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.settings.locales.delete',
        'sort'  => 4,
    ], [
        'key'   => 'settings.channels',
        'name'  => 'Admin::app.acl.channels',
        'route' => 'admin.settings.channels.index',
        'sort'  => 2,
    ], [
        'key'   => 'settings.channels.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.settings.channels.create',
        'sort'  => 1,
    ], [
        'key'   => 'settings.channels.store',
        'name'  => 'Admin::app.acl.store',
        'route' => 'admin.settings.channels.store',
        'sort'  => 2,
    ], [
        'key'   => 'settings.channels.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.settings.channels.edit',
        'sort'  => 3,
    ], [
        'key'   => 'settings.channels.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.settings.channels.update',
        'sort'  => 4,
    ], [
        'key'   => 'settings.channels.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.settings.channels.delete',
        'sort'  => 5,
    ], [
        'key'   => 'settings.users',
        'name'  => 'Admin::app.acl.users',
        'route' => 'admin.settings.users.index',
        'sort'  => 3,
    ], [
        'key'   => 'settings.users.users',
        'name'  => 'Admin::app.acl.users',
        'route' => 'admin.settings.users.index',
        'sort'  => 1,
    ], [
        'key'   => 'settings.users.users.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.settings.users.store',
        'sort'  => 1,
    ], [
        'key'   => 'settings.users.users.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.settings.users.edit',
        'sort'  => 2,
    ], [
        'key'   => 'settings.users.users.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.settings.users.update',
        'sort'  => 3,
    ], [
        'key'   => 'settings.users.users.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.settings.users.delete',
        'sort'  => 4,
    ], [
        'key'   => 'settings.roles',
        'name'  => 'Admin::app.acl.roles',
        'route' => 'admin.settings.roles.index',
        'sort'  => 4,
    ], [
        'key'   => 'settings.roles.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.settings.roles.create',
        'sort'  => 1,
    ], [
        'key'   => 'settings.roles.store',
        'name'  => 'Admin::app.acl.store',
        'route' => 'admin.settings.roles.store',
        'sort'  => 2,
    ], [
        'key'   => 'settings.roles.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.settings.roles.edit',
        'sort'  => 3,
    ], [
        'key'   => 'settings.roles.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.settings.roles.update',
        'sort'  => 4,
    ], [
        'key'   => 'settings.roles.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.settings.roles.delete',
        'sort'  => 5,
    ], [
        'key'   => 'settings.data_transfer',
        'name'  => 'Admin::app.acl.data-transfer',
        'route' => 'admin.settings.data_transfer.imports.index',
        'sort'  => 5,
    ], [
        'key'   => 'settings.data_transfer.imports',
        'name'  => 'Admin::app.acl.imports',
        'route' => 'admin.settings.data_transfer.imports.index',
        'sort'  => 1,
    ], [
        'key'   => 'settings.data_transfer.imports.create',
        'name'  => 'Admin::app.acl.create',
        'route' => 'admin.settings.data_transfer.imports.create',
        'sort'  => 1,
    ], [
        'key'   => 'settings.data_transfer.imports.store',
        'name'  => 'Admin::app.acl.store',
        'route' => 'admin.settings.data_transfer.imports.store',
        'sort'  => 2,
    ], [
        'key'   => 'settings.data_transfer.imports.edit',
        'name'  => 'Admin::app.acl.edit',
        'route' => 'admin.settings.data_transfer.imports.edit',
        'sort'  => 3,
    ], [
        'key'   => 'settings.data_transfer.imports.update',
        'name'  => 'Admin::app.acl.update',
        'route' => 'admin.settings.data_transfer.imports.update',
        'sort'  => 4,
    ], [
        'key'   => 'settings.data_transfer.imports.delete',
        'name'  => 'Admin::app.acl.delete',
        'route' => 'admin.settings.data_transfer.imports.delete',
        'sort'  => 5,
    ], [
        'key'   => 'settings.data_transfer.imports.import',
        'name'  => 'Admin::app.acl.import',
        'route' => 'admin.settings.data_transfer.imports.import',
        'sort'  => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    |
    | All ACLs related to configuration will be placed here.
    |
    */
    [
        'key'   => 'configuration',
        'name'  => 'Admin::app.acl.configure',
        'route' => 'admin.configuration.index',
        'sort'  => 6,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    |
    | All ACLs related to notifications will be placed here.
    |
    */
    [
        'key'   => 'notifications',
        'name'  => 'Admin::app.acl.notifications',
        'route' => 'admin.service-notifications.index',
        'sort'  => 7,
    ], [
        'key'   => 'notifications.view',
        'name'  => 'Admin::app.acl.view',
        'route' => 'admin.service-notifications.get_notifications',
        'sort'  => 1,
    ], [
        'key'   => 'notifications.viewed',
        'name'  => 'Admin::app.acl.viewed',
        'route' => 'admin.service-notifications.viewed',
        'sort'  => 2,
    ], [
        'key'   => 'notifications.mark-read',
        'name'  => 'Admin::app.acl.mark-read',
        'route' => 'admin.service-notifications.mark_read',
        'sort'  => 3,
    ], [
        'key'   => 'notifications.read-all',
        'name'  => 'Admin::app.acl.read-all',
        'route' => 'admin.service-notifications.read_all',
        'sort'  => 4,
    ],
];
