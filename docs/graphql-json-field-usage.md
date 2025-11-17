# كيفية استخدام حقول JSON في GraphQL

## المشكلة

عندما يكون الحقل من نوع `JSON!` في GraphQL schema، لا يمكنك إرساله كـ object مباشر في GraphQL syntax. يجب إرساله كـ **string يحتوي على JSON**.

## الحل: ثلاث طرق

### الطريقة 1: استخدام Variables (المُوصى به) ✅

**الأفضل والأكثر أماناً** - استخدم GraphQL Variables:

```graphql
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
```

**Variables**:
```json
{
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
```

**في JavaScript**:
```javascript
const mutation = `
  mutation CreateServiceRequest($input: CreateServiceRequestInput!) {
    createServiceRequest(input: $input) {
      success
      message
    }
  }
`;

const variables = {
  input: {
    serviceId: 1,
    formData: {
      "wife_data": { "citizen_name": "علي حسن" },
      "husband_data": { "citizen_name": "محمد أحمد" }
    },
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
    variables: variables  // ✅ سيتم تحويل formData تلقائياً
  }),
});
```

### الطريقة 2: JSON String مباشر

إذا لم تستخدم variables، يجب تحويل JSON إلى string:

```graphql
mutation CreateServiceRequest {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: "{\"wife_data\":{\"citizen_name\":\"علي حسن\"},\"husband_data\":{\"citizen_name\":\"محمد أحمد\"}}"
      notes: "طلب خدمة"
    }
  ) {
    success
    message
  }
}
```

**في JavaScript**:
```javascript
const formData = {
  "wife_data": { "citizen_name": "علي حسن" },
  "husband_data": { "citizen_name": "محمد أحمد" }
};

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
    }
  }
`;
```

⚠️ **ملاحظة**: `JSON.stringify(JSON.stringify(formData))` - double stringify لأن:
1. الأول يحول object إلى JSON string
2. الثاني يحوله إلى string في GraphQL query

### الطريقة 3: في Postman

**استخدم Variables tab**:

1. **Query**:
```graphql
mutation CreateServiceRequest($input: CreateServiceRequestInput!) {
  createServiceRequest(input: $input) {
    success
    message
  }
}
```

2. **Variables**:
```json
{
  "input": {
    "serviceId": 1,
    "formData": {
      "wife_data": {
        "citizen_name": "علي حسن"
      },
      "husband_data": {
        "citizen_name": "محمد أحمد"
      }
    },
    "notes": "طلب خدمة"
  }
}
```

## لماذا Variables أفضل؟

1. ✅ **أسهل في القراءة**: لا حاجة لـ escape characters
2. ✅ **أكثر أماناً**: تجنب مشاكل الـ escaping
3. ✅ **أسهل في الصيانة**: يمكن تعديل formData بسهولة
4. ✅ **يدعم التعقيد**: يعمل مع أي هيكل JSON معقد

## أمثلة خاطئة ❌

```graphql
# ❌ خطأ: لا يمكن استخدام object مباشر
mutation {
  createServiceRequest(
    input: {
      formData: {
        "wife_data": { "citizen_name": "علي" }
      }
    }
  ) {
    success
  }
}
```

**الخطأ**: `Syntax Error: Expected Name, found String "wife_data"`

## أمثلة صحيحة ✅

```graphql
# ✅ صحيح: استخدام variables
mutation($input: CreateServiceRequestInput!) {
  createServiceRequest(input: $input) {
    success
  }
}
```

```graphql
# ✅ صحيح: JSON string
mutation {
  createServiceRequest(
    input: {
      formData: "{\"wife_data\":{\"citizen_name\":\"علي\"}}"
    }
  ) {
    success
  }
}
```

## الخلاصة

- ✅ **استخدم Variables** - الأفضل دائماً
- ✅ **JSON String** - يعمل لكن معقد
- ❌ **Object مباشر** - لا يعمل مع حقول JSON

