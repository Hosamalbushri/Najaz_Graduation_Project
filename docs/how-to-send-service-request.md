# كيفية إرسال طلب خدمة

دليل شامل لإرسال طلب خدمة عبر GraphQL API.

---

## المتطلبات الأساسية

1. **تسجيل الدخول**: يجب أن يكون المواطن مسجلاً دخولاً وحاصلاً على `access_token`
2. **معرفة معرف الخدمة**: معرف الخدمة التي تريد طلبها (`serviceId`)
3. **بيانات النموذج**: جميع البيانات المطلوبة حسب نموذج الخدمة (`form_data`)

---

## الخطوات الأساسية

### 1. تسجيل الدخول والحصول على Token

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
    services {
      id
      name
    }
  }
}
```

**الاستجابة**:
```json
{
  "data": {
    "citizenLogin": {
      "success": true,
      "accessToken": "eyJ0eXAiOiJKV1QiLCJhbGc...",
      "citizen": {
        "id": "1",
        "firstName": "علي"
      },
      "services": [
        {
          "id": "1",
          "name": "عقد زواج"
        }
      ]
    }
  }
}
```

**احفظ `accessToken`** لاستخدامه في الطلبات التالية.

---

### 2. الحصول على نموذج الخدمة (اختياري)

قبل إرسال الطلب، يمكنك الحصول على نموذج الخدمة لمعرفة الحقول المطلوبة:

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
```

---

### 3. إرسال طلب الخدمة

#### مثال بسيط: طلب خدمة عقد زواج

```graphql
mutation CreateServiceRequest {
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
        "marriage_location": "المحكمة الشرعية - الرياض"
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
      createdAt
    }
  }
}
```

---

## طرق الإرسال

### 1. استخدام Postman

#### الخطوات:

1. **افتح Postman** وأنشئ طلب جديد

2. **اختر Method**: `POST`

3. **أدخل URL**: 
   ```
   http://your-domain.com/graphql
   ```
   أو
   ```
   https://your-domain.com/graphql
   ```

4. **أضف Headers**:
   ```
   Content-Type: application/json
   Authorization: Bearer YOUR_ACCESS_TOKEN
   Accept: application/json
   ```

5. **في Body**، اختر `GraphQL` واكتب:

```graphql
mutation CreateServiceRequest {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: {
        "husband_national_id": "1234567890"
        "husband_full_name": "علي حسن محمد"
        "wife_national_id": "0987654321"
        "wife_full_name": "فاطمة أحمد سالم"
        "marriage_date": "2025-01-01"
      }
      notes: "طلب عقد زواج"
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
```

6. **أرسل الطلب** (Send)

#### مثال كامل في Postman:

**Headers**:
```
Content-Type: application/json
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
Accept: application/json
```

**Body (GraphQL)**:
```graphql
mutation {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: {
        "husband_national_id": "1234567890"
        "husband_full_name": "علي حسن محمد"
        "wife_national_id": "0987654321"
        "wife_full_name": "فاطمة أحمد سالم"
        "marriage_date": "2025-01-01"
      }
      notes: "طلب عقد زواج"
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

---

### 2. استخدام JavaScript (Fetch API)

```javascript
// المتغيرات
const API_URL = 'http://your-domain.com/graphql';
const ACCESS_TOKEN = 'your_access_token_here';

// البيانات
const mutation = `
  mutation CreateServiceRequest {
    createServiceRequest(
      input: {
        serviceId: 1
        formData: {
          "husband_national_id": "1234567890"
          "husband_full_name": "علي حسن محمد"
          "wife_national_id": "0987654321"
          "wife_full_name": "فاطمة أحمد سالم"
          "marriage_date": "2025-01-01"
        }
        notes: "طلب عقد زواج"
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

// إرسال الطلب
async function sendServiceRequest() {
  try {
    const response = await fetch(API_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${ACCESS_TOKEN}`,
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        query: mutation,
      }),
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

// استدعاء الدالة
sendServiceRequest();
```

---

### 3. استخدام Axios (JavaScript)

```javascript
import axios from 'axios';

