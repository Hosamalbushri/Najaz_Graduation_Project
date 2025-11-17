# مثال عملي: كيفية طلب الخدمة

بناءً على النموذج الذي أرسلته، إليك مثال كامل لإرسال طلب الخدمة.

---

## النموذج الذي لديك

```json
{
  "groups": [
    {
      "code": "hosam",
      "label": "ذمار",
      "fields": [
        {
          "code": "citizen_name",
          "label": "اسم المواطن",
          "isRequired": true
        }
      ]
    },
    {
      "code": "personal_data",
      "label": "بيانات شخصية",
      "fields": [
        {
          "code": "id_number",
          "label": "رقم الهوية",
          "isRequired": false,
          "validationRules": { "validation": "numeric" }
        },
        {
          "code": "citizen_name",
          "label": "اسم المواطن",
          "isRequired": true
        },
        {
          "code": "sex",
          "label": "الجنس",
          "type": "checkbox",
          "isRequired": true,
          "options": [
            { "value": "5", "label": "ذكر" },
            { "value": "6", "label": "انثى" }
          ]
        }
      ]
    }
  ]
}
```

---

## الخطوة 1: تسجيل الدخول

```graphql
mutation Login {
  citizenLogin(
    input: {
      nationalId: "1234567890"
      password: "your_password"
    }
  ) {
    success
    accessToken
    citizen {
      id
      firstName
    }
  }
}
```

**احفظ `accessToken`** من الاستجابة.

---

## الخطوة 2: إرسال طلب الخدمة

### الطريقة المُوصى بها: الهيكل المتداخل باستخدام customCode ✅

**مهم**: استخدم `customCode` من النموذج (وليس `code`) كمفتاح في `form_data`.

```graphql
mutation CreateServiceRequest {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: "{\"wife_data\":{\"citizen_name\":\"علي حسن\"},\"husband_data\":{\"citizen_name\":\"محمد أحمد\",\"id_number\":\"1234567890\",\"sex\":\"5\"}}"
      notes: "طلب خدمة عقد زواج"
    }
  ) {
    success
    message
    request {
      id
      status
      service {
        id
        name
      }
      formData
      createdAt
    }
  }
}
```

**مهم**: `formData` من نوع `JSON!` في GraphQL، لذلك يجب إرساله كـ **string يحتوي على JSON**، وليس كـ object مباشر.

**ملاحظة**: النظام يدعم أيضاً استخدام `code` الأصلي (`hosam`, `personal_data`) كـ fallback، لكن `customCode` هو المفتاح الرئيسي.

---

## استخدام Postman

### 1. Headers

```
Content-Type: application/json
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

### 2. Body (GraphQL)

```graphql
mutation {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: "{\"wife_data\":{\"citizen_name\":\"علي حسن\"},\"husband_data\":{\"citizen_name\":\"محمد أحمد\",\"id_number\":\"1234567890\",\"sex\":\"5\"}}"
      notes: "طلب خدمة"
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

**ملاحظة**: في Postman، يمكنك استخدام Variables بدلاً من كتابة JSON string مباشرة (انظر الأمثلة أدناه).

---

## استخدام JavaScript (Fetch)

```javascript
const API_URL = 'http://your-domain.com/graphql';
const ACCESS_TOKEN = 'your_access_token_here';

// بناء formData كـ object عادي
const formData = {
  "wife_data": {
    "citizen_name": "علي حسن"
  },
  "husband_data": {
    "citizen_name": "محمد أحمد",
    "id_number": "1234567890",
    "sex": "5"
  }
};

// تحويل formData إلى JSON string
const mutation = `
  mutation CreateServiceRequest {
    createServiceRequest(
      input: {
        serviceId: 1
        formData: ${JSON.stringify(JSON.stringify(formData))}
        notes: "طلب خدمة"
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
      }
    }
  }
`;

async function sendRequest() {
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${ACCESS_TOKEN}`,
        'Accept': 'application/json',
      },
      body: JSON.stringify({ query: mutation }),
    });

    const result = await response.json();
    
    if (result.errors) {
      console.error('Errors:', result.errors);
    } else {
      console.log('Success:', result.data);
    }
    
    return result;
  } catch (error) {
    console.error('Error:', error);
    throw error;
  }
}

