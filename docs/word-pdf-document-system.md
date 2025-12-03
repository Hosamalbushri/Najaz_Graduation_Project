# نظام ملفات Word والPDF للخدمات

## نظرة عامة

تم إضافة نظام جديد يسمح بتوليد ملفات Word قابلة للتعبئة يدوياً للخدمات التي تحتوي على حقول ملفات أو صور. يقوم الموظف بتحميل ملف Word، تعبئته، تحويله إلى PDF على جهازه، ثم رفعه للنظام.

## المتطلبات

### 1. مكتبات PHP المطلوبة

تم إضافة المكتبات التالية في `composer.json`:

```json
{
    "require": {
        "phpoffice/phpword": "^1.2"
    }
}
```

قم بتشغيل:

```bash
composer install
```

### 2. قاعدة البيانات

قم بتشغيل الـ migrations الجديدة:

```bash
php artisan migrate
```

## الميزات الجديدة

### 1. توليد ملف Word

عندما تحتوي خدمة على حقول من نوع `file` أو `image`، يظهر زر "تحميل ملف Word القابل للتعبئة" في صفحة معالجة الطلب.

**الموقع:** عرض طلب الخدمة → حالة "قيد المعالجة" → زر تحميل Word

**كيف يعمل:**
1. يتحقق النظام من وجود حقول ملفات/صور في الخدمة
2. يولد ملف Word من القالب مع:
   - الحقول النصية معبأة تلقائياً
   - حقول الملفات/الصور فارغة مع placeholders واضحة مثل: `[أدخل البيانات من الملف: field_name]`
3. يحفظ الملف في `storage/app/service_requests/{request_id}/`
4. يتيح تحميله للموظف

### 2. رفع ملف PDF

بعد تحميل ملف Word وتعبئته، يقوم الموظف بـ:
1. فتح الملف على جهازه
2. مراجعة الملفات/الصور المرفوعة في النظام
3. استخراج البيانات المطلوبة وملء الحقول الفارغة
4. تحويل الملف إلى PDF (File → Save As → PDF في Microsoft Word)
5. رفع PDF إلى النظام

**الموقع:** عرض طلب الخدمة → تبويب "معالجة الوثيقة" → نموذج رفع PDF

**التحقق:**
- نوع الملف: PDF فقط
- الحجم الأقصى: 10 ميجابايت
- مطلوب: نعم

### 3. الطباعة

عند الضغط على زر "طباعة":
- إذا كان هناك PDF مرفوع → يتم تحميله مباشرة
- إذا لم يكن هناك PDF → يتم استخدام القالب HTML التقليدي

## بنية قاعدة البيانات

### جدول `service_requests`

تمت إضافة الحقول التالية:

```sql
editable_word_path VARCHAR(255) NULL    -- مسار ملف Word القابل للتعبئة
final_pdf_path VARCHAR(255) NULL        -- مسار ملف PDF النهائي
filled_by_admin_id INT NULL             -- معرف الموظف الذي قام بالتعبئة
filled_at TIMESTAMP NULL                -- تاريخ رفع PDF
```

## الملفات المعدلة/المضافة

### Services

**جديد:**
- `packages/Najaz/Service/src/Services/WordDocumentService.php`

**معدل:**
- `packages/Najaz/Service/src/Services/DocumentTemplateService.php`

### Controllers

**معدل:**
- `packages/Najaz/Admin/src/Http/Controllers/Admin/ServiceRequests/ServiceRequestController.php`
  - `printDocument()`: محدث ليستخدم PDF المحفوظ
  - `downloadEditableWord()`: جديد
  - `uploadFilledPDF()`: جديد

### Models

**معدل:**
- `packages/Najaz/Request/src/Models/ServiceRequest.php`
  - إضافة الحقول الجديدة
  - إضافة علاقة `filledByAdmin()`

### Routes

**معدل:**
- `packages/Najaz/Admin/src/Routes/service-request-routes.php`

```php
Route::get('download-word/{id}', 'downloadEditableWord')
    ->name('admin.service-requests.download-word');

Route::post('upload-pdf/{id}', 'uploadFilledPDF')
    ->name('admin.service-requests.upload-pdf');
```

### Views

**معدل:**
- `packages/Najaz/Admin/src/Resources/views/service-requests/view.blade.php`
  - إضافة زر تحميل Word في قسم "قيد المعالجة"
  - إضافة تبويب "معالجة الوثيقة"

### Translations

**معدل:**
- `packages/Najaz/Admin/src/Resources/lang/ar/app.php`

النصوص الجديدة في `service-requests.word-document`:
- `download-word`: تحميل ملف Word
- `upload-pdf`: رفع ملف PDF
- `status`: حالة الوثيقة
- `instructions`: التعليمات
- وغيرها...

### Migrations

**جديد:**
- `packages/Najaz/Request/src/Database/Migrations/2025_12_02_100000_add_word_pdf_fields_to_service_requests_table.php`

## التدفق الكامل

### 1. إنشاء الخدمة

عند إنشاء خدمة جديدة:
- أضف حقول من نوع `file` أو `image` في مجموعات السمات
- أنشئ قالب وثيقة للخدمة في "قوالب الخدمات"

