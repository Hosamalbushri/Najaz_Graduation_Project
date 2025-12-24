<?php

return [
    'importers' => [
        'services' => [
            'title' => 'الخدمات',

            'validation' => [
                'errors' => [
                    'service-id-not-found' => 'معرف الخدمة: \'%s\' غير موجود في النظام.',
                    'invalid-category-id'   => 'معرف الفئة غير صحيح أو غير موجود.',
                    'missing-translation'   => 'يجب توفير ترجمة واحدة على الأقل لاسم الخدمة.',
                ],
            ],
        ],
    ],
];

