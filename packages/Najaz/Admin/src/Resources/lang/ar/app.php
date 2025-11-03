<?php

return [
    'citizens' => [
        'types' => [
            'index' => [
                'title'  => 'أنواع المواطنين',
                'create' => [
                    'title'      => 'إنشاء نوع مواطن جديد',
                    'create-btn' => 'إنشاء نوع مواطن',
                    'code'       => 'رمز النوع',
                    'name'       => 'اسم النوع',
                    'save-btn'   => 'حفظ',
                    'success' => 'تم إنشاء نوع المواطن بنجاح',
                ],
                'edit' => [
                    'title'             => 'تعديل نوع المواطن',
                    'success'           => 'تم تحديث نوع المواطن بنجاح',
                    'delete-success'    => 'تم حذف نوع المواطن بنجاح',
                    'delete-failed'     => 'فشل حذف نوع المواطن',
                    'type-default'      => 'لا يمكن حذف نوع المواطن الافتراضي',
                    'citizen-associate' => 'لا يمكن حذف نوع المواطن لأنه مرتبط بمواطنين',
                ],
                'datagrid' => [
                    'id'                  => 'المعرف',
                    'code'                => 'الرمز',
                    'name'                => 'الاسم',
                    'edit'                => 'تعديل',
                    'delete'              => 'حذف',
                    'delete-success'      => 'تم حذف نوع المواطن بنجاح',
                    'mass-delete-success' => 'تم حذف أنواع المواطنين المحددة بنجاح',
                    'mass-update-success' => 'تم تحديث أنواع المواطنين المحددة بنجاح',
                    'mass-delete-error'   => 'حدث خطأ ما',
                    'no-resource'         => 'المورد المقدم غير كافٍ للعملية',
                    'partial-action'      => 'تم تنفيذ بعض الإجراءات على الموارد المحددة',
                    'update-success'      => 'تم تحديث :resource بنجاح',
                ],
            ],
        ],
    ],
    'components' => [
        'layouts' => [
            'sidebar' => [
                'citizens'      => 'المواطنين',
                'citizen-types' => 'انواع المواطنين',
            ],
        ],
    ],
];
