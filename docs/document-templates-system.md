# نظام قوالب الوثائق الرسمية

## نظرة عامة

نظام قوالب الوثائق الرسمية يسمح للمسؤولين بإنشاء قوالب وثائق مخصصة لكل خدمة. هذه القوالب تحتوي على نص ثابت مع إمكانية إدراج حقول ديناميكية يتم استبدالها ببيانات المستخدم عند طلب الخدمة.

> **📄 توثيق طباعة الوثيقة:** راجع [توثيق تطبيق طريقة طباعة الفاتورة على طباعة الوثيقة](./document-printing-implementation.md) للتفاصيل الكاملة عن كيفية تطبيق نفس طريقة طباعة الفاتورة على طباعة الوثيقة.

## المميزات

- ✅ إنشاء قوالب وثائق مخصصة لكل خدمة
- ✅ إدراج حقول ديناميكية في النص (مثل `{{citizen_name}}` أو `<code data-field="field_code">`)
- ✅ دعم حقول من مجموعات السمات (Attribute Groups)
- ✅ إضافة صورة رأس ونص تذييل
- ✅ تحويل القوالب إلى PDF للطباعة
- ✅ واجهة إدارية منفصلة لإدارة القوالب
- ✅ محرر TinyMCE متقدم مع دعم HTML كامل
- ✅ محرر مرئي يعرض الحقول كـ badges بدلاً من الأكواد
- ✅ منع تعديل أكواد الحقول يدوياً لمنع الأخطاء
- ✅ مزامنة تلقائية بين المحرر المرئي والبيانات الفعلية
- ✅ حفظ HTML مع تنسيق كامل للطباعة
- ✅ استبدال تلقائي للحقول عند الطباعة في PDF
- ✅ حفظ تلقائي مع refresh للصفحة بعد النجاح

---

## البنية

### 1. قاعدة البيانات

#### جدول `service_document_templates`

```sql
CREATE TABLE service_document_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_id INT UNIQUE NOT NULL,
    template_content TEXT,
    available_fields JSON,
    used_fields JSON,
    header_image VARCHAR(255),
    footer_text TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);
```

**الحقول:**
- `service_id`: معرف الخدمة (فريد - كل خدمة لها قالب واحد فقط)
- `template_content`: محتوى القالب كـ HTML مع تاجات `<code data-field="field_code">` للحقول الديناميكية
- `available_fields`: قائمة الحقول المتاحة (JSON)
- `used_fields`: قائمة الحقول المستخدمة في القالب (JSON)
- `header_image`: رابط صورة الرأس/الشعار
- `footer_text`: نص التذييل
- `is_active`: حالة تفعيل القالب

**ملاحظة:** `template_content` يتم حفظه كـ HTML كامل مع تنسيق، والحقول الديناميكية يتم تمييزها بتاجات `<code data-field="field_code">Field Label</code>`.

---

### 2. النماذج (Models)

#### `ServiceDocumentTemplate`

**المسار:** `packages/Najaz/Service/src/Models/ServiceDocumentTemplate.php`

```php
class ServiceDocumentTemplate extends Model implements ServiceDocumentTemplateContract
{
    protected $table = 'service_document_templates';
    
    protected $fillable = [
        'service_id',
        'template_content',
        'available_fields',
        'used_fields',
        'header_image',
        'footer_text',
        'is_active',
    ];
    
    protected $casts = [
        'available_fields' => 'array',
        'used_fields'      => 'array',
        'is_active'        => 'boolean',
    ];
    
    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceProxy::modelClass(), 'service_id');
    }
}
```

**العلاقات:**
- `service()`: علاقة `belongsTo` مع `Service`

#### `ServiceDocumentTemplateProxy`

**المسار:** `packages/Najaz/Service/src/Models/ServiceDocumentTemplateProxy.php`

Proxy class للوصول إلى النموذج.

---

### 3. Controller

#### `DocumentTemplateController`

