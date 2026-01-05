<?php

return [
    'notifications' => [
        'service_request_created' => [
            'title' => 'تم إنشاء طلب جديد',
            'message' => 'تم إنشاء طلب جديد برقم :increment_id للخدمة :service_name',
            'beneficiary_title' => 'تم ربطك بطلب جديد',
            'beneficiary_message' => 'تم ربطك كطرف مستفيد في الطلب رقم :increment_id للخدمة :service_name',
        ],
        'service_request_status_changed' => [
            'title' => 'تم تغيير حالة الطلب',
            'message' => 'تم تغيير حالة الطلب :increment_id للخدمة :service_name إلى :status',
            'beneficiary_title' => 'تم تحديث حالة الطلب المرتبط بك',
            'beneficiary_message' => 'تم تغيير حالة الطلب رقم :increment_id للخدمة :service_name الذي أنت مرتبط به إلى :status',
        ],
        'identity_verification_submitted' => [
            'title' => 'تم تقديم طلب التوثيق',
            'message' => 'تم تقديم طلب توثيق الهوية بنجاح وهو قيد المراجعة',
        ],
        'identity_verification_status_changed' => [
            'title' => 'تم تغيير حالة التوثيق',
            'message' => 'تم تغيير حالة طلب توثيق الهوية إلى :status',
        ],
    ],
    'statuses' => [
        'pending' => 'معلق',
        'in_progress' => 'قيد المعالجة',
        'completed' => 'مكتمل',
        'rejected' => 'مرفوض',
        'canceled' => 'ملغى',
        'cancelled' => 'ملغى',
        'approved' => 'موافق عليه',
        'needs_more_info' => 'يحتاج إلى معلومات إضافية',
        'needs_revision' => 'يحتاج تعديل - يرجى مراجعة طلبك وإجراء التعديلات المطلوبة',
    ],
];

