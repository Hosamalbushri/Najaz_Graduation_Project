# إعادة هيكلة جداول طلبات الخدمات

## نظرة عامة

تم إعادة هيكلة جداول طلبات الخدمات لتكون مطابقة لنمط Bagisto الأصلي (مثل جدول `orders`). تم فصل بيانات الفورم إلى جدول منفصل وتخزين معلومات المواطن مباشرة في الجدول الرئيسي.

---

## التغييرات الرئيسية

### 1. تحديث جدول `service_requests`

تم إضافة الحقول التالية لتخزين معلومات المواطن مباشرة (مثل `customer_first_name` في جدول `orders`):

#### الحقول الجديدة:

- **`citizen_first_name`** (string, nullable)
  - الاسم الأول للمواطن
  - يتم نسخه من `citizens.first_name` عند إنشاء الطلب

- **`citizen_middle_name`** (string, nullable)
  - الاسم الأوسط للمواطن
  - يتم نسخه من `citizens.middle_name` عند إنشاء الطلب

- **`citizen_last_name`** (string, nullable)
  - الاسم الأخير للمواطن
  - يتم نسخه من `citizens.last_name` عند إنشاء الطلب

- **`citizen_national_id`** (string, nullable)
  - رقم البطاقة الشخصية للمواطن
  - يتم نسخه من `citizens.national_id` عند إنشاء الطلب

- **`citizen_type_name`** (string, nullable)
  - اسم نوع المواطن
  - يتم نسخه من `citizen_types.name` عند إنشاء الطلب

- **`locale`** (string, nullable)
  - اللغة الافتراضية للمواطن عند إنشاء الطلب
  - يتم نسخه من `app()->getLocale()` عند إنشاء الطلب

#### الحقول المحذوفة:

- **`form_data`** (json, nullable)
  - تم نقله إلى جدول منفصل `service_request_form_data`

---

### 2. إنشاء جدول `service_request_form_data`

جدول جديد لحفظ بيانات الفورم منظمة حسب المجموعات:

#### الحقول:

- **`id`** (integer, primary key)
  - المعرف الفريد

- **`service_request_id`** (integer, foreign key)
  - ربط بجدول `service_requests`
  - Foreign key مع `onDelete('cascade')`

- **`group_code`** (string)
  - كود المجموعة (customCode أو code)
  - مثال: `wife_data`, `husband_data`, `personal_data`

- **`group_name`** (string, nullable)
  - اسم المجموعة (customName أو name)
  - مثال: `بيانات الزوجة`, `بيانات الزوج`, `البيانات الشخصية`

- **`sort_order`** (integer, default: 0)
  - ترتيب المجموعة

**ملاحظة**: تم إزالة `fields_data` من الجدول. الجدول الآن يحفظ فقط معلومات المجموعة (الكود والاسم والترتيب).

- **`created_at`** (timestamp)
  - تاريخ الإنشاء

- **`updated_at`** (timestamp)
  - تاريخ التحديث

#### الفهارس (Indexes):

- `service_request_id`
- `service_request_id, group_code` (composite index)

---

## البنية الجديدة

### جدول `service_requests`

```sql
service_requests
├── id
├── service_id (FK)
├── citizen_id (FK)
├── status
├── citizen_first_name      ← جديد
├── citizen_middle_name      ← جديد
├── citizen_last_name        ← جديد
├── citizen_national_id      ← جديد
├── citizen_type_name        ← جديد
├── locale                   ← جديد
├── notes
├── admin_notes
├── assigned_to (FK)
├── submitted_at
├── completed_at
├── created_at
└── updated_at
```

### جدول `service_request_form_data`

```sql
service_request_form_data
├── id
├── service_request_id (FK)
├── group_code
├── group_name
├── sort_order
├── created_at
└── updated_at
```

**ملاحظة**: تم إزالة `fields_data` من الجدول بناءً على طلب المستخدم.

