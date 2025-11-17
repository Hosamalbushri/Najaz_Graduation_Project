# نظام طلبات الخدمات والأطراف المستفيدة

## نظرة عامة

تم إنشاء نظام كامل لإدارة طلبات الخدمات من المواطنين، مع دعم نظام الأطراف المستفيدة (Beneficiaries) الذي يسمح لعدة مواطنين بمتابعة نفس الطلب.

### المميزات الرئيسية

- ✅ إنشاء طلبات خدمات من المواطنين
- ✅ نظام الأطراف المستفيدة (مثل: الزوج والزوجة في عقد الزواج)
- ✅ متابعة الطلبات من قبل صاحب الطلب والأطراف المستفيدة
- ✅ GraphQL API كامل للمواطنين
- ✅ ربط تلقائي بالأطراف المستفيدة بناءً على `is_notifiable` في مجموعات الخدمة
- ✅ التحقق التلقائي من الحقول المطلوبة قبل إنشاء الطلب
- ✅ رفض الحقول غير المرتبطة بالخدمة (الأمان)
- ✅ دعم `customCode` للمجموعات (مفتاح رئيسي ثابت)
- ✅ دعم الحقول المتداخلة والمسطحة
- ✅ تنظيف تلقائي لرقم الهوية (إزالة المسافات والشرطات)

---

## البنية الأساسية

### 1. حزمة Request

**الموقع**: `packages/Najaz/Request/`

**المكونات**:
- `ServiceRequest` Model - نموذج طلب الخدمة
- `ServiceRequestRepository` - مستودع البيانات
- Migrations - جداول قاعدة البيانات

### 2. جداول قاعدة البيانات

#### جدول `service_requests`
```sql
- id
- service_id (FK → services)
- citizen_id (FK → citizens) - صاحب الطلب
- status (pending, in_progress, completed, rejected, cancelled)
- form_data (JSON) - البيانات المدخلة من النموذج
- notes - ملاحظات من المواطن
- admin_notes - ملاحظات من الأدمن
- assigned_to (FK → admins) - موظف معين
- submitted_at
- completed_at
- timestamps
```

#### جدول `service_request_beneficiaries`
```sql
- id
- service_request_id (FK → service_requests)
- citizen_id (FK → citizens) - الطرف المستفيد
- group_code - كود المجموعة (مثلاً: husband_data, wife_data)
- timestamps
- UNIQUE(service_request_id, citizen_id)
```

---

## نظام الأطراف المستفيدة

### كيف يعمل؟

1. **في نموذج الخدمة**: عند تعريف مجموعات البيانات (مثل: بيانات الزوج، بيانات الزوجة)، يمكن تحديد `is_notifiable = 1` في جدول الربط `service_attribute_group_service`.

2. **عند إنشاء الطلب**: النظام يقوم تلقائياً بـ:
   - فحص جميع المجموعات التي `is_notifiable = 1`
   - البحث عن حقل يحتوي على رقم الهوية في كل مجموعة (يدعم: `id_number`, `national_id`, `citizen_id`, وغيرها)
   - استخراج رقم الهوية من `form_data` باستخدام `customCode` (أو `code` كـ fallback)
   - تنظيف رقم الهوية (إزالة المسافات والشرطات)
   - البحث عن المواطن برقم الهوية في قاعدة البيانات
   - ربطه بالطلب كطرف مستفيد مع `group_code`

3. **النتيجة**: الأطراف المستفيدة يمكنهم:
   - رؤية الطلب في `myServiceRequests`
   - متابعة حالة الطلب
   - استقبال إشعارات عند تحديث الطلب (في المستقبل)

### الحقول المدعومة لرقم الهوية

النظام يبحث تلقائياً عن الحقول التالية في كل مجموعة `is_notifiable`:
- `id_number` ✅
- `national_id` ✅
- `citizen_id` ✅
- `nationalId` ✅
- `citizenId` ✅
- `idNumber` ✅
- `national_number` ✅
- `identity_number` ✅

### مثال عملي: خدمة عقد زواج

```
الخدمة: عقد زواج
- الأمين الشرعي (نوع مواطن: أمين شرعي) → يقدم الطلب
- مجموعة "بيانات الزوج" (customCode: "husband_data") → is_notifiable = 1 → حقل: id_number → القيمة: 1234567890
- مجموعة "بيانات الزوجة" (customCode: "wife_data") → is_notifiable = 1 → حقل: id_number → القيمة: 0987654321

form_data المرسل:
{
  "husband_data": {
    "id_number": "1234567890"
  },
  "wife_data": {
    "id_number": "0987654321"
  }
}

النتيجة:
- الأمين الشرعي: يتابع الطلب (صاحب الطلب)
- الزوج (national_id: 1234567890): يتابع الطلب (طرف مستفيد من مجموعة husband_data)
- الزوجة (national_id: 0987654321): تتابع الطلب (طرف مستفيد من مجموعة wife_data)
```

