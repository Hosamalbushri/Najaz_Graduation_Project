<?php

return [
    /**
     * Documents.
     */
    [
        'key'  => 'documents',
        'name' => 'Admin::app.configuration.index.documents.title',
        'info' => 'Admin::app.configuration.index.documents.info',
        'icon' => 'settings/tax.svg',
        'sort' => 1,
    ], [
        'key'  => 'documents.official',
        'name' => 'Admin::app.configuration.index.documents.official.title',
        'info' => 'Admin::app.configuration.index.documents.official.info',
        'sort' => 1,
    ], [
        'key'    => 'documents.official.header',
        'name'   => 'Admin::app.configuration.index.documents.official.header.title',
        'info'   => 'Admin::app.configuration.index.documents.official.header.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'header_right',
                'title'         => 'Admin::app.configuration.index.documents.official.header.header-right',
                'info'          => 'Admin::app.configuration.index.documents.official.header.header-right-info',
                'type'          => 'textarea',
                'locale_based'  => true,
            ],
          [
                'name'          => 'header_center',
                'title'         => 'Admin::app.configuration.index.documents.official.header.header-center',
                'info'          => 'Admin::app.configuration.index.documents.official.header.header-center-info',
                'type'          => 'image',
                'validation'    => 'mimes:bmp,jpeg,jpg,png,webp',
                'channel_based' => true,
            ],
            [
                'name'          => 'header_left',
                'title'         => 'Admin::app.configuration.index.documents.official.header.header-left',
                'info'          => 'Admin::app.configuration.index.documents.official.header.header-left-info',
                'type'          => 'textarea',
                'locale_based'  => true,
            ],
        ],
    ], [
        'key'    => 'documents.official.footer',
        'name'   => 'Admin::app.configuration.index.documents.official.footer.title',
        'info'   => 'Admin::app.configuration.index.documents.official.footer.info',
        'sort'   => 2,
        'fields' => [
            [
                'name'          => 'footer_text',
                'title'         => 'Admin::app.configuration.index.documents.official.footer.footer-text',
                'info'          => 'Admin::app.configuration.index.documents.official.footer.footer-text-info',
                'type'          => 'textarea',
                'locale_based'  => true,
            ],
            [
                'name'          => 'stamp_image',
                'title'         => 'Admin::app.configuration.index.documents.official.footer.stamp-image',
                'info'          => 'Admin::app.configuration.index.documents.official.footer.stamp-image-info',
                'type'          => 'image',
                'validation'    => 'mimes:bmp,jpeg,jpg,png,webp',
            ],
        ],
    ],
];
