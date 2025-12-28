<?php

return [

    /**
     * General.
     */
    [
        'key'  => 'general',
        'name' => 'admin::app.configuration.index.general.title',
        'info' => 'admin::app.configuration.index.general.info',
        'sort' => 1,
    ],
        [
        'key'  => 'general.design',
        'name' => 'admin::app.configuration.index.general.design.title',
        'info' => 'admin::app.configuration.index.general.design.info',
        'icon' => 'settings/theme.svg',
        'sort' => 3,
    ], [
        'key'    => 'general.design.admin_logo',
        'name'   => 'admin::app.configuration.index.general.design.admin-logo.title',
        'info'   => 'admin::app.configuration.index.general.design.admin-logo.title-info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'logo_image',
                'title'         => 'admin::app.configuration.index.general.design.admin-logo.logo-image',
                'type'          => 'image',
                'channel_based' => false,
                'validation'    => 'mimes:bmp,jpeg,jpg,png,webp,svg',
            ], [
                'name'          => 'favicon',
                'title'         => 'admin::app.configuration.index.general.design.admin-logo.favicon',
                'type'          => 'image',
                'channel_based' => false,
                'validation'    => 'mimes:bmp,jpeg,jpg,png,webp,svg,ico',
            ],
        ],
    ],
    [
        'key'  => 'general.magic_ai',
        'name' => 'admin::app.configuration.index.general.magic-ai.title',
        'info' => 'admin::app.configuration.index.general.magic-ai.info',
        'icon' => 'settings/magic-ai.svg',
        'sort' => 3,
    ], [
        'key'    => 'general.magic_ai.settings',
        'name'   => 'admin::app.configuration.index.general.magic-ai.settings.title',
        'info'   => 'admin::app.configuration.index.general.magic-ai.settings.title-info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'enabled',
                'title'         => 'admin::app.configuration.index.general.magic-ai.settings.enabled',
                'type'          => 'boolean',
                'channel_based' => true,
            ], [
                'name'          => 'api_key',
                'title'         => 'admin::app.configuration.index.general.magic-ai.settings.api-key',
                'type'          => 'password',
                'channel_based' => true,
            ], [
                'name'          => 'organization',
                'title'         => 'admin::app.configuration.index.general.magic-ai.settings.organization',
                'type'          => 'text',
                'channel_based' => true,
            ], [
                'name'          => 'api_domain',
                'title'         => 'admin::app.configuration.index.general.magic-ai.settings.llm-api-domain',
                'type'          => 'text',
                'channel_based' => true,
            ],
        ],
    ], [
        'key'    => 'general.magic_ai.content_generation',
        'name'   => 'admin::app.configuration.index.general.magic-ai.content-generation.title',
        'info'   => 'admin::app.configuration.index.general.magic-ai.content-generation.title-info',
        'sort'   => 1,
        'fields' => [
            [
                'name'  => 'enabled',
                'title' => 'admin::app.configuration.index.general.magic-ai.content-generation.enabled',
                'type'  => 'boolean',
            ], [
                'name'         => 'product_short_description_prompt',
                'title'        => 'admin::app.configuration.index.general.magic-ai.content-generation.product-short-description-prompt',
                'type'         => 'textarea',
                'locale_based' => true,
            ], [
                'name'         => 'product_description_prompt',
                'title'        => 'admin::app.configuration.index.general.magic-ai.content-generation.product-description-prompt',
                'type'         => 'textarea',
                'locale_based' => true,
            ], [
                'name'         => 'category_description_prompt',
                'title'        => 'admin::app.configuration.index.general.magic-ai.content-generation.category-description-prompt',
                'type'         => 'textarea',
                'locale_based' => true,
            ], [
                'name'         => 'cms_page_content_prompt',
                'title'        => 'admin::app.configuration.index.general.magic-ai.content-generation.cms-page-content-prompt',
                'type'         => 'textarea',
                'locale_based' => true,
            ],
        ],
    ],
    [
        'key'  => 'general.sitemap',
        'name' => 'admin::app.configuration.index.general.sitemap.title',
        'info' => 'admin::app.configuration.index.general.sitemap.info',
        'icon' => 'settings/store.svg',
        'sort' => 3,
    ], [
        'key'    => 'general.sitemap.settings',
        'name'   => 'admin::app.configuration.index.general.sitemap.settings.title',
        'info'   => 'admin::app.configuration.index.general.sitemap.settings.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'enabled',
                'title'         => 'admin::app.configuration.index.general.sitemap.settings.enabled',
                'type'          => 'boolean',
                'default'       => 1,
                'channel_based' => true,
            ],
        ],
    ], [
        'key'    => 'general.sitemap.file_limits',
        'name'   => 'admin::app.configuration.index.general.sitemap.file-limits.title',
        'info'   => 'admin::app.configuration.index.general.sitemap.file-limits.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'max_url_per_file',
                'title'         => 'admin::app.configuration.index.general.sitemap.file-limits.max-url-per-file',
                'type'          => 'text',
                'default'       => 50000,
                'validation'    => 'integer|min:1',
                'channel_based' => true,
            ],
        ],
    ], [
        'key'  => 'general.gdpr',
        'name' => 'admin::app.configuration.index.general.gdpr.title',
        'info' => 'admin::app.configuration.index.general.gdpr.info',
        'icon' => 'settings/store.svg',
        'sort' => 4,
    ], [
        'key'    => 'general.gdpr.settings',
        'name'   => 'admin::app.configuration.index.general.gdpr.settings.title',
        'info'   => 'admin::app.configuration.index.general.gdpr.settings.info',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'enabled',
                'title'         => 'admin::app.configuration.index.general.gdpr.settings.enabled',
                'type'          => 'boolean',
                'channel_based' => true,
                'locale_based'  => true,
            ],
        ],
    ], [
        'key'    => 'general.gdpr.agreement',
        'name'   => 'admin::app.configuration.index.general.gdpr.agreement.title',
        'info'   => 'admin::app.configuration.index.general.gdpr.agreement.info',
        'sort'   => 2,
        'fields' => [
            [
                'name'          => 'enabled',
                'title'         => 'admin::app.configuration.index.general.gdpr.agreement.enable',
                'type'          => 'boolean',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'agreement_label',
                'title'         => 'admin::app.configuration.index.general.gdpr.agreement.checkbox-label',
                'type'          => 'text',
                'default'       => 'I agree with the terms and conditions.',
                'validation'    => 'max:255',
                'depends'       => 'enabled:true',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'agreement_content',
                'title'         => 'admin::app.configuration.index.general.gdpr.agreement.content',
                'type'          => 'editor',
                'depends'       => 'enabled:true',
                'channel_based' => true,
                'locale_based'  => true,
            ],
        ],
    ], [
        'key'    => 'general.gdpr.cookie',
        'name'   => 'admin::app.configuration.index.general.gdpr.cookie.title',
        'info'   => 'admin::app.configuration.index.general.gdpr.cookie.info',
        'sort'   => 3,
        'fields' => [
            [
                'name'          => 'enabled',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie.enable',
                'type'          => 'boolean',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'position',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie.position',
                'type'          => 'select',
                'default'       => 'bottom-left',
                'depends'       => 'enabled:true',
                'options'       => [
                    [
                        'title' => 'admin::app.configuration.index.general.gdpr.cookie.bottom-left',
                        'value' => 'bottom-left',
                    ], [
                        'title' => 'admin::app.configuration.index.general.gdpr.cookie.bottom-right',
                        'value' => 'bottom-right',
                    ], [
                        'title' => 'admin::app.configuration.index.general.gdpr.cookie.top-left',
                        'value' => 'top-left',
                    ], [
                        'title' => 'admin::app.configuration.index.general.gdpr.cookie.top-right',
                        'value' => 'top-right',
                    ], [
                        'title' => 'admin::app.configuration.index.general.gdpr.cookie.center',
                        'value' => 'center',
                    ],
                ],
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'static_block_identifier',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie.identifier',
                'type'          => 'text',
                'default'       => 'Cookie Block',
                'validation'    => 'max:255',
                'depends'       => 'enabled:true',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie.description',
                'type'          => 'textarea',
                'default'       => 'This website uses cookies to ensure you get the best experience on our website.',
                'validation'    => 'max:500',
                'depends'       => 'enabled:true',
                'channel_based' => true,
                'locale_based'  => true,
            ],
        ],
    ], [
        'key'    => 'general.gdpr.cookie_consent',
        'name'   => 'admin::app.configuration.index.general.gdpr.cookie-consent.title',
        'info'   => 'admin::app.configuration.index.general.gdpr.cookie-consent.info',
        'sort'   => 4,
        'fields' => [
            [
                'name'          => 'strictly_necessary',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie-consent.strictly-necessary',
                'type'          => 'editor',
                'default'       => 'I agree with the terms and conditions.',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'basic_interaction',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie-consent.basic-interaction',
                'type'          => 'editor',
                'default'       => 'These trackers enable basic interactions and functionalities that allow you to access selected features of our service and facilitate your communication with us.',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'experience_enhancement',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie-consent.experience-enhancement',
                'type'          => 'editor',
                'default'       => 'These trackers help us to provide a personalized user experience by improving the quality of your preference management options, and by enabling the interaction with external networks and platforms.',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'measurements',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie-consent.measurement',
                'type'          => 'editor',
                'default'       => 'These trackers help us to measure traffic and analyze your behavior with the goal of improving our service.',
                'channel_based' => true,
                'locale_based'  => true,
            ], [
                'name'          => 'targeting_advertising',
                'title'         => 'admin::app.configuration.index.general.gdpr.cookie-consent.targeting-advertising',
                'type'          => 'editor',
                'default'       => 'These trackers help us to deliver personalized marketing content to you based on your behavior and to operate, serve and track ads.',
                'channel_based' => true,
                'locale_based'  => true,
            ],
        ],
    ],

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
        'icon' => 'settings/invoice.svg',
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
