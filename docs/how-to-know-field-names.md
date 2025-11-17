# كيفية معرفة أسماء الحقول في form_data

## المهم جداً ⚠️

**أسماء الحقول في `form_data` يجب أن تطابق `code` الموجود في قاعدة البيانات** (جدول `service_attribute_fields`).

لا يمكنك تخمين الأسماء! يجب أن تستعلم عن النموذج أولاً.

---

## الطريقة الصحيحة

### 1. استعلم عن نموذج الخدمة أولاً

```graphql
query GetServiceForm {
  citizenService(id: 1) {
    id
    name
    form {
      groups {
        code
        label
        fields {
          code          # ⭐ هذا هو الاسم الذي يجب استخدامه في form_data
          label
          type
          isRequired
          validationRules
        }
      }
    }
  }
}
```

### 2. مثال على الاستجابة

```json
{
  "data": {
    "citizenService": {
      "id": "1",
      "name": "عقد زواج",
      "form": {
        "groups": [
          {
            "code": "husband_data",
            "label": "بيانات الزوج",
            "fields": [
              {
                "code": "husband_national_id",    # ⭐ استخدم هذا الاسم
                "label": "رقم هوية الزوج",
                "type": "text",
                "isRequired": true
              },
              {
                "code": "husband_full_name",       # ⭐ استخدم هذا الاسم
                "label": "اسم الزوج الكامل",
                "type": "text",
                "isRequired": true
              }
            ]
          },
          {
            "code": "wife_data",
            "label": "بيانات الزوجة",
            "fields": [
              {
                "code": "wife_national_id",       # ⭐ استخدم هذا الاسم
                "label": "رقم هوية الزوجة",
                "type": "text",
                "isRequired": true
              }
            ]
          }
        ]
      }
    }
  }
}
```

### 3. استخدم الأسماء الصحيحة في form_data

بناءً على الاستجابة أعلاه، يجب أن يكون `form_data` كالتالي:

```graphql
mutation CreateServiceRequest {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: {
        "husband_national_id": "1234567890"      # ✅ مطابق لـ code
        "husband_full_name": "علي حسن محمد"      # ✅ مطابق لـ code
        "wife_national_id": "0987654321"         # ✅ مطابق لـ code
      }
    }
  ) {
    success
    message
  }
}
```

---

## كيف يعمل النظام

### 1. في قاعدة البيانات

الحقول تُخزن في جدول `service_attribute_fields`:

```sql
SELECT code, label, is_required 
FROM service_attribute_fields 
WHERE service_attribute_group_id IN (
  SELECT service_attribute_group_id 
  FROM service_attribute_group_service 
  WHERE service_id = 1
);
```

**مثال**:
```
code                  | label              | is_required
---------------------|--------------------|------------
husband_national_id  | رقم هوية الزوج     | 1
husband_full_name    | اسم الزوج الكامل   | 1
wife_national_id     | رقم هوية الزوجة    | 1
```

### 2. في GraphQL Query

عند استعلام `citizenService`, يتم إرجاع `code` لكل حقل:

```php
// في ServiceFormQuery.php
return [
    'code' => $field->code,  // ⭐ هذا يأتي من قاعدة البيانات
    'label' => $field->label,
    // ...
];
```

### 3. في التحقق (Validation)

عند إنشاء الطلب، النظام يبحث عن الحقول باستخدام `code`:

```php
// في ServiceRequestMutation.php
$fieldCode = $field->code;  // مثلاً: "husband_national_id"
$value = $formData[$fieldCode] ?? null;  // يبحث عن "husband_national_id"
```

---

## أمثلة عملية

### مثال 1: خدمة عقد زواج

**الاستعلام**:
```graphql
query {
  citizenService(id: 1) {
    form {
      groups {
        code
        fields {
          code
          label
          isRequired
        }
      }
    }
  }
}
```

**الاستجابة**:
```json
{
  "groups": [
    {
      "code": "husband_data",
      "fields": [
        { "code": "husband_national_id", "label": "رقم هوية الزوج", "isRequired": true },
        { "code": "husband_name", "label": "اسم الزوج", "isRequired": true },
        { "code": "husband_birth_date", "label": "تاريخ الميلاد", "isRequired": false }
      ]
    },
    {
      "code": "wife_data",
      "fields": [
        { "code": "wife_national_id", "label": "رقم هوية الزوجة", "isRequired": true },
        { "code": "wife_name", "label": "اسم الزوجة", "isRequired": true }
      ]
    }
  ]
}
```

**form_data الصحيح**:
```json
{
  "husband_national_id": "1234567890",
  "husband_name": "علي حسن",
  "husband_birth_date": "1990-05-15",
  "wife_national_id": "0987654321",
  "wife_name": "فاطمة أحمد"
}
```