**المسار:** `packages/Najaz/Admin/src/Http/Controllers/Admin/Services/DocumentTemplateController.php`

**الدوال:**

##### `index()`
- **الوصف:** عرض قائمة قوالب الوثائق
- **المسار:** `GET /admin/services/document-templates`
- **البيانات المرسلة:** `$services` - قائمة الخدمات التي لا تملك قوالب

##### `getServicesWithoutTemplates()`
- **الوصف:** جلب الخدمات التي لا تملك قوالب (API)
- **المسار:** `GET /admin/services/document-templates/services-without-templates`
- **الاستجابة:** JSON array من الخدمات

##### `store()`
- **الوصف:** إنشاء قالب جديد فارغ
- **المسار:** `POST /admin/services/document-templates`
- **المعاملات:**
  - `service_id` (required): معرف الخدمة
- **الاستجابة:** JSON مع redirect إلى صفحة التعديل

##### `edit(int $id)`
- **الوصف:** عرض صفحة تعديل القالب
- **المسار:** `GET /admin/services/document-templates/{id}/edit`
- **البيانات المرسلة:**
  - `$template`: القالب
  - `$service`: الخدمة
  - `$availableFields`: قائمة الحقول المتاحة

##### `update(int $id)`
- **الوصف:** تحديث القالب
- **المسار:** `PUT /admin/services/document-templates/{id}`
- **المعاملات:**
  - `template_content` (required): محتوى القالب
  - `used_fields` (optional): الحقول المستخدمة
  - `header_image` (optional): صورة الرأس
  - `footer_text` (optional): نص التذييل
  - `is_active` (optional): حالة التفعيل

##### `destroy(int $id)`
- **الوصف:** حذف القالب
- **المسار:** `DELETE /admin/services/document-templates/{id}`

##### `buildAvailableFieldsForTemplate(Service $service, string $locale)`
- **الوصف:** بناء قائمة الحقول المتاحة للقالب
- **الحقول المضمنة:**
  - بيانات المواطن: `citizen_first_name`, `citizen_middle_name`, `citizen_last_name`, `citizen_national_id`, `citizen_type_name`
  - بيانات الطلب: `request_increment_id`, `request_date`, `current_date`
  - حقول مجموعات السمات: `{group_code}.{field_code}` و `{field_code}`

---

### 4. Routes

**المسار:** `packages/Najaz/Admin/src/Routes/service-routes.php`

```php
Route::group(['prefix' => 'document-templates'], function () {
    Route::controller(DocumentTemplateController::class)->group(function () {
        Route::get('', 'index')->name('admin.services.document-templates.index');
        Route::get('services-without-templates', 'getServicesWithoutTemplates')
            ->name('admin.services.document-templates.services-without-templates');
        Route::post('', 'store')->name('admin.services.document-templates.store');
        Route::get('{id}/edit', 'edit')->name('admin.services.document-templates.edit');
        Route::put('{id}', 'update')->name('admin.services.document-templates.update');
        Route::delete('{id}', 'destroy')->name('admin.services.document-templates.delete');
    });
});
```

---

### 5. Views

#### `index.blade.php`
**المسار:** `packages/Najaz/Admin/src/Resources/views/services/document-templates/index.blade.php`

- صفحة قائمة القوالب
- يحتوي على DataGrid لعرض القوالب
- زر "إنشاء قالب جديد" يفتح Modal

#### `create.blade.php`
**المسار:** `packages/Najaz/Admin/src/Resources/views/services/document-templates/create.blade.php`

- Modal منبثق لإنشاء قالب جديد
- يعرض قائمة الخدمات التي لا تملك قوالب
- عند اختيار خدمة، يتم إنشاء قالب فارغ والتوجيه إلى صفحة التعديل

#### `edit.blade.php`
**المسار:** `packages/Najaz/Admin/src/Resources/views/services/document-templates/edit.blade.php`