### متطلبات إنشاء الأطراف المستفيدة

1. ✅ `is_notifiable = 1` في `service_attribute_group_service`
2. ✅ وجود حقل رقم الهوية في المجموعة (مثل: `id_number`, `national_id`)
3. ✅ إرسال رقم الهوية في `form_data` باستخدام `customCode`
4. ✅ المواطن موجود في قاعدة البيانات برقم الهوية المرسل

---

## GraphQL API

### الاستعلامات (Queries)

#### 1. جلب جميع طلبات المواطن

```graphql
query MyServiceRequests {
  myServiceRequests {
    id
    status
    service {
      id
      name
    }
    citizen {
      firstName
      lastName
    }
    beneficiaries {
      id
      firstName
      lastName
      nationalId
    }
    formData
    notes
    createdAt
  }
}
```

**الفلترة**:
```graphql
query FilteredRequests {
  myServiceRequests(
    serviceId: 1
    status: PENDING
  ) {
    id
    status
  }
}
```

#### 2. جلب طلب محدد

```graphql
query GetServiceRequest {
  myServiceRequest(id: 5) {
    id
    status
    service {
      id
      name
      form {
        groups {
          code
          label
          fields {
            code
            label
            type
          }
        }
      }
    }
    formData
    beneficiaries {
      id
      firstName
      lastName
    }
    assignedAdmin {
      id
      name
    }
  }
}
```

**ملاحظة**: يعيد الطلب إذا كان المواطن:
- صاحب الطلب (`citizen_id`)
- أو طرف مستفيد (`beneficiaries`)

---

### الميوتشنات (Mutations)

#### 1. إنشاء طلب خدمة جديد

```graphql
# استخدام Variables (المُوصى به)
mutation CreateServiceRequest($input: CreateServiceRequestInput!) {
  createServiceRequest(input: $input) {
    success
    message
    request {
      id
      status
      service {
        name
      }
      beneficiaries {
        id
        firstName
        lastName
        nationalId
      }
    }
  }
}
```

**Variables**:
```json
{
  "input": {
    "serviceId": 1,
    "formData": {
      "husband_data": {
        "id_number": "1234567890",
        "citizen_name": "علي حسن"
      },
      "wife_data": {
        "id_number": "0987654321",
        "citizen_name": "فاطمة أحمد"
      }
    },
    "notes": "طلب عقد زواج"
  }
}
```

**أو استخدام JSON String مباشر**:
```graphql
mutation CreateServiceRequest {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: "{\"husband_data\":{\"id_number\":\"1234567890\",\"citizen_name\":\"علي حسن\"},\"wife_data\":{\"id_number\":\"0987654321\",\"citizen_name\":\"فاطمة أحمد\"}}"
      notes: "طلب عقد زواج"
    }
  ) {
    success
    message
    request {
      id
      status
      beneficiaries {
        id
        firstName
        lastName
      }
    }
  }
}
```

**ما يحدث تلقائياً**:
1. التحقق من أن الخدمة متاحة لنوع المواطن
2. **التحقق من الحقول المطلوبة** - التأكد من وجود جميع الحقول الإلزامية في `form_data`
3. **التحقق من قواعد التحقق** - التحقق من صحة القيم حسب `validation_rules`
4. **تنظيف الحقول غير المرتبطة** - إزالة تلقائية لأي حقول غير موجودة في نموذج الخدمة (يتم تجاهلها بصمت) ⚠️
5. إنشاء الطلب بحالة `pending`
6. **استخراج الأطراف المستفيدة** من `form_data` بناءً على `is_notifiable`:
   - البحث عن المجموعات التي `is_notifiable = 1`
   - البحث عن حقل رقم الهوية في كل مجموعة
   - استخراج رقم الهوية من `form_data` باستخدام `customCode`
   - تنظيف رقم الهوية (إزالة المسافات والشرطات)
   - البحث عن المواطن في قاعدة البيانات
   - ربطه بالطلب كطرف مستفيد
7. ربط الأطراف بالطلب

