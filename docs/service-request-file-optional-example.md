# مثال: طلب خدمة مع ملف اختياري

## المشكلة

إذا كان الملف اختياري (يمكن أن يكون `null`)، لا تضفه في `files` array على الإطلاق.

## الحل 1: مع ملف (مطلوب)

### operations (Text):

```json
{
  "query": "mutation CreateServiceRequest($input: CreateServiceRequestInput!, $husbandDocumentsFile: Upload!) { createServiceRequest(input: $input, files: [{fieldCode: \"husband_data_documents\", file: $husbandDocumentsFile}]) { success message request { id status } } }",
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

**ملاحظة**: في variables، `husbandDocumentsFile` يجب أن يكون `null` في JSON، لكن الملف الفعلي يُرسل في form-data.

### map (Text):
```json
{
  "0": ["variables.husbandDocumentsFile"]
}
```

### الملف (File):
- Key `0`: document.pdf

---

## الحل 2: بدون ملف (اختياري)

إذا كان الملف اختياري وتريد إرسال الطلب بدون ملف، **لا تضف** `files` parameter على الإطلاق:

### operations (Text):

```json
{
  "query": "mutation CreateServiceRequest($input: CreateServiceRequestInput!) { createServiceRequest(input: $input) { success message request { id status } } }",
  "variables": {
    "input": {
      "serviceId": 1,
      "formData": {
        "husband_data": {
          "husband_data_national_id_card": "01011118504",
          "husband_data_citizen_name": "jammel jmale",
          "husband_data_gender": "1"
        }
      }
    }
  }
}
```

**ملاحظة**: 
- لا يوجد `files` parameter في mutation
- لا يوجد `husbandDocumentsFile` في variables
- لا يوجد `map`
- لا توجد ملفات في form-data
- لا تضف `husband_data_documents` في formData (أو ضع `null`)

---

## الحل 3: مع ملف اختياري (JavaScript)

إذا كان الملف اختياري وتريد التحقق في الكود:

```javascript
const formData = new FormData();

const variables = {
  input: {
    serviceId: 1,
    formData: {
      husband_data: {
        husband_data_national_id_card: "01011118504",
        husband_data_citizen_name: "jammel jmale",
        husband_data_gender: "1"
      }
    }
  }
};

let query = `
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

// إذا كان الملف موجوداً، أضفه
const fileInput = document.getElementById('documentsFile');
if (fileInput && fileInput.files[0]) {
  query = `
    mutation CreateServiceRequest($input: CreateServiceRequestInput!, $husbandDocumentsFile: Upload!) {
      createServiceRequest(
        input: $input
        files: [{fieldCode: "husband_data_documents", file: $husbandDocumentsFile}]
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
  
  variables.husbandDocumentsFile = null;
  
  const map = {
    "0": ["variables.husbandDocumentsFile"]
  };
  formData.append('map', JSON.stringify(map));
  formData.append('0', fileInput.files[0]);
}

const operations = {
  query: query,
  variables: variables
};

formData.append('operations', JSON.stringify(operations));

fetch('/graphql', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${accessToken}`
  },
  body: formData
});
```

---

## الخلاصة

- **إذا كان الملف مطلوباً**: استخدم `Upload!` وأضف الملف في form-data
- **إذا كان الملف اختياري**: لا تضف `files` parameter على الإطلاق
- **لا تضع `null` في variables للملف** إذا كنت تستخدم `Upload!` - يجب أن يكون الملف موجوداً في form-data

