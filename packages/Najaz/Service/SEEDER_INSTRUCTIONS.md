# تعليمات تشغيل Seeder لأنواع الحقول الثابتة

## كيفية تشغيل الـ Seeder

### الطريقة 1: تشغيل مباشر من سطر الأوامر

```bash
php artisan db:seed --class="Najaz\Service\Database\Seeders\ServiceFieldTypeTableSeeder"
```

### الطريقة 2: تشغيل مع معاملات (Locales)

```php
// في tinker أو في كود
use Najaz\Service\Database\Seeders\ServiceFieldTypeTableSeeder;

$seeder = new ServiceFieldTypeTableSeeder();
$seeder->run([
    'default_locale' => 'ar',
    'allowed_locales' => ['ar', 'en']
]);
```

### الطريقة 3: إضافة إلى DatabaseSeeder الرئيسي

يمكن إضافة الـ Seeder إلى ملف `database/seeders/DatabaseSeeder.php`:

```php
use Najaz\Service\Database\Seeders\ServiceFieldTypeTableSeeder;

public function run()
{
    $this->call(BagistoDatabaseSeeder::class);
    $this->call(ServiceFieldTypeTableSeeder::class);
}
```

ثم تشغيل:
```bash
php artisan db:seed
```

## ملاحظات مهمة

1. **قبل تشغيل الـ Seeder**: تأكد من تشغيل Migrations أولاً:
   ```bash
   php artisan migrate
   ```

2. **الحقول الثابتة**: الـ Seeder ينشئ حقول ثابتة مع `is_user_defined = 0`:
   - `id_number` (رقم هوية)
   - `citizen_name` (اسم مواطن)

3. **الحقول من المستخدم**: الحقول التي ينشئها المستخدم من لوحة الإدارة تكون `is_user_defined = 1`

4. **منع الحذف/التعديل**: الحقول الثابتة (`is_user_defined = 0`) لا يمكن حذفها أو تعديلها من لوحة الإدارة

