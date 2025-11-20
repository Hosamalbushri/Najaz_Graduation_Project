# توثيق تطبيق طريقة طباعة الفاتورة على طباعة الوثيقة

## نظرة عامة

تم تطبيق نفس طريقة طباعة الفاتورة في المبيعات على طباعة الوثيقة للخدمة لضمان الاتساق في الكود والتصميم.

---

## التغييرات المنفذة

### 1. تحديث Controller (`ServiceRequestController.php`)

**الموقع:** `packages/Najaz/Admin/src/Http/Controllers/Admin/ServiceRequests/ServiceRequestController.php`

#### التغييرات:

1. **إضافة PDFHandler Trait:**
```php
use Webkul\Core\Traits\PDFHandler;

class ServiceRequestController extends Controller
{
    use PDFHandler;
    // ...
}
```

2. **تغيير اسم Method:**
   - **قبل:** `downloadDocument(int $id)`
   - **بعد:** `printDocument(int $id)`

3. **تطبيق نفس طريقة الفاتورة:**
```php
public function printDocument(int $id)
{
    try {
        $serviceRequest = ServiceRequestProxy::modelClass()::with(['service.documentTemplate'])
            ->findOrFail($id);

        $template = $serviceRequest->service->documentTemplate;

        if (! $template || ! $template->is_active) {
            session()->flash('error', trans('Admin::app.service-requests.view.template-not-found'));
            return redirect()->back();
        }

        // Generate document content using DocumentTemplateService
        $documentService = new DocumentTemplateService();
        
        // Get field values and replace placeholders
        $fieldValues = $documentService->getFieldValues($serviceRequest);
        $content = $documentService->replacePlaceholders($template->template_content, $fieldValues);

        return $this->downloadPDF(
            view('admin::service-requests.pdf', compact('serviceRequest', 'template', 'content'))->render(),
            'document-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y')
        );
    } catch (\Exception $e) {
        session()->flash('error', $e->getMessage());
        return redirect()->back();
    }
}
```

**المقارنة مع InvoiceController:**
```php
// InvoiceController::printInvoice()
return $this->downloadPDF(
    view('admin::sales.invoices.pdf', compact('invoice'))->render(),
    'invoice-'.$invoice->created_at->format('d-m-Y')
);

// ServiceRequestController::printDocument()
return $this->downloadPDF(
    view('admin::service-requests.pdf', compact('serviceRequest', 'template', 'content'))->render(),
    'document-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y')
);
```

---

### 2. تحديث Routes (`service-request-routes.php`)

**الموقع:** `packages/Najaz/Admin/src/Routes/service-request-routes.php`

#### التغييرات:

**قبل:**
```php
Route::get('{id}/download-document', 'downloadDocument')->name('admin.service-requests.download-document');
```

**بعد:**
```php
Route::get('print/{id}', 'printDocument')->name('admin.service-requests.print');
```

**المقارنة مع Invoice Routes:**
```php
// Invoice Route
Route::get('print/{id}', 'printInvoice')->name('admin.sales.invoices.print');

// Service Request Route
Route::get('print/{id}', 'printDocument')->name('admin.service-requests.print');
```

---

### 3. إنشاء View Template للـ PDF (`pdf.blade.php`)

**الموقع:** `packages/Najaz/Admin/src/Resources/views/service-requests/pdf.blade.php`

#### المميزات:

1. **نفس البنية المستخدمة في فاتورة المبيعات:**
   - نفس الـ HTML structure
   - نفس الـ CSS styling
   - نفس دعم الخطوط واللغات (RTL/LTR)

2. **دعم الخطوط المتعددة:**
   - DejaVu Sans للغات RTL (العربية، الفارسية، إلخ)
   - خطوط مخصصة للصينية، اليابانية، الهندية، إلخ

3. **دعم صورة الرأس:**
```php
@if ($template->header_image)
    @php
        $imagePath = storage_path('app/public/' . $template->header_image);
        if (file_exists($imagePath)) {
            $imageData = base64_encode(file_get_contents($imagePath));
        }
    @endphp
    @if ($imageData)
        <img src="data:image/png;base64,{{ $imageData }}"/>
    @endif
@endif
```