**في حالة وجود حقول ناقصة أو غير صحيحة**: سيتم إرجاع رسالة خطأ واضحة توضح المشكلة.

**الحقول غير المرتبطة**: يتم تجاهلها تلقائياً بدون رسالة خطأ. يتم حفظ البيانات الصحيحة فقط.

**ملاحظة**: إذا لم يتم إنشاء الأطراف المستفيدة، راجع `docs/troubleshooting-beneficiaries.md`.

#### 2. تحديث طلب خدمة

```graphql
# استخدام Variables (المُوصى به)
mutation UpdateServiceRequest($id: ID!, $input: UpdateServiceRequestInput!) {
  updateServiceRequest(id: $id, input: $input) {
    success
    message
    request {
      id
      status
      formData
    }
  }
}
```

**Variables**:
```json
{
  "id": 5,
  "input": {
    "formData": {
      "husband_data": {
        "id_number": "1234567890",
        "citizen_name": "علي حسن محمد"
      }
    },
    "notes": "تم تحديث بيانات الزوج"
  }
}
```

**أو JSON String**:
```graphql
mutation UpdateServiceRequest {
  updateServiceRequest(
    id: 5
    input: {
      formData: "{\"husband_data\":{\"id_number\":\"1234567890\",\"citizen_name\":\"علي حسن محمد\"}}"
      notes: "تم تحديث بيانات الزوج"
    }
  ) {
    success
    message
    request {
      id
      status
    }
  }
}
```

**الشروط**:
- المواطن يجب أن يكون صاحب الطلب (ليس طرف مستفيد)
- الحالة يجب أن تكون `pending` أو `in_progress`

#### 3. إلغاء طلب خدمة

```graphql
mutation CancelServiceRequest {
  cancelServiceRequest(id: 5) {
    success
    message
  }
}
```

**الشروط**:
- المواطن يجب أن يكون صاحب الطلب
- الحالة يجب أن تكون `pending` أو `in_progress`

---

## العلاقات (Relationships)

### ServiceRequest Model

```php
// صاحب الطلب
$request->citizen

// الخدمة
$request->service

// الموظف المعين
$request->assignedAdmin

// الأطراف المستفيدة
$request->beneficiaries
```

### Citizen Model

```php
// الطلبات التي أرسلها المواطن
$citizen->serviceRequests (سيتم إضافتها لاحقاً)

// الطلبات التي هو طرف مستفيد فيها
$citizen->serviceRequestsAsBeneficiary
```

---

## منطق استخراج الأطراف المستفيدة

### الخوارزمية

```php
1. تحميل الخدمة مع attributeGroups و fields (مع pivot data)
2. فلترة المجموعات: is_notifiable = 1
3. لكل مجموعة notifiable:
   a. استخدام customCode (أو code كـ fallback)
   b. البحث عن حقل رقم الهوية (id_number, national_id, citizen_id, etc.)
   c. استخراج القيمة من form_data باستخدام customCode
   d. تنظيف رقم الهوية (إزالة المسافات والشرطات)
   e. البحث عن المواطن في قاعدة البيانات برقم الهوية
   f. ربطه بالطلب مع group_code (customCode)
```

### مثال على form_data

```json
{
  "husband_data": {
    "id_number": "1234567890",
    "citizen_name": "علي حسن"
  },
  "wife_data": {
    "id_number": "0987654321",
    "citizen_name": "فاطمة أحمد"
  },
  "marriage_date": "2025-01-01"
}
```

**كيف يعمل**:

إذا كانت:
- مجموعة `husband_data` (customCode) → `is_notifiable = 1` → حقل `id_number`
- مجموعة `wife_data` (customCode) → `is_notifiable = 1` → حقل `id_number`

سيتم ربط:
- المواطن برقم `1234567890` كطرف مستفيد من مجموعة `husband_data`
- المواطن برقم `0987654321` كطرف مستفيد من مجموعة `wife_data`

**ملاحظة**: النظام يستخدم `customCode` كمفتاح رئيسي، ويدعم `code` الأصلي كـ fallback.

---

## حالات الطلب (Status)

- `pending` - قيد الانتظار
- `in_progress` - قيد المعالجة
- `completed` - مكتمل
- `rejected` - مرفوض
- `cancelled` - ملغي

---

## الأمان والصلاحيات

### Guard Protection

جميع الاستعلامات والميوتشنات محمية بـ `@guard(with: ["citizen-api"])`:

- ✅ المواطن يجب أن يكون مسجلاً دخول
- ✅ المواطن يمكنه فقط:
  - رؤية طلباته
  - رؤية الطلبات التي هو طرف مستفيد فيها
  - تحديث/إلغاء طلباته فقط (ليس طلبات الأطراف المستفيدة)

### التحقق من الخدمة

عند إنشاء طلب:
- ✅ التحقق من أن الخدمة متاحة لنوع المواطن (`citizen_type_id`)
- ✅ رفض الطلب إذا لم تكن الخدمة متاحة

---

## أمثلة الاستخدام الكاملة

### سيناريو: عقد زواج

#### 1. الأمين الشرعي يقدم الطلب

```graphql
mutation CreateMarriageRequest {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: {
        "husband_national_id": "1234567890"
        "husband_full_name": "علي حسن محمد"
        "husband_date_of_birth": "1990-05-15"
        "wife_national_id": "0987654321"
        "wife_full_name": "فاطمة أحمد سالم"
        "wife_date_of_birth": "1992-08-20"
        "marriage_date": "2025-01-01"
        "marriage_location": "المحكمة الشرعية"
      }
      notes: "طلب عقد زواج جديد"
    }
  ) {
    success
    message
    request {
      id
      status
      service {
        name
      }
      citizen {
        firstName
        lastName
      }
      beneficiaries {
        id
        firstName
        lastName
        nationalId
      }
    }
  }
}
```

#### 2. الزوج يتابع طلبه

```graphql
query MyRequests {
  myServiceRequests {
    id
    status
    service {
      name
    }
    citizen {
      firstName
      lastName
    }
    formData
    createdAt
  }
}
```

**النتيجة**: سيرى الطلب لأنه طرف مستفيد (رقم هويته موجود في `husband_national_id`)

#### 3. الزوجة تتابع طلبها

نفس الاستعلام أعلاه - سترى الطلب لأنها طرف مستفيد أيضاً.

---

## استخدام حقول JSON في GraphQL

### ⚠️ مهم: formData من نوع JSON!

`formData` من نوع `JSON!` في GraphQL schema، لذلك يجب إرساله كـ **string يحتوي على JSON** وليس كـ object مباشر.

### الطريقة المُوصى به: استخدام Variables ✅

```graphql
mutation CreateServiceRequest($input: CreateServiceRequestInput!) {
  createServiceRequest(input: $input) {
    success
    message
  }
}
```

**Variables**:
```json
{
  "input": {
    "serviceId": 1,
    "formData": {
      "wife_data": {
        "id_number": "1234567890"
      }
    }
  }
}
```

### الطريقة البديلة: JSON String

```graphql
mutation CreateServiceRequest {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: "{\"wife_data\":{\"id_number\":\"1234567890\"}}"
    }
  ) {
    success
  }
}
```

**لمزيد من التفاصيل**: راجع `docs/graphql-json-field-usage.md`

---

## التحقق من الحقول (Field Validation)

### التحقق التلقائي

عند إنشاء طلب خدمة، يقوم النظام تلقائياً بـ:

1. **التحقق من الحقول المطلوبة**:
   - فحص جميع الحقول التي `is_required = true` في نموذج الخدمة
   - التأكد من وجود قيم غير فارغة في `form_data`
   - إرجاع قائمة واضحة بالحقول الناقصة

2. **التحقق من قواعد التحقق (Validation Rules)**:
   - إذا كان الحقل يحتوي على `validation_rules`، يتم التحقق منها
   - القواعد المدعومة:
     - `min`: الحد الأدنى (للأرقام أو طول النص)
     - `max`: الحد الأقصى (للأرقام أو طول النص)
     - `email`: التحقق من صحة البريد الإلكتروني
     - `regex`: التحقق من التنسيق باستخدام regex

3. **تنظيف الحقول غير المرتبطة بالخدمة** ⚠️:
   - فحص جميع الحقول المرسلة في `form_data`
   - إزالة تلقائية لأي حقول غير موجودة في نموذج الخدمة
   - الحقول غير المرتبطة يتم تجاهلها بصمت (بدون رسالة خطأ)
   - يتم حفظ البيانات الصحيحة فقط
   - هذا يضمن أن البيانات المحفوظة تطابق النموذج المحدد للخدمة فقط

### مثال على رسالة الخطأ

```json
{
  "errors": [
    {
      "message": "الحقول المطلوبة التالية مفقودة: اسم الزوج (husband_name) - المجموعة: بيانات الزوج, رقم هوية الزوجة (wife_national_id) - المجموعة: بيانات الزوجة"
    }
  ]
}
```