sendRequest();
```

**أو استخدام variables** (الطريقة الأفضل):

```javascript
const formData = {
  "wife_data": {
    "citizen_name": "علي حسن"
  },
  "husband_data": {
    "citizen_name": "محمد أحمد",
    "id_number": "1234567890",
    "sex": "5"
  }
};

const mutation = `
  mutation CreateServiceRequest($input: CreateServiceRequestInput!) {
    createServiceRequest(input: $input) {
      success
      message
      request {
        id
        status
      }
    }
  }
`;

const variables = {
  input: {
    serviceId: 1,
    formData: formData,  // سيتم تحويله تلقائياً إلى JSON string
    notes: "طلب خدمة"
  }
};

fetch(API_URL, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${ACCESS_TOKEN}`,
  },
  body: JSON.stringify({
    query: mutation,
    variables: variables
  }),
})
.then(res => res.json())
.then(data => console.log(data));
```

---

## استخدام Axios

```javascript
import axios from 'axios';

const formData = {
  "wife_data": {
    "citizen_name": "علي حسن"
  },
  "husband_data": {
    "citizen_name": "محمد أحمد",
    "id_number": "1234567890",
    "sex": "5"
  }
};

// استخدام variables (الطريقة الأفضل)
const mutation = `
  mutation CreateServiceRequest($input: CreateServiceRequestInput!) {
    createServiceRequest(input: $input) {
      success
      message
      request {
        id
        status
      }
    }
  }
`;

axios.post('http://your-domain.com/graphql', {
  query: mutation,
  variables: {
    input: {
      serviceId: 1,
      formData: formData,  // سيتم تحويله تلقائياً
      notes: "طلب خدمة"
    }
  }
}, {
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${ACCESS_TOKEN}`,
    'Accept': 'application/json',
  },
})
.then(response => {
  console.log('Success:', response.data);
})
.catch(error => {
  console.error('Error:', error);
});
```

---

## استخدام cURL

```bash
curl -X POST http://your-domain.com/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -d '{
    "query": "mutation CreateServiceRequest($input: CreateServiceRequestInput!) { createServiceRequest(input: $input) { success message request { id status } } }",
    "variables": {
      "input": {
        "serviceId": 1,
        "formData": {
          "wife_data": {
            "citizen_name": "علي حسن"
          },
          "husband_data": {
            "citizen_name": "محمد أحمد",
            "id_number": "1234567890",
            "sex": "5"
          }
        },
        "notes": "طلب خدمة"
      }
    }
  }'
```

---

## الطريقة البديلة: الهيكل المسطح

إذا كنت تريد استخدام الهيكل المسطح (لكن سيستخدم نفس القيمة لكلا المجموعتين):

```graphql
mutation CreateServiceRequest {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: "{\"citizen_name\":\"علي حسن\",\"id_number\":\"1234567890\",\"sex\":\"5\"}"
      notes: "طلب خدمة"
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

⚠️ **ملاحظة**: في هذه الحالة، `citizen_name` سيستخدم نفس القيمة (`"علي حسن"`) لكل من `wife_data` و `husband_data`.

---

## الاستجابة المتوقعة

### نجاح ✅

```json
{
  "data": {
    "createServiceRequest": {
      "success": true,
      "message": "تم إنشاء طلب الخدمة بنجاح.",
      "request": {
        "id": "5",
        "status": "pending",
        "service": {
          "id": "1",
          "name": "عقد زواج"
        },
        "formData": {
          "wife_data": {
            "citizen_name": "علي حسن"
          },
          "husband_data": {
            "citizen_name": "محمد أحمد",
            "id_number": "1234567890",
            "sex": "5"
          }
        },
        "createdAt": "2025-01-15T10:30:00.000000Z"
      }
    }
  }
}
```

### خطأ: حقول مفقودة ❌

```json
{
  "errors": [
    {
      "message": "الحقول المطلوبة التالية مفقودة: اسم المواطن (wife_data.citizen_name) - المجموعة: بيانات الزوجة, اسم المواطن (husband_data.citizen_name) - المجموعة: بيانات الزوج, الجنس (husband_data.sex) - المجموعة: بيانات الزوج"
    }
  ]
}
```

### خطأ: قيم غير صحيحة ❌

```json
{
  "errors": [
    {
      "message": "الحقول التالية تحتوي على قيم غير صحيحة: رقم الهوية: التنسيق غير صحيح"
    }
  ]
}
```

---

## مثال كامل: دالة JavaScript جاهزة

```javascript
class ServiceRequestAPI {
  constructor(apiUrl, accessToken) {
    this.apiUrl = apiUrl;
    this.accessToken = accessToken;
  }

  async createRequest(serviceId, formData, notes = '') {
    // استخدام variables (الطريقة الأفضل والأكثر أماناً)
    const mutation = `
      mutation CreateServiceRequest($input: CreateServiceRequestInput!) {
        createServiceRequest(input: $input) {
          success
          message
          request {
            id
            status
            service {
              id
              name
            }
            formData
            createdAt
          }
        }
      }
    `;

    const variables = {
      input: {
        serviceId: serviceId,
        formData: formData,  // سيتم تحويله تلقائياً إلى JSON string
        notes: notes || null,
      }
    };

    try {
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.accessToken}`,
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          query: mutation,
          variables: variables
        }),
      });

      const result = await response.json();

      if (result.errors) {
        throw new Error(result.errors[0].message);
      }

      if (!result.data.createServiceRequest.success) {
        throw new Error(result.data.createServiceRequest.message);
      }

      return result.data.createServiceRequest;
    } catch (error) {
      console.error('Error creating service request:', error);
      throw error;
    }
  }
}