4. **عرض المحتوى المعالج:**
```php
<div class="document-content">
    {!! $content !!}
</div>
```

5. **عرض التذييل مع التنسيق الكامل:**
```php
@if ($template->footer_text)
    <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; font-size: 12px; color: #666;">
        {!! $template->footer_text !!}
    </div>
@endif
```

**ملاحظة:** استخدام `{!! $template->footer_text !!}` بدلاً من `nl2br(e($template->footer_text))` للحفاظ على التنسيق HTML المحفوظ من TinyMCE.

---

### 4. تحديث View (`view.blade.php`)

**الموقع:** `packages/Najaz/Admin/src/Resources/views/service-requests/view.blade.php`

#### التغييرات:

**قبل:**
```php
<a
    href="{{ route('admin.service-requests.download-document', $request->id) }}"
    class="primary-button px-1 py-1.5"
    target="_blank"
>
    <span class="icon-download text-2xl"></span>
    @lang('Admin::app.service-requests.view.download-document')
</a>
```

**بعد:**
```php
<a
    href="{{ route('admin.service-requests.print', $request->id) }}"
    class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
>
    <span class="icon-printer text-2xl"></span>
    @lang('Admin::app.service-requests.view.print')
</a>
```

**المقارنة مع Invoice View:**
```php
// Invoice View
<a
    href="{{ route('admin.sales.invoices.print', $invoice->id) }}"
    class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
>
    <span class="icon-printer text-2xl"></span>
    @lang('admin::app.sales.invoices.view.print')
</a>
```

---

### 5. تحديث DocumentTemplateService

**الموقع:** `packages/Najaz/Service/src/Services/DocumentTemplateService.php`

#### التغييرات:

جعل الـ methods التالية `public` للوصول من Controller:

1. **`getFieldValues()`:**
```php
// قبل: protected function getFieldValues(...)
// بعد: public function getFieldValues(...)
```

2. **`replacePlaceholders()`:**
```php
// قبل: protected function replacePlaceholders(...)
// بعد: public function replacePlaceholders(...)
```

**السبب:** للسماح للـ Controller بالوصول المباشر إلى هذه الـ methods دون الحاجة لاستخدام `generateDocument()` الذي يرجع HTML كامل.

---

### 6. تحديث الترجمات

**الموقع:** 
- `packages/Najaz/Admin/src/Resources/lang/ar/app.php`
- `packages/Najaz/Admin/src/Resources/lang/en/app.php`

#### الترجمات المضافة:

**العربية:**
```php
'print' => 'طباعة',
'document' => 'الوثيقة',
'template-not-found' => 'القالب غير موجود أو غير نشط',
```

**الإنجليزية:**
```php
'print' => 'Print',
'document' => 'Document',
'template-not-found' => 'Template not found or inactive',
```

**الترجمات المحذوفة:**
- `download-document` (تم استبدالها بـ `print`)

---

## البنية النهائية

### Flow Chart:

```
User clicks "Print" button
    ↓
Route: admin.service-requests.print
    ↓
ServiceRequestController::printDocument()
    ↓
Load ServiceRequest with Template
    ↓
DocumentTemplateService::getFieldValues()
    ↓
DocumentTemplateService::replacePlaceholders()
    ↓
View: admin::service-requests.pdf
    ↓
PDFHandler::downloadPDF()
    ↓
PDF File Download
```

---

## الملفات المعدلة

### ملفات جديدة:
1. `packages/Najaz/Admin/src/Resources/views/service-requests/pdf.blade.php` - View template للـ PDF

### ملفات معدلة:
1. `packages/Najaz/Admin/src/Http/Controllers/Admin/ServiceRequests/ServiceRequestController.php`
   - إضافة `PDFHandler` trait
   - تغيير `downloadDocument()` إلى `printDocument()`
   - تحديث الـ implementation

2. `packages/Najaz/Admin/src/Routes/service-request-routes.php`
   - تغيير route من `download-document` إلى `print`

3. `packages/Najaz/Admin/src/Resources/views/service-requests/view.blade.php`
   - تحديث زر الطباعة (icon, style, route)

4. `packages/Najaz/Service/src/Services/DocumentTemplateService.php`
   - جعل `getFieldValues()` و `replacePlaceholders()` public