- صفحة تعديل القالب الكاملة
- Vue component واحد: `v-document-template-editor`
- **المكونات:**
  - العنوان وزر الحفظ في الأعلى
  - محرر TinyMCE متقدم مع دعم HTML كامل
  - حقول النموذج (صورة الرأس، نص التذييل، حالة التفعيل)
- **المميزات:**
  - محرر TinyMCE مع واجهة WYSIWYG كاملة
  - محرر مرئي يعرض الحقول كـ badges بدلاً من الأكواد
  - حفظ المحتوى كـ HTML مع تنسيق كامل
  - تمييز الحقول بتاجات `<code data-field="field_code">` في HTML المحفوظ
  - مزامنة تلقائية بين المحرر المرئي والبيانات الفعلية
  - استخراج تلقائي للحقول المستخدمة من تاجات `<code>`
  - حفظ مع refresh للصفحة بعد النجاح

---

### 6. DataGrid

#### `DocumentTemplateDataGrid`

**المسار:** `packages/Najaz/Admin/src/DataGrids/Services/DocumentTemplateDataGrid.php`

**الأعمدة:**
- `template_id`: رقم القالب
- `service_name`: اسم الخدمة
- `is_active`: الحالة (نشط/غير نشط)
- `created_at`: تاريخ الإنشاء

**الإجراءات:**
- تعديل
- حذف

---

### 7. Service Layer

#### `DocumentTemplateService`

**المسار:** `packages/Najaz/Service/src/Services/DocumentTemplateService.php`

**الدوال:**

##### `generateDocument(ServiceRequest $serviceRequest): string`
- **الوصف:** توليد HTML للوثيقة من القالب
- **المعاملات:** `ServiceRequest` object
- **الإرجاع:** HTML string

##### `generateAndDownloadPDF(ServiceRequest $serviceRequest): Response`
- **الوصف:** توليد PDF وتحميله
- **المعاملات:** `ServiceRequest` object
- **الإرجاع:** PDF download response

##### `getFieldValues(ServiceRequest $serviceRequest): array`
- **الوصف:** استخراج جميع القيم المتاحة للاستبدال
- **المعاملات:** `ServiceRequest` object
- **الإرجاع:** Array من القيم (key => value)

##### `replacePlaceholders(string $content, array $fieldValues): string`
- **الوصف:** استبدال الـ placeholders بالقيم الفعلية
- **يدعم:** 
  - التنسيق الجديد: `<code data-field="field_code">Field Label</code>`
  - التنسيق القديم: `{{field_code}}` (للتوافق مع القوالب القديمة)
- **المعاملات:**
  - `$content`: محتوى القالب (HTML)
  - `$fieldValues`: قائمة القيم
- **الإرجاع:** محتوى HTML بعد الاستبدال

##### `replaceCodeTags(string $content, array $fieldValues): string`
- **الوصف:** استبدال تاجات `<code data-field="field_code">` بقيم الحقول
- **المعاملات:**
  - `$content`: محتوى HTML
  - `$fieldValues`: قائمة القيم
- **الإرجاع:** HTML بعد استبدال التاجات
- **الأمان:** يتم escape قيم الحقول لمنع XSS

##### `buildHtmlDocument(string $content, ServiceDocumentTemplate $template): string`
- **الوصف:** بناء HTML كامل مع header و footer
- **المعاملات:**
  - `$content`: المحتوى بعد الاستبدال
  - `$template`: القالب
- **الإرجاع:** HTML كامل

---

## الحقول المتاحة

### حقول بيانات المواطن

- `citizen_first_name` - الاسم الأول
- `citizen_middle_name` - الاسم الأوسط
- `citizen_last_name` - الاسم الأخير
- `citizen_national_id` - رقم الهوية
- `citizen_type_name` - نوع المواطن

### حقول بيانات الطلب

- `request_increment_id` - رقم الطلب
- `request_date` - تاريخ الطلب
- `current_date` - التاريخ الحالي

### حقول مجموعات السمات