### 2. تقديم الطلب (المواطن)

- المواطن يملأ النموذج
- يرفع الملفات/الصور المطلوبة
- يقدم الطلب

### 3. معالجة الطلب (الموظف)

**أ. تغيير الحالة إلى "قيد المعالجة":**
- افتح طلب الخدمة
- اضغط زر "قيد المعالجة"

**ب. تحميل ملف Word:**
- في قسم الأزرار العلوية، اضغط "تحميل ملف Word القابل للتعبئة"
- سيتم تحميل الملف تلقائياً

**ج. تعبئة الملف:**
- افتح الملف المحمل
- راجع الملفات/الصور المرفوعة في النظام
- املأ الحقول الفارغة بالبيانات المستخرجة

**د. تحويل إلى PDF:**
- في Microsoft Word: File → Save As → PDF
- أو استخدم أي أداة تحويل أخرى

**هـ. رفع PDF:**
- ارجع إلى صفحة الطلب في النظام
- افتح تبويب "معالجة الوثيقة"
- اختر ملف PDF المعبأ
- اضغط "رفع"

**و. إكمال الطلب:**
- اضغط زر "إكمال" لتغيير حالة الطلب

### 4. الطباعة

عند إكمال الطلب:
- اضغط زر "طباعة"
- سيتم تحميل PDF المعبأ (إذا كان موجود)
- أو سيتم توليد PDF من القالب (إذا لم يكن هناك PDF مرفوع)

## الأمان

### Validation

- **نوع الملف:** PDF فقط (`.pdf`)
- **الحجم:** حد أقصى 10 ميجابايت
- **الصلاحيات:** يجب أن يكون الموظف مسجل دخول ولديه صلاحية الوصول

### Storage

- الملفات محفوظة في `storage/app/service_requests/{request_id}/`
- لكل طلب مجلد منفصل
- التسمية: `editable-{increment_id}.docx` و `final-{increment_id}.pdf`

## الأخطاء الشائعة وحلولها

### 1. خطأ "Template not found"

**السبب:** القالب غير موجود أو غير نشط

**الحل:** 
- تأكد من وجود قالب للخدمة
- تأكد أن `is_active = true` في جدول `service_document_templates`

### 2. خطأ "No file fields"

**السبب:** الخدمة لا تحتوي على حقول ملفات أو صور

**الحل:**
- أضف حقول من نوع `file` أو `image` في مجموعات السمات للخدمة

### 3. فشل تحميل Word

**السبب:** مشكلة في مكتبة PHPWord أو الصلاحيات

**الحل:**
```bash
composer require phpoffice/phpword
php artisan storage:link
chmod -R 775 storage/app/service_requests
```

### 4. فشل رفع PDF

**السبب:** حجم الملف كبير أو نوع خاطئ

**الحل:**
- تأكد أن الملف PDF وليس Word
- تأكد أن الحجم أقل من 10 ميجابايت
- تحقق من `upload_max_filesize` في `php.ini`

## Logging

يتم تسجيل العمليات التالية:

```php
\Log::info('WordDocumentService: Word document generated', [...]);
\Log::error('Failed to download Word document', [...]);
\Log::error('Failed to upload PDF document', [...]);
```

تحقق من الـ logs في `storage/logs/laravel.log`

## API Endpoints

### 1. تحميل Word

```
GET /admin/service-requests/download-word/{id}
```

**Response:**
- File download (`.docx`)

### 2. رفع PDF

```
POST /admin/service-requests/upload-pdf/{id}
Content-Type: multipart/form-data

Body:
- filled_pdf: File (PDF)
```

**Response:**
```json
{
    "message": "تم رفع ملف PDF بنجاح",
    "data": {
        "path": "service_requests/123/final-SR001.pdf",
        "filled_at": "2025-12-02 10:30:00",
        "filled_by": "Admin Name"
    }
}
```

## الصيانة

### تنظيف الملفات القديمة

يمكنك إنشاء command لتنظيف الملفات القديمة:

```bash
php artisan make:command CleanOldServiceRequestFiles
```

مثال:

```php
// حذف ملفات Word/PDF للطلبات المكتملة التي مر عليها أكثر من 6 أشهر
$oldRequests = ServiceRequest::where('status', 'completed')
    ->where('completed_at', '<', now()->subMonths(6))
    ->get();

foreach ($oldRequests as $request) {
    if ($request->editable_word_path) {
        Storage::delete($request->editable_word_path);
    }
    if ($request->final_pdf_path) {
        Storage::delete($request->final_pdf_path);
    }
}
```

## الخلاصة

هذا النظام يوفر طريقة مرنة لمعالجة الوثائق التي تحتوي على بيانات من ملفات/صور مرفوعة، مع الحفاظ على:
- **البساطة:** لا حاجة لمكتبات تحويل معقدة
- **المرونة:** الموظف يستخدم أدواته المفضلة
- **الجودة:** تحويل Word إلى PDF على الجهاز أفضل
- **الأمان:** رفع PDF فقط يقلل المخاطر