---

## العلاقات (Relationships)

### ServiceRequest Model

```php
// علاقة hasMany مع ServiceRequestFormData
public function formData(): HasMany
{
    return $this->hasMany(ServiceRequestFormData::class, 'service_request_id');
}
```

### ServiceRequestFormData Model

```php
// علاقة belongsTo مع ServiceRequest
public function serviceRequest(): BelongsTo
{
    return $this->belongsTo(ServiceRequestProxy::modelClass(), 'service_request_id');
}
```

---

## كيفية العمل

### 1. عند إنشاء طلب جديد

```php
// في ServiceRequestRepository::createServiceRequestIfNotThenRetry()

// 1. جلب معلومات المواطن
$citizen = CitizenProxy::modelClass()::findOrFail($citizenId);
$citizenTypeName = $citizen->citizenType ? $citizen->citizenType->name : null;

// 2. إنشاء الطلب مع معلومات المواطن
$request = $this->model->create([
    'service_id'         => $data['service_id'],
    'citizen_id'         => $citizenId,
    'status'             => 'pending',
    'citizen_first_name' => $citizen->first_name,
    'citizen_middle_name' => $citizen->middle_name,
    'citizen_last_name'  => $citizen->last_name,
    'citizen_national_id' => $citizen->national_id,
    'citizen_type_name'  => $citizenTypeName,
    'locale'             => app()->getLocale(),
    'notes'              => $data['notes'] ?? null,
    'submitted_at'       => now(),
]);

// 3. حفظ بيانات الفورم في جدول منفصل
$this->saveFormData($request, $service, $cleanedFormData);
```

### 2. حفظ بيانات الفورم

```php
// في ServiceRequestRepository::saveFormData()

foreach ($service->attributeGroups as $group) {
    $groupCode = $group->pivot->custom_code ?? $group->code;
    $groupName = $group->pivot->custom_name ?? $group->name ?? $groupCode;

    // التحقق من وجود بيانات لهذه المجموعة
    $hasData = false;

    if (isset($formData[$groupCode]) && is_array($formData[$groupCode]) && ! empty($formData[$groupCode])) {
        // Nested structure
        $hasData = true;
    } else {
        // Flat structure - التحقق من وجود أي حقل من هذه المجموعة
        foreach ($group->fields as $field) {
            $fieldCode = $field->code;
            if (isset($formData[$fieldCode])) {
                $hasData = true;
                break;
            }
        }
    }

    // حفظ معلومات المجموعة فقط (بدون البيانات)
    if ($hasData) {
        ServiceRequestFormData::create([
            'service_request_id' => $request->id,
            'group_code'        => $groupCode,
            'group_name'        => $groupName,
            'sort_order'        => $sortOrder++,
        ]);
    }
}
```

**ملاحظة**: الجدول يحفظ فقط معلومات المجموعة (الكود والاسم والترتيب)، وليس بيانات الحقول نفسها.

---

## المزايا

### 1. **مطابقة لنمط Bagisto**
- نفس النمط المستخدم في جدول `orders`
- تخزين معلومات المواطن مباشرة في الجدول الرئيسي
- الحفاظ على البيانات حتى لو تغيرت لاحقاً

### 2. **تنظيم أفضل**
- فصل بيانات الفورم عن البيانات الأساسية
- تنظيم البيانات حسب المجموعات
- سهولة الاستعلام والفلترة

### 3. **الأداء**
- فهارس محسّنة للاستعلامات السريعة
- تقليل حجم الجدول الرئيسي
- استعلامات أسرع على البيانات الأساسية

### 4. **المرونة**
- إمكانية إضافة حقول جديدة بسهولة
- دعم هيكل متداخل ومسطح
- حفظ ترتيب المجموعات

---

## أمثلة على الاستعلامات

### 1. جلب طلب مع بيانات الفورم