const API_URL = 'http://your-domain.com/graphql';
const ACCESS_TOKEN = 'your_access_token_here';

const mutation = `
  mutation CreateServiceRequest {
    createServiceRequest(
      input: {
        serviceId: 1
        formData: {
          "husband_national_id": "1234567890"
          "husband_full_name": "علي حسن محمد"
          "wife_national_id": "0987654321"
          "wife_full_name": "فاطمة أحمد سالم"
          "marriage_date": "2025-01-01"
        }
        notes: "طلب عقد زواج"
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

axios.post(API_URL, {
  query: mutation,
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

### 4. استخدام cURL (Terminal)

```bash
curl -X POST http://your-domain.com/graphql \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -d '{
    "query": "mutation CreateServiceRequest { createServiceRequest(input: { serviceId: 1, formData: { \"husband_national_id\": \"1234567890\", \"husband_full_name\": \"علي حسن محمد\", \"wife_national_id\": \"0987654321\", \"wife_full_name\": \"فاطمة أحمد سالم\", \"marriage_date\": \"2025-01-01\" }, notes: \"طلب عقد زواج\" }) { success message request { id status service { name } } } }"
  }'
```

---

### 5. استخدام React Native / Mobile App

```javascript
import axios from 'axios';

const createServiceRequest = async (serviceId, formData, notes, accessToken) => {
  const API_URL = 'http://your-domain.com/graphql';
  
  const mutation = `
    mutation CreateServiceRequest {
      createServiceRequest(
        input: {
          serviceId: ${serviceId}
          formData: ${JSON.stringify(formData)}
          notes: "${notes || ''}"
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
          beneficiaries {
            id
            firstName
            lastName
          }
        }
      }
    }
  `;

  try {
    const response = await axios.post(
      API_URL,
      { query: mutation },
      {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${accessToken}`,
          'Accept': 'application/json',
        },
      }
    );

    if (response.data.errors) {
      throw new Error(response.data.errors[0].message);
    }

    return response.data.data.createServiceRequest;
  } catch (error) {
    console.error('Error creating service request:', error);
    throw error;
  }
};

// الاستخدام
const handleSubmit = async () => {
  try {
    const formData = {
      "husband_national_id": "1234567890",
      "husband_full_name": "علي حسن محمد",
      "wife_national_id": "0987654321",
      "wife_full_name": "فاطمة أحمد سالم",
      "marriage_date": "2025-01-01"
    };

    const result = await createServiceRequest(
      1,           // serviceId
      formData,    // formData
      "طلب عقد زواج", // notes
      accessToken  // من AsyncStorage أو Context
    );

    if (result.success) {
      console.log('Request created:', result.request);
      // عرض رسالة نجاح
    }
  } catch (error) {
    console.error('Error:', error.message);
    // عرض رسالة خطأ
  }
};
```

---

## الاستجابة الناجحة

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
        "citizen": {
          "firstName": "أحمد",
          "lastName": "محمد"
        },
        "beneficiaries": [
          {
            "id": "2",
            "firstName": "علي",
            "lastName": "حسن",
            "nationalId": "1234567890"
          },
          {
            "id": "3",
            "firstName": "فاطمة",
            "lastName": "أحمد",
            "nationalId": "0987654321"
          }
        ],
        "formData": {
          "husband_national_id": "1234567890",
          "husband_full_name": "علي حسن محمد",
          "wife_national_id": "0987654321",
          "wife_full_name": "فاطمة أحمد سالم",
          "marriage_date": "2025-01-01"
        },
        "createdAt": "2025-01-15T10:30:00.000000Z"
      }
    }
  }
}
```

---

## أخطاء شائعة وحلولها

### 1. خطأ: "You must be logged in as a citizen"

**السبب**: لم يتم إرسال `access_token` أو انتهت صلاحيته.

**الحل**:
- تأكد من إرسال Header: `Authorization: Bearer YOUR_TOKEN`
- سجل دخول مرة أخرى للحصول على token جديد

---

### 2. خطأ: "The following required fields are missing"

**السبب**: لم يتم إرسال جميع الحقول المطلوبة.

**مثال الخطأ**:
```json
{
  "errors": [{
    "message": "الحقول المطلوبة التالية مفقودة: رقم هوية الزوجة (wife_national_id) - المجموعة: بيانات الزوجة"
  }]
}
```

**الحل**:
- راجع نموذج الخدمة باستخدام `citizenService` query
- تأكد من إرسال جميع الحقول التي `isRequired: true`

---

### 3. خطأ: "This service is not accessible for your citizen type"

**السبب**: الخدمة غير متاحة لنوع المواطن الخاص بك.

**الحل**:
- تحقق من الخدمات المتاحة باستخدام `citizenServices` query
- استخدم `serviceId` من الخدمات المتاحة فقط

---

### 4. خطأ: "The following fields have invalid values"

**السبب**: القيم المرسلة لا تطابق قواعد التحقق.

**مثال الخطأ**:
```json
{
  "errors": [{
    "message": "الحقول التالية تحتوي على قيم غير صحيحة: البريد الإلكتروني: البريد الإلكتروني غير صحيح"
  }]
}
```

**الحل**:
- راجع `validationRules` لكل حقل
- تأكد من صحة التنسيق (email, phone, date, etc.)

---

## نصائح مهمة

1. **احفظ Token بأمان**: استخدم `AsyncStorage` (React Native) أو `localStorage` (Web) أو `SecureStore`

2. **تحقق من النموذج أولاً**: استخدم `citizenService` query لمعرفة الحقول المطلوبة قبل الإرسال

3. **تحقق من البيانات**: تأكد من صحة جميع البيانات قبل الإرسال لتجنب الأخطاء

4. **معالجة الأخطاء**: استخدم try-catch أو .catch() لمعالجة الأخطاء بشكل صحيح

5. **اختبار أولاً**: اختبر الطلب باستخدام Postman قبل التكامل في التطبيق

---

## مثال كامل: دالة JavaScript شاملة

```javascript
class ServiceRequestAPI {
  constructor(apiUrl, accessToken) {
    this.apiUrl = apiUrl;
    this.accessToken = accessToken;
  }

