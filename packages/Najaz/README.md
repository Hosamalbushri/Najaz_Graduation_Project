# Najaz Packages

## نظام الحزم (بنفس طريقة Bagisto)

تستخدم حزم Najaz نفس طريقة Bagisto - **لا حاجة لتسجيل الحزم في `require` أو `require-dev`**

### كيف يعمل النظام:

1. **Path Repository Pattern**: `packages/*/*` يكتشف جميع الحزم تلقائياً
2. **Autoload**: كل حزمة مسجلة في `composer.json` الرئيسي في قسم `autoload`
3. **Laravel Package Discovery**: Laravel يكتشف الحزم تلقائياً من خلال `extra.laravel.providers` في composer.json لكل حزمة

## لا حاجة لتثبيت

الحزم تعمل تلقائياً! فقط شغّل:

```bash
composer dump-autoload
php artisan package:discover
```

## كيفية إضافة حزمة جديدة

### 1. أنشئ مجلد الحزمة
`packages/Najaz/YourPackageName`

### 2. أنشئ `composer.json` داخل الحزمة
```json
{
    "name": "najaz/your-package-name",
    "license": "MIT",
    "authors": [
        {
            "name": "Najaz",
            "email": "support@webkul.com"
        }
    ],
    "require": {},
    "autoload": {
        "psr-4": {
            "Najaz\\YourPackageName\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "Najaz\\YourPackageName\\Providers\\YourPackageNameServiceProvider"
            ]
        }
    },
    "minimum-stability": "dev"
}
```

### 3. أضف autoload في `composer.json` الرئيسي

في قسم `autoload.psr-4` في `composer.json` الرئيسي:
```json
"Najaz\\YourPackageName\\": "packages/Najaz/YourPackageName/src"
```

### 4. شغّل الأوامر
```bash
composer dump-autoload
php artisan package:discover
```

**هذا كل شيء!** لا حاجة لتسجيل الحزمة في `require` أو `repositories` لأن `packages/*/*` يكتشفها تلقائياً.

## الحزم الحالية
- ✅ najaz/admin
- ✅ najaz/citizen
