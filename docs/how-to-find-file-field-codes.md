# كيفية معرفة أسماء حقول الملفات والصور (field codes)

## الطريقة 1: استخدام GraphQL Query

استخدم الاستعلام التالي لمعرفة جميع الحقول وأسمائها:

```graphql
query GetServiceFields($serviceId: ID!) {
  citizenService(id: $serviceId) {
    id
    name
    form {
      groups {
        code
        label
        fields {
          code          # ⭐ هذا هو fieldCode الذي تحتاجه
          label         # اسم الحقل
          type          # نوع الحقل: "file" أو "image" أو "text" إلخ
          isRequired
          validationRules
        }
      }
    }
  }
}
```

**Variables:**
```json
{
  "serviceId": "1"
}
```

**مثال على الاستجابة:**
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
                "code": "husband_data_national_id_card",
                "label": "رقم هوية الزوج",
                "type": "text",
                "isRequired": true
              },
              {
                "code": "husband_data_documents",    # ⭐ هذا هو fieldCode للملف
                "label": "مستندات الزوج",
                "type": "file",                       # ⭐ نوع file أو image
                "isRequired": true,
                "validationRules": {
                  "validation": "mimes:pdf,doc,docx|max:5120"
                }
              }
            ]
          }
        ]
      }
    }
  }
}
```

## الطريقة 2: تصفية حقول الملفات فقط

إذا كنت تريد فقط حقول الملفات/الصور:

```graphql
query GetFileFieldsOnly($serviceId: ID!) {
  citizenService(id: $serviceId) {
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
        }
      }
    }
  }
}
```

ثم في الكود، قم بتصفية الحقول:

```javascript
const service = response.data.citizenService;
const fileFields = [];

service.form.groups.forEach(group => {
  group.fields.forEach(field => {
    if (field.type === 'file' || field.type === 'image') {
      fileFields.push({
        fieldCode: field.code,  // ⭐ استخدم هذا في files array
        label: field.label,
        type: field.type,
        isRequired: field.isRequired,
        validationRules: field.validationRules
      });
    }
  });
});

console.log('File fields:', fileFields);
// Output: [
//   {
//     fieldCode: "husband_data_documents",
//     label: "مستندات الزوج",
//     type: "file",
//     ...
//   }
// ]
```

## ملاحظات مهمة

1. **fieldCode**: يجب أن يكون مطابقاً بالضبط لما يظهر في query (case-sensitive)
2. **النوع**: فقط الحقول من نوع `"file"` أو `"image"` يمكن رفعها
3. **validationRules**: تحقق من القواعد قبل الرفع (مثل الامتدادات المسموحة والحجم الأقصى)

## مثال كامل

```graphql
query GetServiceFormWithFileFields($serviceId: ID!) {
  citizenService(id: $serviceId) {
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

**بعد الحصول على fieldCode، استخدمه في mutation:**

```graphql
mutation CreateServiceRequest(
    $input: CreateServiceRequestInput!
    $husbandDocumentsFile: Upload!
) {
    createServiceRequest(
        input: $input
        files: [
            { fieldCode: "husband_data_documents", file: $husbandDocumentsFile }
        ]
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

