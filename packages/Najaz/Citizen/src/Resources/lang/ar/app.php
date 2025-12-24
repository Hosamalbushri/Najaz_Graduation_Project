<?php

return [
    'importers' => [
        'citizens' => [
            'title' => 'المواطنين',

            'validation' => [
                'errors' => [
                    'national-id-not-found' => 'رقم الهوية: \'%s\' غير موجود في النظام.',
                    'duplicate-national-id' => 'رقم الهوية: \'%s\' موجود أكثر من مرة في ملف الاستيراد.',
                    'duplicate-email'        => 'البريد الإلكتروني: \'%s\' موجود أكثر من مرة في ملف الاستيراد.',
                    'duplicate-phone'       => 'رقم الهاتف: \'%s\' موجود أكثر من مرة في ملف الاستيراد.',
                    'invalid-citizen-type'   => 'نوع المواطن غير صحيح أو غير موجود.',
                ],
            ],
        ],
    ],
];

