<?php

return [
    [
        'key'   => 'admin',
        'name'  => 'Admin',
        'route' => 'admin.admin.index',
        'sort'  => 2,
        'icon'  => 'icon-sales',
    ],
    [
        'key'   => 'citizens',
        'name'  => 'Admin::app.components.layouts.sidebar.citizens',
        'route' => 'admin.citizens.index',
        'sort'  => 2,
        'icon'  => 'icon-sales',
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
];