### مثال 2: خدمة مختلفة

**الاستعلام**:
```graphql
query {
  citizenService(id: 2) {
    form {
      groups {
        fields {
          code
          label
        }
      }
    }
  }
}
```

**الاستجابة**:
```json
{
  "groups": [
    {
      "fields": [
        { "code": "applicant_name", "label": "اسم مقدم الطلب" },
        { "code": "applicant_id", "label": "رقم الهوية" },
        { "code": "request_date", "label": "تاريخ الطلب" }
      ]
    }
  ]
}
```

**form_data الصحيح**:
```json
{
  "applicant_name": "محمد علي",
  "applicant_id": "1234567890",
  "request_date": "2025-01-15"
}
```

---

## دالة JavaScript لاستخراج الأسماء تلقائياً

```javascript
async function getFieldCodes(serviceId, accessToken) {
  const query = `
    query GetServiceForm {
      citizenService(id: ${serviceId}) {
        form {
          groups {
            code
            label
            fields {
              code
              label
              isRequired
              type
            }
          }
        }
      }
    }
  `;

  const response = await fetch('http://your-domain.com/graphql', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${accessToken}`,
    },
    body: JSON.stringify({ query }),
  });

  const result = await response.json();
  const service = result.data.citizenService;

  // استخراج جميع الأكواد
  const fieldCodes = {};
  
  service.form.groups.forEach(group => {
    group.fields.forEach(field => {
      fieldCodes[field.code] = {
        label: field.label,
        isRequired: field.isRequired,
        type: field.type,
        group: group.label,
      };
    });
  });

  return fieldCodes;
}

// الاستخدام
const codes = await getFieldCodes(1, accessToken);
console.log(codes);
// Output:
// {
//   "husband_national_id": {
//     "label": "رقم هوية الزوج",
//     "isRequired": true,
//     "type": "text",
//     "group": "بيانات الزوج"
//   },
//   ...
// }
```

---

## بناء form_data ديناميكياً

```javascript
async function buildFormData(serviceId, accessToken, userInputs) {
  // 1. احصل على النموذج
  const fieldCodes = await getFieldCodes(serviceId, accessToken);
  
  // 2. بناء form_data
  const formData = {};
  
  // التحقق من جميع الحقول المطلوبة
  const missingFields = [];
  
  Object.keys(fieldCodes).forEach(code => {
    const field = fieldCodes[code];
    
    if (field.isRequired && !userInputs[code]) {
      missingFields.push({
        code,
        label: field.label,
        group: field.group,
      });
    }
    
    // إضافة القيمة إذا كانت موجودة
    if (userInputs[code] !== undefined) {
      formData[code] = userInputs[code];
    }
  });
  
  if (missingFields.length > 0) {
    throw new Error(`الحقول المطلوبة مفقودة: ${missingFields.map(f => f.label).join(', ')}`);
  }
  
  return formData;
}

// الاستخدام
const userInputs = {
  "husband_national_id": "1234567890",
  "husband_name": "علي حسن",
  "wife_national_id": "0987654321",
  "wife_name": "فاطمة أحمد",
};