  async sendRequest(serviceId, formData, notes = '') {
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
            service {
              id
              name
            }
            beneficiaries {
              id
              firstName
              lastName
            }
            createdAt
          }
        }
      }
    `;

    try {
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.accessToken}`,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ query: mutation }),
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

    try {
      const response = await fetch(this.apiUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.accessToken}`,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ query }),
      });

      const result = await response.json();

      if (result.errors) {
        throw new Error(result.errors[0].message);
      }

      return result.data.citizenService;
    } catch (error) {
      console.error('Error fetching service form:', error);
      throw error;
    }
  }
}

// الاستخدام
const api = new ServiceRequestAPI(
  'http://your-domain.com/graphql',
  'your_access_token'
);

// الحصول على النموذج
const service = await api.getServiceForm(1);

// إرسال الطلب
const formData = {
  "husband_national_id": "1234567890",
  "husband_full_name": "علي حسن محمد",
  "wife_national_id": "0987654321",
  "wife_full_name": "فاطمة أحمد سالم",
  "marriage_date": "2025-01-01"
};

const result = await api.sendRequest(1, formData, "طلب عقد زواج");
console.log('Request created:', result);
```

---

## الخلاصة

إرسال طلب خدمة يتطلب:
1. ✅ Token صالح من تسجيل الدخول
2. ✅ معرف الخدمة (`serviceId`)
3. ✅ بيانات النموذج (`formData`) مع جميع الحقول المطلوبة
4. ✅ إرسال Header: `Authorization: Bearer TOKEN`
5. ✅ استخدام GraphQL mutation: `createServiceRequest`

النظام سيتحقق تلقائياً من:
- ✅ صحة Token
- ✅ توفر الخدمة لنوع المواطن
- ✅ وجود جميع الحقول المطلوبة
- ✅ صحة قيم الحقول حسب قواعد التحقق
- ✅ استخراج الأطراف المستفيدة تلقائياً