```php
$request = ServiceRequest::with('formData')
    ->find($id);

// الوصول لمعلومات المجموعات
foreach ($request->formData as $formData) {
    echo $formData->group_code;  // "wife_data"
    echo $formData->group_name;  // "بيانات الزوجة"
    echo $formData->sort_order;  // 0
}
```

### 2. البحث عن طلبات حسب اسم المواطن

```php
$requests = ServiceRequest::where('citizen_first_name', 'LIKE', '%علي%')
    ->orWhere('citizen_last_name', 'LIKE', '%علي%')
    ->get();
```

### 3. البحث عن طلبات حسب رقم البطاقة

```php
$requests = ServiceRequest::where('citizen_national_id', '1234567890')
    ->get();
```

### 4. البحث عن طلبات حسب نوع المواطن

```php
$requests = ServiceRequest::where('citizen_type_name', 'مواطن عادي')
    ->get();
```

### 5. جلب طلبات بلغة معينة

```php
$requests = ServiceRequest::where('locale', 'ar')
    ->get();
```

### 6. البحث عن طلبات حسب مجموعة معينة

```php
$requests = ServiceRequest::whereHas('formData', function ($query) {
    $query->where('group_code', 'wife_data');
})->get();
```

---

## Migration Files

### 1. `2025_01_20_000000_update_service_requests_table_add_citizen_info.php`

تحديث جدول `service_requests`:
- إضافة الحقول الجديدة
- حذف `form_data`

### 2. `2025_01_20_000001_create_service_request_form_data_table.php`

إنشاء جدول `service_request_form_data`:
- إنشاء الجدول الجديد
- إضافة الفهارس والعلاقات

---

## ملاحظات مهمة

### 1. **البيانات التاريخية**
- إذا كان لديك بيانات قديمة في `form_data`، ستحتاج إلى migration لتحويلها إلى الجدول الجديد

### 2. **GraphQL Schema**
- قد تحتاج إلى تحديث GraphQL schema لإضافة الحقول الجديدة
- إضافة `formData` كعلاقة في `ServiceRequest` type

### 3. **API Responses**
- تحديث الـ responses لإرجاع `formData` كعلاقة منفصلة
- إرجاع معلومات المواطن مباشرة من الجدول الرئيسي

---

## مثال على Response الجديد

### قبل التغيير:
```json
{
  "id": 1,
  "service_id": 1,
  "citizen_id": 1,
  "status": "pending",
  "form_data": {
    "wife_data": {
      "id_number": "1234567890",
      "citizen_name": "علي"
    },
    "husband_data": {
      "id_number": "0987654321",
      "citizen_name": "فاطمة"
    }
  }
}
```

### بعد التغيير:
```json
{
  "id": 1,
  "service_id": 1,
  "citizen_id": 1,
  "status": "pending",
  "citizen_first_name": "أحمد",
  "citizen_middle_name": "محمد",
  "citizen_last_name": "علي",
  "citizen_national_id": "1234567890",
  "citizen_type_name": "مواطن عادي",
  "locale": "ar",
  "formData": [
    {
      "id": 1,
      "service_request_id": 1,
      "group_code": "wife_data",
      "group_name": "بيانات الزوجة",
      "sort_order": 0,
      "created_at": "2025-01-20 10:00:00",
      "updated_at": "2025-01-20 10:00:00"
    },
    {
      "id": 2,
      "service_request_id": 1,
      "group_code": "husband_data",
      "group_name": "بيانات الزوج",
      "sort_order": 1,
      "created_at": "2025-01-20 10:00:00",
      "updated_at": "2025-01-20 10:00:00"
    }
  ]
}
```

---

## الخلاصة

تم إعادة هيكلة جداول طلبات الخدمات بنجاح لتكون:
- ✅ مطابقة لنمط Bagisto الأصلي
- ✅ منظمة بشكل أفضل
- ✅ أسرع في الاستعلامات
- ✅ أكثر مرونة للتطوير المستقبلي