5. `packages/Najaz/Admin/src/Resources/lang/ar/app.php`
   - إضافة ترجمات جديدة

6. `packages/Najaz/Admin/src/Resources/lang/en/app.php`
   - إضافة ترجمات جديدة

---

## المميزات

### ✅ الاتساق في الكود
- نفس الـ pattern المستخدم في `InvoiceController`
- نفس الـ trait (`PDFHandler`)
- نفس الـ method name pattern (`printDocument` مثل `printInvoice`)

### ✅ الاتساق في التصميم
- نفس الـ styling للزر
- نفس الأيقونة (`icon-printer`)
- نفس الـ hover effects

### ✅ جودة الكود
- استخدام view template بدلاً من Service مباشرة
- فصل الـ concerns (Controller → Service → View)
- سهولة الصيانة والتطوير

### ✅ دعم التنسيق الكامل
- التذييل يطبع بنفس التنسيق المحفوظ (HTML من TinyMCE)
- دعم جميع تنسيقات النص (Bold, Italic, Colors, Lists, etc.)

---

## الاستخدام

### للمستخدمين:

1. **الوصول إلى صفحة طلب الخدمة:**
   - اذهب إلى `admin/service-requests`
   - اختر طلب خدمة
   - اضغط على زر "طباعة" (icon-printer)

2. **النتيجة:**
   - يتم تحميل ملف PDF تلقائياً
   - اسم الملف: `document-{increment_id}-{date}.pdf`
   - يحتوي على المحتوى المعالج مع الحقول المستبدلة

### للمطورين:

```php
// في Controller
public function printDocument(int $id)
{
    $serviceRequest = ServiceRequestProxy::modelClass()::with(['service.documentTemplate'])
        ->findOrFail($id);

    $template = $serviceRequest->service->documentTemplate;
    
    $documentService = new DocumentTemplateService();
    $fieldValues = $documentService->getFieldValues($serviceRequest);
    $content = $documentService->replacePlaceholders($template->template_content, $fieldValues);

    return $this->downloadPDF(
        view('admin::service-requests.pdf', compact('serviceRequest', 'template', 'content'))->render(),
        'document-'.$serviceRequest->increment_id.'-'.$serviceRequest->created_at->format('d-m-Y')
    );
}
```

---

## الاختبار

### Scenarios للاختبار:

1. ✅ طباعة وثيقة مع قالب نشط
2. ✅ محاولة طباعة وثيقة بدون قالب
3. ✅ محاولة طباعة وثيقة مع قالب غير نشط
4. ✅ التحقق من استبدال الحقول الديناميكية
5. ✅ التحقق من عرض صورة الرأس
6. ✅ التحقق من عرض التذييل مع التنسيق الكامل
7. ✅ التحقق من دعم RTL/LTR
8. ✅ التحقق من اسم الملف المحمل

---

## ملاحظات مهمة

1. **التنسيق المحفوظ:**
   - التذييل يتم عرضه باستخدام `{!! $template->footer_text !!}` للحفاظ على HTML
   - لا يتم استخدام `e()` أو `nl2br()` لتجنب فقدان التنسيق

2. **الأمان:**
   - المحتوى الرئيسي يتم معالجته من خلال `replacePlaceholders()` الذي يقوم بـ escape للقيم
   - التذييل يحتوي على HTML من TinyMCE (موثوق به)

3. **الأداء:**
   - استخدام `with(['service.documentTemplate'])` لتجنب N+1 queries
   - معالجة الصور باستخدام base64 encoding

---

## المراجع

- [PDFHandler Trait](../../packages/Webkul/Core/src/Traits/PDFHandler.php)
- [InvoiceController](../../packages/Webkul/Admin/src/Http/Controllers/Sales/InvoiceController.php)
- [Invoice PDF View](../../packages/Webkul/Admin/src/Resources/views/sales/invoices/pdf.blade.php)
- [Document Templates System Documentation](./document-templates-system.md)

---

## تاريخ التحديث

- **التاريخ:** 2025-01-XX
- **الإصدار:** 1.0.0
- **المطور:** AI Assistant

---

## الدعم

للمساعدة أو الإبلاغ عن مشاكل، يرجى التواصل مع فريق التطوير.

