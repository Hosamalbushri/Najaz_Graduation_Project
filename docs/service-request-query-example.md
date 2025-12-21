# مثال كامل لاستعلام createServiceRequest مع ملف

## الاستعلام GraphQL

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
            formData
            createdAt
        }
    }
}
```

**ملاحظة**: إذا كان الملف اختياري، يمكنك استخدام `Upload` (بدون !) لكن يجب التأكد من عدم إضافة الملف في `files` array إذا كان `null`. الأفضل هو استخدام `Upload!` إذا كان الملف مطلوباً.

## للاستخدام مع Postman (multipart/form-data)

### 1. operations (Text):

```json
{
  "query": "mutation CreateServiceRequest($input: CreateServiceRequestInput!, $husbandDocumentsFile: Upload!) { createServiceRequest(input: $input, files: [{fieldCode: \"husband_data_documents\", file: $husbandDocumentsFile}]) { success message request { id status formData createdAt } } }",
  "variables": {
    "input": {
      "serviceId": 1,
      "formData": {
        "husband_data": {
          "husband_data_national_id_card": "01011118504",
          "husband_data_citizen_name": "jammel jmale",
          "husband_data_gender": "1",
          "husband_data_documents": null
        }
      }
    },
    "husbandDocumentsFile": null
  }
}
```

### 2. map (Text):

```json
{
  "0": ["variables.husbandDocumentsFile"]
}
```

### 3. الملف (File):

| Key | Type | Value |
|-----|------|-------|
| 0 | File | document.pdf (أو أي ملف) |

## للاستخدام مع JavaScript/Fetch

```javascript
const formData = new FormData();

// operations
const operations = {
  query: `
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
          formData
          createdAt
        }
      }
    }
  `,
  variables: {
    input: {
      serviceId: 1,
      formData: {
        husband_data: {
          husband_data_national_id_card: "01011118504",
          husband_data_citizen_name: "jammel jmale",
          husband_data_gender: "1",
          husband_data_documents: null
        }
      }
    },
    husbandDocumentsFile: null
  }
};

formData.append('operations', JSON.stringify(operations));

// map
const map = {
  "0": ["variables.husbandDocumentsFile"]
};
formData.append('map', JSON.stringify(map));

// الملف
const fileInput = document.getElementById('documentsFile');
formData.append('0', fileInput.files[0]);

// إرسال الطلب
fetch('https://your-domain.com/graphql', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${accessToken}`
  },
  body: formData
})
.then(response => response.json())
.then(data => {
  console.log('Success:', data);
})
.catch(error => {
  console.error('Error:', error);
});
```

## ملاحظات مهمة

1. **fieldCode**: يجب أن يطابق `"husband_data_documents"` كود الحقل في الخدمة بالضبط
2. **formData**: في `husband_data_documents` يجب أن يكون `null` - سيتم استبداله تلقائياً بمسار الملف بعد الرفع
3. **الترتيب**: ترتيب الملف في `map` يجب أن يطابق ترتيبه في `variables`

## مثال مع عدة ملفات

إذا كان لديك عدة ملفات:

```json
{
  "input": {
    "serviceId": 1,
    "formData": {
      "husband_data": {
        "husband_data_national_id_card": "01011118504",
        "husband_data_citizen_name": "jammel jmale",
        "husband_data_gender": "1",
        "husband_data_documents": null,
        "husband_data_photo": null
      }
    }
  },
  "husbandDocumentsFile": null,
  "husbandPhotoFile": null
}
```

**map**:
```json
{
  "0": ["variables.husbandDocumentsFile"],
  "1": ["variables.husbandPhotoFile"]
}
```

**files**:
- Key `0`: document.pdf
- Key `1`: photo.jpg

**mutation**:
```graphql
mutation CreateServiceRequest(
    $input: CreateServiceRequestInput!
    $husbandDocumentsFile: Upload
    $husbandPhotoFile: Upload
) {
    createServiceRequest(
        input: $input
        files: [
            { fieldCode: "husband_data_documents", file: $husbandDocumentsFile }
            { fieldCode: "husband_data_photo", file: $husbandPhotoFile }
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