- `{group_code}.{field_code}` - حقل من مجموعة (مثل: `husband_data.citizen_name`)
- `{field_code}` - حقل مباشر (مثل: `citizen_name`)

### صيغ الاستخدام

**التنسيق الجديد (مُوصى به):**
```html
<p>الاسم: <code data-field="citizen_first_name">First Name</code></p>
```

**التنسيق القديم (للتوافق):**
```
الاسم: {{citizen_first_name}}
```

**ملاحظة:** التنسيق الجديد يحفظ HTML مع تنسيق كامل، مما يجعل الوثيقة مرتبة عند الطباعة.

---

## كيفية الاستخدام

### 1. إنشاء قالب جديد

1. انتقل إلى **قوالب الوثائق** من القائمة الجانبية
2. اضغط على **"إنشاء قالب جديد"**
3. اختر الخدمة من القائمة المنسدلة
4. سيتم إنشاء قالب فارغ والتوجيه إلى صفحة التعديل

### 2. تعديل القالب

1. في صفحة التعديل، استخدم محرر TinyMCE لكتابة النص المطلوب
2. لإدراج حقل ديناميكي:
   - اختر الحقل من القائمة المنسدلة في الأعلى
   - سيتم إدراج الحقل كـ badge مرئي في المحرر
   - يمكنك النقر على Badge لحذفه
3. استخدم أدوات TinyMCE لتنسيق النص (عريض، مائل، قوائم، إلخ)
4. أضف صورة رأس (اختياري)
5. أضف نص تذييل (اختياري)
6. احفظ القالب

**ملاحظة:** المحتوى يتم حفظه كـ HTML مع تنسيق كامل. الحقول يتم حفظها كـ `<code data-field="field_code">` في HTML.

### 3. استخدام القالب

1. عند طلب خدمة من قبل المستخدم
2. في صفحة عرض الطلب، اضغط على **"تحميل الوثيقة"**
3. سيتم توليد PDF مع البيانات الفعلية

---

## مثال على القالب

### في المحرر (مرئي - TinyMCE):
```
بسم الله الرحمن الرحيم

السيد/السيدة: [Badge: الاسم الأول] [Badge: الاسم الأوسط] [Badge: الاسم الأخير]

رقم الهوية: [Badge: رقم الهوية]

نوع المواطن: [Badge: نوع المواطن]

رقم الطلب: [Badge: رقم الطلب]

تاريخ الطلب: [Badge: تاريخ الطلب]

---

[المحتوى هنا مع تنسيق HTML]

---

[نص التذييل]
```

### في قاعدة البيانات (HTML محفوظ):
```html
<p>بسم الله الرحمن الرحيم</p>
<p>السيد/السيدة: <code data-field="citizen_first_name">First Name</code> <code data-field="citizen_middle_name">Middle Name</code> <code data-field="citizen_last_name">Last Name</code></p>
<p>رقم الهوية: <code data-field="citizen_national_id">National ID</code></p>
<p>نوع المواطن: <code data-field="citizen_type_name">Citizen Type</code></p>
<p>رقم الطلب: <code data-field="request_increment_id">Request ID</code></p>
<p>تاريخ الطلب: <code data-field="request_date">Request Date</code></p>
<hr>
<p>[المحتوى هنا مع تنسيق HTML]</p>
<hr>
<p>[نص التذييل]</p>
```

### عند الطباعة في PDF (بعد الاستبدال):
```html
<p>بسم الله الرحمن الرحيم</p>
<p>السيد/السيدة: أحمد محمد علي</p>
<p>رقم الهوية: 1234567890</p>
<p>نوع المواطن: مواطن</p>
<p>رقم الطلب: REQ-2025-001</p>
<p>تاريخ الطلب: 2025-01-20</p>
<hr>
<p>[المحتوى هنا مع تنسيق HTML]</p>
<hr>
<p>[نص التذييل]</p>
```

---

## التكامل مع ServiceRequest

