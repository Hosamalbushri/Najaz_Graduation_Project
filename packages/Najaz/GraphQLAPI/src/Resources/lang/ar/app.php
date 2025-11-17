<?php

return [
    'citizens' => [
        'auth' => [
            'unauthenticated'   => 'يجب تسجيل الدخول كمواطن.',
            'not-activated'     => 'حسابك غير مُفعل.',
            'verify-first'      => 'يرجى تفعيل الحساب قبل تسجيل الدخول.',
            'identity-pending'  => 'طلب توثيق الهوية ما زال قيد المراجعة.',
        ],

        'login' => [
            'invalid-creds'   => 'بيانات الدخول غير صحيحة.',
            'success'         => 'تم تسجيل الدخول بنجاح.',
            'logout-success'  => 'تم تسجيل الخروج بنجاح.',
        ],

        'registration' => [
            'success' => 'تم إنشاء حساب المواطن بنجاح.',
        ],

        'identity_verification' => [
            'submitted' => 'تم إرسال طلب توثيق الهوية بنجاح.',
        ],

        'service_request' => [
            'created'                => 'تم إنشاء طلب الخدمة بنجاح.',
            'updated'                => 'تم تحديث طلب الخدمة بنجاح.',
            'cancelled'              => 'تم إلغاء طلب الخدمة بنجاح.',
            'not_found'              => 'طلب الخدمة غير موجود.',
            'cannot_update'          => 'يمكن تحديث طلب الخدمة فقط عندما تكون الحالة pending أو in_progress.',
            'cannot_cancel'          => 'يمكن إلغاء طلب الخدمة فقط عندما تكون الحالة pending أو in_progress.',
            'service_not_accessible' => 'هذه الخدمة غير متاحة لنوع المواطن الخاص بك.',
            'missing_required_fields' => 'الحقول المطلوبة التالية مفقودة: :fields',
            'invalid_fields'         => 'الحقول التالية تحتوي على قيم غير صحيحة: :fields',
            'unknown_fields'         => 'الحقول التالية غير مرتبطة بهذه الخدمة: :fields',
            'create_error'           => 'فشل إنشاء طلب الخدمة: :message',
            'update_error'           => 'فشل تحديث طلب الخدمة: :message',
            'cancel_error'           => 'فشل إلغاء طلب الخدمة: :message',
        ],
    ],
];