### مثال على التحقق من القيم

```json
{
  "errors": [
    {
      "message": "الحقول التالية تحتوي على قيم غير صحيحة: البريد الإلكتروني: البريد الإلكتروني غير صحيح, رقم الهاتف: النص يجب أن يكون على الأقل 10 حرف"
    }
  ]
}
```

### مثال على تنظيف الحقول غير المرتبطة

**البيانات المرسلة**:
```json
{
  "wife_data": {
    "id_number": "1234567890",  // ✅ سيتم حفظه
    "citizen_name": "علي",       // ✅ سيتم حفظه
    "unknown_field": "value"     // ⚠️ سيتم تجاهله بصمت
  },
  "extra_data": {               // ⚠️ سيتم تجاهله بصمت
    "some_field": "value"
  },
  "random_field": "value"       // ⚠️ سيتم تجاهله بصمت
}
```

**البيانات المحفوظة** (بعد التنظيف):
```json
{
  "wife_data": {
    "id_number": "1234567890",
    "citizen_name": "علي"
  }
}
```

**ملاحظة**: الحقول غير المرتبطة بالخدمة يتم تجاهلها تلقائياً بدون رسالة خطأ. يتم حفظ البيانات الصحيحة فقط.

### دعم الحقول المتداخلة و customCode

النظام يدعم **ثلاث طرق** لإرسال البيانات:

#### الطريقة 1: الهيكل المتداخل باستخدام customCode (المُوصى به) ✅

```json
{
  "wife_data": {        // ✅ customCode من النموذج
    "citizen_name": "علي",
    "id_number": "1234567890"
  },
  "husband_data": {     // ✅ customCode من النموذج
    "citizen_name": "محمد",
    "id_number": "0987654321"
  }
}
```

**المميزات**:
- ✅ واضح ومنظم
- ✅ يدعم نفس الحقل في مجموعات مختلفة
- ✅ يطابق هيكل النموذج

#### الطريقة 2: الهيكل المسطح

```json
{
  "citizen_name": "علي",
  "id_number": "1234567890"
}
```

⚠️ **ملاحظة**: في الحالة المسطحة، إذا كان نفس الحقل موجود في أكثر من مجموعة، سيتم استخدام نفس القيمة لجميع المجموعات.

#### الطريقة 3: Dot Notation

```json
{
  "wife_data.citizen_name": "علي",
  "husband_data.id_number": "1234567890"
}
```

### أولوية البحث

النظام يبحث عن القيم بالترتيب التالي:

1. **المتداخل مع customCode**: `formData[customCode][fieldCode]` (مثل `formData["wife_data"]["citizen_name"]`)
2. **المتداخل مع code**: `formData[code][fieldCode]` (مثل `formData["hosam"]["citizen_name"]`) - fallback
3. **المسطح**: `formData[fieldCode]` (مثل `formData["citizen_name"]`)
4. **Dot Notation**: `formData["customCode.fieldCode"]` أو `formData["code.fieldCode"]`

### ⚠️ مهم: استخدام customCode

**استخدم `customCode` من النموذج كمفتاح رئيسي** في `form_data`:

- ✅ صحيح: `"wife_data": { "citizen_name": "..." }` (customCode)
- ✅ صحيح أيضاً: `"hosam": { "citizen_name": "..." }` (code - fallback)
- ❌ تجنب: استخدام `code` عندما يوجد `customCode` (لأنه قد يتغير)

---

## التطبيق (Migration)

```bash
php artisan migrate
```

سيتم إنشاء:
- ✅ جدول `service_requests`
- ✅ جدول `service_request_beneficiaries`

---

## ملاحظات مهمة

1. **الأطراف المستفيدة**: 
   - يتم استخراجها تلقائياً عند إنشاء الطلب فقط
   - إذا تم تحديث `form_data` لاحقاً، لن يتم تحديث الأطراف تلقائياً
   - يجب تفعيل `is_notifiable = 1` في `service_attribute_group_service` للمجموعات المطلوبة
   - يجب أن يكون حقل رقم الهوية موجوداً في كل مجموعة `is_notifiable`

2. **حقل رقم الهوية**: النظام يبحث تلقائياً عن:
   - `id_number` ✅ (الأكثر شيوعاً)
   - `national_id`
   - `citizen_id`
   - `nationalId`, `citizenId`, `idNumber`
   - `national_number`, `identity_number`