### في `ServiceRequestController`

**المسار:** `packages/Najaz/Admin/src/Http/Controllers/Admin/ServiceRequests/ServiceRequestController.php`

تمت إضافة:
- `downloadDocument(int $id)`: تحميل PDF للوثيقة
- Route: `GET /admin/service-requests/{id}/download-document`

### في `view.blade.php`

**المسار:** `packages/Najaz/Admin/src/Resources/views/service-requests/view.blade.php`

تمت إضافة زر "تحميل الوثيقة" يظهر فقط إذا كان للخدمة قالب نشط.

---

## الترجمات

### العربية
**المسار:** `packages/Najaz/Admin/src/Resources/lang/ar/app.php`

```php
'services' => [
    'document-templates' => [
        'index' => [...],
        'create' => [...],
        'edit' => [...],
        'fields' => [...],
    ],
],
```

### الإنجليزية
**المسار:** `packages/Najaz/Admin/src/Resources/lang/en/app.php`

---

## القائمة الجانبية

تمت إضافة رابط "قوالب الوثائق" في القائمة الجانبية:

**المسار:** `packages/Najaz/Admin/src/Config/menu.php`

```php
[
    'key'   => 'services.document-templates',
    'name'  => 'Admin::app.components.layouts.sidebar.document-templates',
    'route' => 'admin.services.document-templates.index',
    'sort'  => 3,
    'icon'  => 'icon-sales',
],
```

---

## Migration

**المسار:** `packages/Najaz/Service/src/Database/Migrations/2025_01_20_100000_create_service_document_templates_table.php`

```bash
php artisan migrate
```

---

## ملاحظات مهمة

1. **كل خدمة لها قالب واحد فقط**: العلاقة `hasOne` بين `Service` و `ServiceDocumentTemplate`
2. **حفظ HTML**: المحتوى يتم حفظه كـ HTML كامل مع تنسيق، مما يجعل الوثيقة مرتبة عند الطباعة
3. **تمييز الحقول**: الحقول الديناميكية يتم تمييزها بتاجات `<code data-field="field_code">` في HTML المحفوظ
4. **استبدال الحقول**: عند الطباعة، يتم البحث عن تاجات `<code data-field>` واستبدالها بقيم الحقول تلقائياً
5. **التوافق مع القوالب القديمة**: النظام يدعم أيضاً التنسيق القديم `{{field_code}}` للتوافق مع القوالب القديمة
6. **استخراج الحقول**: يتم استخراج الحقول المستخدمة تلقائياً من تاجات `<code>` في HTML عند الحفظ
7. **PDF Generation**: يستخدم `PDFHandler` trait لتحويل HTML إلى PDF
8. **الأمان**: قيم الحقول يتم escape تلقائياً لمنع XSS
9. **البيانات**: يتم استخراج البيانات من:
   - `ServiceRequest` (المواطن، رقم الطلب، التاريخ)
   - `ServiceRequestFormData` (بيانات النموذج)

---

## استكشاف الأخطاء

### المشكلة: الحقول لا يتم استبدالها

**الحل:**
- تأكد من أن `fields_data` محفوظة بشكل صحيح في `service_request_form_data`
- تحقق من logs في `storage/logs/laravel.log`
- تأكد من أن أسماء الحقول في القالب تطابق أسماء الحقول في البيانات

### المشكلة: Modal لا يفتح

**الحل:**
- تأكد من أن Vue component محمّل بشكل صحيح
- تحقق من console للأخطاء
- تأكد من أن `$services` يتم تمريرها بشكل صحيح

### المشكلة: PDF فارغ

**الحل:**
- تحقق من أن القالب يحتوي على محتوى
- تأكد من أن `is_active = true`
- تحقق من logs لمعرفة الأخطاء

### المشكلة: المحتوى لا يُحفظ

**الحل:**
- تأكد من أن `syncContentFromEditor` يتم استدعاؤها قبل الحفظ
- تحقق من console للأخطاء في `updateTemplateContent`
- تأكد من أن `templateContent` يتم تحديثه بشكل صحيح

