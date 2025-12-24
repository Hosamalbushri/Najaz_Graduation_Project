<?php

return [
    'importers' => [
        'customers' => [
            'title' => 'العملاء',

            'validation' => [
                'errors' => [
                    'duplicate-email'        => 'البريد الإلكتروني: \'%s\' تم العثور عليه أكثر من مرة في ملف الاستيراد.',
                    'duplicate-phone'        => 'الهاتف: \'%s\' تم العثور عليه أكثر من مرة في ملف الاستيراد.',
                    'email-not-found'        => 'البريد الإلكتروني: \'%s\' غير موجود في النظام.',
                    'invalid-customer-group' => 'مجموعة العملاء غير صالحة أو غير مدعومة',
                ],
            ],
        ],

        'products' => [
            'title' => 'المنتجات',

            'validation' => [
                'errors' => [
                    'duplicate-url-key'         => 'تم إنشاء مفتاح URL مكرر: \'%s\' بالفعل لعنصر بالرمز SKU: \'%s\'.',
                    'invalid-attribute-family'  => 'قيمة غير صالحة لعمود مجموعة السمات (مجموعة السمات غير موجودة؟)',
                    'invalid-type'              => 'نوع المنتج غير صالح أو غير مدعوم',
                    'sku-not-found'             => 'المنتج بالرمز SKU المحدد غير موجود',
                    'super-attribute-not-found' => 'لم يتم العثور على السمة الفائقة بالرمز: \'%s\' أو لا تنتمي إلى عائلة السمات: \'%s\'',
                ],
            ],
        ],

        'tax-rates' => [
            'title' => 'معدلات الضريبة',

            'validation' => [
                'errors' => [
                    'duplicate-identifier' => 'المعرف: \'%s\' تم العثور عليه أكثر من مرة في ملف الاستيراد.',
                    'identifier-not-found' => 'المعرف: \'%s\' غير موجود في النظام.',
                ],
            ],
        ],

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

    'validation' => [
        'errors' => [
            'column-empty-headers' => 'عدد الأعمدة "%s" تحتوي على رؤوس فارغة.',
            'column-name-invalid'  => 'أسماء الأعمدة غير صالحة: "%s".',
            'column-not-found'     => 'لم يتم العثور على الأعمدة المطلوبة: %s.',
            'column-numbers'       => 'عدد الأعمدة لا يتطابق مع عدد الصفوف في الرأس.',
            'invalid-attribute'    => 'الرأس يحتوي على سمات غير صالحة: "%s".',
            'system'               => 'حدث خطأ غير متوقع في النظام.',
            'wrong-quotes'         => 'استخدمت علامات اقتباس مائلة بدلاً من علامات اقتباس مستقيمة.',
        ],
    ],
];