3. **تنظيف رقم الهوية**: النظام ينظف رقم الهوية تلقائياً:
   - إزالة المسافات: `"1234 5678 90"` → `"1234567890"`
   - إزالة الشرطات: `"1234-5678-90"` → `"1234567890"`
   - إزالة underscores: `"1234_5678_90"` → `"1234567890"`

4. **منع التكرار**: لا يمكن ربط نفس المواطن مرتين في نفس الطلب (unique constraint).

5. **الحذف التلقائي**: عند حذف طلب، يتم حذف جميع الأطراف المستفيدة تلقائياً (cascade).

6. **استخدام customCode**: 
   - استخدم `customCode` من النموذج كمفتاح رئيسي في `form_data`
   - النظام يدعم `code` الأصلي كـ fallback، لكن `customCode` هو المفتاح المُوصى به
   - `customCode` يبقى ثابتاً حتى لو تغير `code` الأصلي

7. **الحقول غير المرتبطة بالخدمة**: 
   - النظام يتجاهل تلقائياً أي حقول غير موجودة في نموذج الخدمة (بدون رسالة خطأ)
   - يتم حفظ البيانات الصحيحة فقط
   - هذا يضمن أن البيانات المحفوظة تطابق النموذج المحدد للخدمة
   - يتم تنظيف:
     - المجموعات (groups) - المجموعات غير الموجودة يتم تجاهلها
     - الحقول داخل المجموعات - الحقول غير الموجودة في تلك المجموعة يتم تجاهلها
     - الحقول المسطحة - الحقول غير الموجودة في أي مجموعة يتم تجاهلها

---

## التطوير المستقبلي

- [ ] إشعارات للأطراف المستفيدة عند تحديث حالة الطلب
- [ ] إضافة/حذف أطراف مستفيدة يدوياً من الأدمن
- [ ] تحديث تلقائي للأطراف عند تحديث `form_data`
- [ ] تقارير وإحصائيات للطلبات
- [ ] نظام تعليقات على الطلبات

---

## استكشاف الأخطاء

إذا لم يتم إنشاء الأطراف المستفيدة، راجع ملف التوثيق الكامل:
- **`docs/troubleshooting-beneficiaries.md`** - دليل شامل لاستكشاف أخطاء الأطراف المستفيدة

### قائمة التحقق السريعة:

- [ ] `is_notifiable = 1` في `service_attribute_group_service`
- [ ] وجود حقل `id_number` أو `national_id` في المجموعات `is_notifiable`
- [ ] استخدام `customCode` في `form_data` (مثل `wife_data`, `husband_data`)
- [ ] إرسال `id_number` في `form_data` لكل مجموعة
- [ ] المواطن موجود في قاعدة البيانات برقم الهوية المرسل
- [ ] رقم الهوية يطابق `national_id` في جدول `citizens`

### أمثلة على المشاكل الشائعة:

**المشكلة 1**: `is_notifiable` غير مفعل
```sql
UPDATE service_attribute_group_service
SET is_notifiable = 1
WHERE service_id = 1 AND service_attribute_group_id = 5;
```

**المشكلة 2**: استخدام `code` بدلاً من `customCode`
```json
// ❌ خطأ
{ "hosam": { "id_number": "1234567890" } }

// ✅ صحيح
{ "wife_data": { "id_number": "1234567890" } }
```

---

## الملفات المرجعية

### الكود الأساسي:
- **Migration**: `packages/Najaz/Request/src/Database/Migrations/`
- **Model**: `packages/Najaz/Request/src/Models/ServiceRequest.php`
- **Repository**: `packages/Najaz/Request/src/Repositories/ServiceRequestRepository.php`
- **GraphQL Schema**: `packages/Najaz/GraphQLAPI/src/graphql/app/citizen/service-request.graphql`
- **Queries**: `packages/Najaz/GraphQLAPI/src/Queries/App/Citizen/ServiceRequestQuery.php`
- **Mutations**: `packages/Najaz/GraphQLAPI/src/Mutations/App/Citizen/ServiceRequestMutation.php`

### التوثيق:
- **`docs/service-requests-system.md`** - هذا الملف (نظرة عامة)
- **`docs/how-to-send-service-request.md`** - كيفية إرسال طلب خدمة
- **`docs/how-to-know-field-names.md`** - كيفية معرفة أسماء الحقول
- **`docs/example-service-request.md`** - أمثلة عملية كاملة
- **`docs/graphql-json-field-usage.md`** - كيفية استخدام حقول JSON في GraphQL
- **`docs/troubleshooting-beneficiaries.md`** - استكشاف أخطاء الأطراف المستفيدة