const formData = await buildFormData(1, accessToken, userInputs);
// formData سيكون:
// {
//   "husband_national_id": "1234567890",
//   "husband_name": "علي حسن",
//   "wife_national_id": "0987654321",
//   "wife_name": "فاطمة أحمد"
// }
```

---

## ملاحظات مهمة

1. **لا تخمن الأسماء**: دائماً استعلم عن النموذج أولاً
2. **الأسماء حساسة لحالة الأحرف**: `husband_national_id` ≠ `Husband_National_Id`
3. **استخدم `code` وليس `label`**: `code` هو المفتاح، `label` هو للعرض فقط
4. **الحقول المطلوبة**: تأكد من إرسال جميع الحقول التي `isRequired: true`
5. **التحقق التلقائي**: النظام سيرفض الطلب إذا كانت الحقول المطلوبة مفقودة

---

## الحقول المتداخلة (Nested Fields)

### المشكلة

عندما يكون نفس الحقل موجوداً في أكثر من مجموعة (مثل `citizen_name` في `hosam` و `personal_data`)، يجب استخدام الهيكل المتداخل.

### الحل: ثلاث طرق مدعومة

#### الطريقة 1: الهيكل المتداخل (المُوصى به) ✅

```json
{
  "hosam": {
    "citizen_name": "علي"
  },
  "personal_data": {
    "citizen_name": "محمد",
    "id_number": "1234567890",
    "sex": "5"
  }
}
```

#### الطريقة 2: الهيكل المسطح

```json
{
  "citizen_name": "علي",
  "id_number": "1234567890",
  "sex": "5"
}
```

⚠️ **ملاحظة**: في الحالة المسطحة، إذا كان `citizen_name` مطلوب في مجموعتين، سيتم استخدام نفس القيمة لكلا المجموعتين.

#### الطريقة 3: Dot Notation

```json
{
  "hosam.citizen_name": "علي",
  "personal_data.citizen_name": "محمد",
  "personal_data.id_number": "1234567890"
}
```

### أولوية البحث

النظام يبحث عن القيم بالترتيب التالي:

1. **المتداخل**: `formData[groupCode][fieldCode]`
2. **المسطح**: `formData[fieldCode]`
3. **Dot Notation**: `formData["groupCode.fieldCode"]`

### مثال عملي

بناءً على النموذج:

```json
{
  "groups": [
    {
      "code": "hosam",
      "fields": [
        { "code": "citizen_name", "isRequired": true }
      ]
    },
    {
      "code": "personal_data",
      "fields": [
        { "code": "id_number", "isRequired": false },
        { "code": "citizen_name", "isRequired": true },
        { "code": "sex", "isRequired": true }
      ]
    }
  ]
}
```

**الحل الأمثل** (متداخل):
```json
{
  "hosam": {
    "citizen_name": "علي"
  },
  "personal_data": {
    "citizen_name": "محمد",
    "id_number": "1234567890",
    "sex": "5"
  }
}
```

---

## الخلاصة

✅ **الطريقة الصحيحة**:
1. استعلم عن النموذج: `citizenService(id: X) { form { ... } }`
2. استخرج `code` و `group.code` من كل حقل
3. استخدم الهيكل المتداخل إذا كان نفس الحقل في أكثر من مجموعة
4. استخدم `code` كـ key في `form_data`

❌ **الطريقة الخاطئة**:
- تخمين الأسماء مثل `husband_national_id`
- استخدام `label` بدلاً من `code`
- نسخ أمثلة بدون التحقق من النموذج الفعلي
- تجاهل `group.code` عند وجود حقول متكررة

---

## مثال كامل: دالة جاهزة

```javascript
class ServiceRequestBuilder {
  constructor(apiUrl, accessToken) {
    this.apiUrl = apiUrl;
    this.accessToken = accessToken;
  }

  async getServiceForm(serviceId) {
    const query = `
      query GetServiceForm {
        citizenService(id: ${serviceId}) {
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
                isRequired
                validationRules
                options {
                  value
                  label
                }
              }
            }
          }
        }
      }
    `;

    const response = await fetch(this.apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${this.accessToken}`,
      },
      body: JSON.stringify({ query }),
    });

    const result = await response.json();
    return result.data.citizenService;
  }

  async createRequest(serviceId, userInputs, notes = '') {
    // 1. احصل على النموذج
    const service = await this.getServiceForm(serviceId);
    
    // 2. بناء form_data من userInputs باستخدام codes
    const formData = {};
    const requiredFields = [];
    
    service.form.groups.forEach(group => {
      group.fields.forEach(field => {
        if (field.isRequired && !userInputs[field.code]) {
          requiredFields.push({
            code: field.code,
            label: field.label,
            group: group.label,
          });
        }
        
        if (userInputs[field.code] !== undefined) {
          formData[field.code] = userInputs[field.code];
        }
      });
    });
    
    if (requiredFields.length > 0) {
      throw new Error(
        `الحقول المطلوبة مفقودة: ${requiredFields.map(f => `${f.label} (${f.code})`).join(', ')}`
      );
    }
    
    // 3. إرسال الطلب
    const mutation = `
      mutation CreateServiceRequest {
        createServiceRequest(
          input: {
            serviceId: ${serviceId}
            formData: ${JSON.stringify(formData)}
            notes: "${notes}"
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
    `;

    const response = await fetch(this.apiUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${this.accessToken}`,
      },
      body: JSON.stringify({ query: mutation }),
    });

    const result = await response.json();
    
    if (result.errors) {
      throw new Error(result.errors[0].message);
    }
    
    return result.data.createServiceRequest;
  }
}

// الاستخدام
const builder = new ServiceRequestBuilder(
  'http://your-domain.com/graphql',
  'your_access_token'
);

// userInputs يجب أن تستخدم codes من النموذج
const userInputs = {
  "husband_national_id": "1234567890",  // ✅ من النموذج
  "husband_name": "علي حسن",            // ✅ من النموذج
  "wife_national_id": "0987654321",     // ✅ من النموذج
  "wife_name": "فاطمة أحمد",            // ✅ من النموذج
};

const result = await builder.createRequest(1, userInputs, "طلب عقد زواج");
console.log('Request created:', result);
```