### المشكلة: الحقول لا تظهر كـ badges

**الحل:**
- تأكد من أن TinyMCE تم تحميله بشكل صحيح
- تحقق من أن `updateEditorContent` يتم استدعاؤها في `mounted` بعد تحميل TinyMCE
- تحقق من أن `availableFields` يحتوي على الحقول بشكل صحيح
- تأكد من أن `templateContent` يحتوي على HTML مع تاجات `<code>`

### المشكلة: تاجات `<code>` لا يتم استبدالها عند الطباعة

**الحل:**
- تحقق من أن `replaceCodeTags` يتم استدعاؤها في `replacePlaceholders`
- تحقق من logs في `storage/logs/laravel.log` لمعرفة ما إذا تم العثور على التاجات
- تأكد من أن `data-field` attribute موجود في تاجات `<code>`
- تحقق من أن أسماء الحقول في التاجات تطابق أسماء الحقول في `$fieldValues`

---

## محرر القوالب المرئي

### المميزات

تم تطوير محرر مرئي متقدم باستخدام TinyMCE:

1. **محرر TinyMCE متقدم**: محرر WYSIWYG كامل مع دعم HTML وتنسيق النص
2. **عرض الحقول بشكل مرئي**: بدلاً من عرض أكواد الحقول، يتم عرض الحقول كـ badges زرقاء باسم الحقل
3. **منع تعديل الأكواد**: المستخدم لا يمكنه تعديل أكواد الحقول يدوياً، مما يمنع الأخطاء
4. **حفظ HTML كامل**: المحتوى يتم حفظه كـ HTML مع تنسيق كامل للطباعة
5. **تمييز الحقول بتاجات HTML**: الحقول يتم حفظها كـ `<code data-field="field_code">` في HTML

### كيفية العمل

#### البنية:
- **TinyMCE Editor**: محرر HTML متقدم مع واجهة WYSIWYG
- **templateContent**: يحفظ HTML كامل مع تاجات `<code data-field="field_code">`
- **Visual Badges**: في المحرر، تاجات `<code>` تظهر كـ badges زرقاء قابلة للنقر

#### الدوال الرئيسية (Vue Component):

##### `getTinyMCEEditor()`
- **الوصف**: الحصول على instance من TinyMCE editor
- **الإرجاع**: TinyMCE editor object

##### `insertField(fieldCode)`
- **الوصف**: إدراج حقل جديد في المحرر
- **الخطوات**:
  1. مزامنة المحتوى الحالي من TinyMCE
  2. إدراج badge HTML في TinyMCE
  3. مزامنة المحتوى مرة أخرى لتحويل badge إلى `<code>` tag
  4. إرفاق event listeners للـ badge

##### `syncContentFromTinyMCE()`
- **الوصف**: مزامنة المحتوى من TinyMCE إلى `templateContent`
- **الخطوات**:
  1. الحصول على HTML من TinyMCE
  2. البحث عن badges (`<span data-field>`) في HTML
  3. استبدال badges بتاجات `<code data-field="field_code">`
  4. حفظ HTML في `templateContent`

##### `updateEditorContent()`
- **الوصف**: تحديث محتوى TinyMCE من `templateContent`
- **الخطوات**:
  1. قراءة HTML من `templateContent`
  2. البحث عن تاجات `<code data-field="field_code">`
  3. استبدالها بـ badges مرئية
  4. تحديث محتوى TinyMCE

##### `deleteField(fieldCode)`
- **الوصف**: حذف حقل من القالب
- **الخطوات**:
  1. مزامنة المحتوى من TinyMCE
  2. البحث عن تاجات `<code data-field="field_code">` في HTML
  3. حذفها
  4. تحديث المحرر