// الاستخدام
const api = new ServiceRequestAPI(
  'http://your-domain.com/graphql',
  'your_access_token'
);

// بناء form_data باستخدام customCode
const formData = {
  "wife_data": {        // ✅ customCode من النموذج
    "citizen_name": "علي حسن"
  },
  "husband_data": {     // ✅ customCode من النموذج
    "citizen_name": "محمد أحمد",
    "id_number": "1234567890",
    "sex": "5"  // 5 = ذكر، 6 = انثى
  }
};

// إرسال الطلب
const result = await api.createRequest(1, formData, "طلب خدمة عقد زواج");

console.log('Request created:', result);
// Output:
// {
//   success: true,
//   message: "تم إنشاء طلب الخدمة بنجاح.",
//   request: {
//     id: "5",
//     status: "pending",
//     ...
//   }
// }
```

---

## ملخص الخطوات

1. ✅ **سجل الدخول** واحصل على `accessToken`
2. ✅ **احصل على النموذج** لمعرفة `customCode` و `code` لكل مجموعة
3. ✅ **ابني `form_data`** باستخدام:
   - **الهيكل المتداخل (مُوصى به)**: استخدم `customCode` كمفتاح رئيسي
     ```json
     { "customCode": { "field_code": "value" } }
     ```
   - أو الهيكل المسطح: `{ "field_code": "value" }`
   - النظام يدعم أيضاً `code` الأصلي كـ fallback
4. ✅ **أرسل الطلب** باستخدام `createServiceRequest` mutation
5. ✅ **تحقق من الاستجابة** - `success: true` يعني النجاح

## ⚠️ مهم: استخدام customCode

**استخدم `customCode` من النموذج كمفتاح رئيسي** في `form_data`:

- ✅ صحيح: `"wife_data": { "citizen_name": "..." }` (customCode)
- ✅ صحيح أيضاً: `"hosam": { "citizen_name": "..." }` (code - fallback)
- ❌ تجنب: استخدام `code` عندما يوجد `customCode` (لأنه قد يتغير)

---

## نصائح مهمة

1. **استخدم الهيكل المتداخل** عندما يكون نفس الحقل في أكثر من مجموعة
2. **تحقق من الحقول المطلوبة** (`isRequired: true`) قبل الإرسال
3. **استخدم القيم الصحيحة** للخيارات (مثل `sex: "5"` أو `sex: "6"`)
4. **تحقق من `validationRules`** (مثل `id_number` يجب أن يكون رقمي)
5. **احفظ `request.id`** لمتابعة حالة الطلب لاحقاً

---

## متابعة الطلب

بعد إنشاء الطلب، يمكنك متابعته:

```graphql
query MyServiceRequest {
  myServiceRequest(id: 5) {
    id
    status
    service {
      name
    }
    formData
    createdAt
  }
}
```