##### `saveTemplate()`
- **الوصف**: حفظ القالب
- **الخطوات**:
  1. مزامنة المحتوى من TinyMCE
  2. استخراج الحقول المستخدمة من تاجات `<code>`
  3. إرسال HTML إلى الخادم

### مثال على العرض المرئي

**في المحرر (مرئي - TinyMCE):**
```
السيد/السيدة: [Badge: الاسم الأول] [Badge: الاسم الأخير]
```

**في قاعدة البيانات (HTML محفوظ):**
```html
<p>السيد/السيدة: <code data-field="citizen_first_name">First Name</code> <code data-field="citizen_last_name">Last Name</code></p>
```

**عند الطباعة (بعد الاستبدال):**
```html
<p>السيد/السيدة: أحمد علي</p>
```

## التطوير المستقبلي

- [ ] إضافة معاينة للوثيقة قبل الحفظ
- [ ] دعم الصور في القالب
- [ ] إضافة حقول مخصصة
- [ ] دعم تنسيقات متعددة (HTML, DOCX)
- [ ] إضافة قوالب جاهزة
- [x] محرر مرئي للحقول (تم التنفيذ)
- [x] محرر TinyMCE مع دعم HTML كامل (تم التنفيذ)
- [x] حفظ HTML مع تنسيق كامل (تم التنفيذ)
- [x] استبدال تاجات `<code>` عند الطباعة (تم التنفيذ)

---

## الملفات المضافة/المعدلة

### ملفات جديدة:
1. `packages/Najaz/Service/src/Database/Migrations/2025_01_20_100000_create_service_document_templates_table.php`
2. `packages/Najaz/Service/src/Contracts/ServiceDocumentTemplate.php`
3. `packages/Najaz/Service/src/Models/ServiceDocumentTemplate.php`
4. `packages/Najaz/Service/src/Models/ServiceDocumentTemplateProxy.php`
5. `packages/Najaz/Service/src/Services/DocumentTemplateService.php`
6. `packages/Najaz/Admin/src/Http/Controllers/Admin/Services/DocumentTemplateController.php`
7. `packages/Najaz/Admin/src/DataGrids/Services/DocumentTemplateDataGrid.php`
8. `packages/Najaz/Admin/src/Resources/views/services/document-templates/index.blade.php`
9. `packages/Najaz/Admin/src/Resources/views/services/document-templates/create.blade.php`
10. `packages/Najaz/Admin/src/Resources/views/services/document-templates/edit.blade.php`

### ملفات معدلة:
1. `packages/Najaz/Service/src/Models/Service.php` - إضافة علاقة `documentTemplate()`
2. `packages/Najaz/Service/src/Providers/ModuleServiceProvider.php` - تسجيل النموذج
3. `packages/Najaz/Admin/src/Routes/service-routes.php` - إضافة routes
4. `packages/Najaz/Admin/src/Resources/views/services/edit.blade.php` - إضافة تبويب (اختياري)
5. `packages/Najaz/Admin/src/Http/Controllers/Admin/ServiceRequests/ServiceRequestController.php` - إضافة `downloadDocument()`
6. `packages/Najaz/Admin/src/Routes/service-request-routes.php` - إضافة route
7. `packages/Najaz/Admin/src/Resources/views/service-requests/view.blade.php` - إضافة زر التحميل
8. `packages/Najaz/Admin/src/Config/menu.php` - إضافة رابط القائمة
9. `packages/Najaz/Admin/src/Resources/lang/ar/app.php` - إضافة الترجمات
10. `packages/Najaz/Admin/src/Resources/lang/en/app.php` - إضافة الترجمات
11. `packages/Najaz/Admin/src/Resources/views/services/document-templates/edit.blade.php` - تحديث لاستخدام TinyMCE
12. `packages/Najaz/Service/src/Services/DocumentTemplateService.php` - إضافة `replaceCodeTags()` للتعامل مع تاجات `<code>`

---

## الدعم

للمساعدة أو الإبلاغ عن مشاكل، يرجى التواصل مع فريق التطوير.

