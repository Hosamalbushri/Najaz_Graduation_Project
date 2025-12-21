# دليل إرسال طلب خدمة مع الملفات والصور

## نظرة عامة

يمكنك الآن إرسال طلبات الخدمات مع الملفات والصور باستخدام `createServiceRequest` mutation. الملفات تُرفع باستخدام GraphQL multipart/form-data specification.

## البنية

### GraphQL Mutation

```graphql
mutation CreateServiceRequest(
    $input: CreateServiceRequestInput!
    $idCardFile: Upload
    $photoFile: Upload
) {
    createServiceRequest(
        input: $input
        files: [
            { fieldCode: "id_card", file: $idCardFile }
            { fieldCode: "photo", file: $photoFile }
        ]
    ) {
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

## الطريقة 1: استخدام Postman (multipart/form-data)

### الخطوات:

1. **Method**: `POST`
2. **URL**: `https://your-domain.com/graphql`
3. **Headers**:
   - `Authorization`: `Bearer <citizen_jwt_token>`
   - `Content-Type`: سيتم تعيينه تلقائياً إلى `multipart/form-data`

4. **Body** (اختر `form-data`):

#### أ. operations (Text):

```json
{
  "query": "mutation CreateServiceRequest($input: CreateServiceRequestInput!, $idCardFile: Upload, $photoFile: Upload) { createServiceRequest(input: $input, files: [{fieldCode: \"id_card\", file: $idCardFile}, {fieldCode: \"photo\", file: $photoFile}]) { success message request { id status formData } } }",
  "variables": {
    "input": {
      "serviceId": 1,
      "formData": {
        "personal_data": {
          "name": "أحمد محمد",
          "email": "ahmed@example.com"
        }
      },
      "notes": "طلب خدمة مع المرفقات"
    },
    "idCardFile": null,
    "photoFile": null
  }
}
```

#### ب. map (Text):

```json
{
  "0": ["variables.idCardFile"],
  "1": ["variables.photoFile"]
}
```

#### ج. ملفات (File):

| Key | Type | Value |
|-----|------|-------|
| 0 | File | id-card.jpg |
| 1 | File | photo.jpg |

## الطريقة 2: استخدام JavaScript/Fetch API

```javascript
// إعداد البيانات
const formData = new FormData();

// 1. operations (JSON string)
const operations = {
  query: `
    mutation CreateServiceRequest($input: CreateServiceRequestInput!, $idCardFile: Upload, $photoFile: Upload) {
      createServiceRequest(
        input: $input
        files: [
          { fieldCode: "id_card", file: $idCardFile }
          { fieldCode: "photo", file: $photoFile }
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
  `,
  variables: {
    input: {
      serviceId: 1,
      formData: {
        personal_data: {
          name: "أحمد محمد",
          email: "ahmed@example.com"
        }
      },
      notes: "طلب خدمة مع المرفقات"
    },
    idCardFile: null,
    photoFile: null
  }
};

formData.append('operations', JSON.stringify(operations));

// 2. map (JSON string)
const map = {
  "0": ["variables.idCardFile"],
  "1": ["variables.photoFile"]
};
formData.append('map', JSON.stringify(map));

// 3. الملفات
const idCardFile = document.getElementById('idCardInput').files[0];
const photoFile = document.getElementById('photoInput').files[0];

formData.append('0', idCardFile);
formData.append('1', photoFile);

// إرسال الطلب
fetch('https://your-domain.com/graphql', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${accessToken}`
    // لا تضيف Content-Type - سيتم تعيينه تلقائياً
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

## الطريقة 3: استخدام Axios

```javascript
import axios from 'axios';

const formData = new FormData();

// operations
const operations = {
  query: `
    mutation CreateServiceRequest($input: CreateServiceRequestInput!, $idCardFile: Upload, $photoFile: Upload) {
      createServiceRequest(
        input: $input
        files: [
          { fieldCode: "id_card", file: $idCardFile }
          { fieldCode: "photo", file: $photoFile }
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
  `,
  variables: {
    input: {
      serviceId: 1,
      formData: {
        personal_data: {
          name: "أحمد محمد",
          email: "ahmed@example.com"
        }
      },
      notes: "طلب خدمة مع المرفقات"
    },
    idCardFile: null,
    photoFile: null
  }
};

formData.append('operations', JSON.stringify(operations));

// map
const map = {
  "0": ["variables.idCardFile"],
  "1": ["variables.photoFile"]
};
formData.append('map', JSON.stringify(map));

// الملفات
formData.append('0', idCardFile);
formData.append('1', photoFile);

// إرسال الطلب
axios.post('https://your-domain.com/graphql', formData, {
  headers: {
    'Authorization': `Bearer ${accessToken}`
  }
})
.then(response => {
  console.log('Success:', response.data);
})
.catch(error => {
  console.error('Error:', error);
});
```

## الطريقة 4: استخدام React Native / Mobile App

```javascript
import { launchImageLibrary } from 'react-native-image-picker';
import FormData from 'form-data';

// اختيار الملفات
const pickFiles = async () => {
  const idCardResult = await launchImageLibrary({
    mediaType: 'photo',
    quality: 0.8,
  });

  const photoResult = await launchImageLibrary({
    mediaType: 'photo',
    quality: 0.8,
  });

  return {
    idCard: idCardResult.assets[0],
    photo: photoResult.assets[0],
  };
};

// إرسال الطلب
const submitServiceRequest = async () => {
  const files = await pickFiles();
  
  const formData = new FormData();

  // operations
  const operations = {
    query: `
      mutation CreateServiceRequest($input: CreateServiceRequestInput!, $idCardFile: Upload, $photoFile: Upload) {
        createServiceRequest(
          input: $input
          files: [
            { fieldCode: "id_card", file: $idCardFile }
            { fieldCode: "photo", file: $photoFile }
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
    `,
    variables: {
      input: {
        serviceId: 1,
        formData: {
          personal_data: {
            name: "أحمد محمد",
            email: "ahmed@example.com"
          }
        },
        notes: "طلب خدمة مع المرفقات"
      },
      idCardFile: null,
      photoFile: null
    }
  };

  formData.append('operations', JSON.stringify(operations));

  // map
  const map = {
    "0": ["variables.idCardFile"],
    "1": ["variables.photoFile"]
  };
  formData.append('map', JSON.stringify(map));

  // الملفات
  formData.append('0', {
    uri: files.idCard.uri,
    type: files.idCard.type,
    name: files.idCard.fileName || 'id-card.jpg',
  });

  formData.append('1', {
    uri: files.photo.uri,
    type: files.photo.type,
    name: files.photo.fileName || 'photo.jpg',
  });

  // إرسال
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
};
```

## ملاحظات مهمة

### 1. معرفة field codes

قبل إرسال الطلب، يجب أن تعرف `fieldCode` لكل حقل ملف/صورة. يمكنك الحصول عليها من:

```graphql
query GetServiceForm($serviceId: Int!) {
  citizenService(id: $serviceId) {
    form {
      groups {
        code
        label
        fields {
          code
          label
          type
          isRequired
        }
      }
    }
  }
}
```

### 2. ترتيب الملفات في map

ترتيب الملفات في `map` يجب أن يطابق ترتيبها في `operations.variables`:

```json
{
  "0": ["variables.idCardFile"],    // أول ملف في variables
  "1": ["variables.photoFile"]      // ثاني ملف في variables
}
```

### 3. validation rules

الملفات يتم التحقق منها تلقائياً بناءً على:
- **نوع الملف**: يجب أن يكون الحقل من نوع `file` أو `image`
- **الامتدادات المسموحة**: من `validation_rules` في تعريف الحقل (مثل: `mimes:pdf,doc,docx`)
- **الحجم الأقصى**: من `validation_rules` (مثل: `max:5120` = 5MB)

### 4. البنية المتداخلة vs المسطحة

`formData` يدعم بنيتين:

**متداخلة (Nested)**:
```json
{
  "personal_data": {
    "name": "أحمد",
    "id_card": "path/to/file.jpg"  // سيتم إضافة مسار الملف هنا
  }
}
```

**مسطحة (Flat)**:
```json
{
  "name": "أحمد",
  "id_card": "path/to/file.jpg"  // سيتم إضافة مسار الملف هنا
}
```

## تحديث طلب موجود

للتحديث، استخدم `updateServiceRequest` بنفس الطريقة:

```graphql
mutation UpdateServiceRequest(
    $id: ID!
    $input: UpdateServiceRequestInput!
    $newFile: Upload
) {
    updateServiceRequest(
        id: $id
        input: $input
        files: [
            { fieldCode: "id_card", file: $newFile }
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

**ملاحظة**: عند التحديث، الملف القديم سيتم حذفه تلقائياً واستبداله بالملف الجديد.

## أمثلة على الأخطاء

### خطأ: حقل غير موجود
```json
{
  "errors": [{
    "message": "الحقل \"wrong_field\" غير صالح أو ليس من نوع ملف/صورة."
  }]
}
```

### خطأ: نوع ملف غير مسموح
```json
{
  "errors": [{
    "message": "فشل التحقق من الملف في الحقل \"id_card\": The file must be a file of type: pdf, doc, docx."
  }]
}
```

### خطأ: حجم الملف كبير جداً
```json
{
  "errors": [{
    "message": "فشل التحقق من الملف في الحقل \"photo\": The file may not be greater than 5120 kilobytes."
  }]
}
```

## مثال كامل مع React

```jsx
import React, { useState } from 'react';
import { useMutation } from '@apollo/client';

const CREATE_SERVICE_REQUEST = gql`
  mutation CreateServiceRequest(
    $input: CreateServiceRequestInput!
    $idCardFile: Upload
    $photoFile: Upload
  ) {
    createServiceRequest(
      input: $input
      files: [
        { fieldCode: "id_card", file: $idCardFile }
        { fieldCode: "photo", file: $photoFile }
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
`;

function ServiceRequestForm() {
  const [idCardFile, setIdCardFile] = useState(null);
  const [photoFile, setPhotoFile] = useState(null);
  const [createRequest, { loading, error }] = useMutation(CREATE_SERVICE_REQUEST);

  const handleSubmit = async (e) => {
    e.preventDefault();

    const formData = new FormData();

    const operations = {
      query: CREATE_SERVICE_REQUEST.loc.source.body,
      variables: {
        input: {
          serviceId: 1,
          formData: {
            personal_data: {
              name: "أحمد محمد",
              email: "ahmed@example.com"
            }
          },
          notes: "طلب خدمة"
        },
        idCardFile: null,
        photoFile: null
      }
    };

    formData.append('operations', JSON.stringify(operations));
    formData.append('map', JSON.stringify({
      "0": ["variables.idCardFile"],
      "1": ["variables.photoFile"]
    }));
    formData.append('0', idCardFile);
    formData.append('1', photoFile);

    try {
      const response = await fetch('/graphql', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`
        },
        body: formData
      });

      const result = await response.json();
      console.log('Success:', result);
    } catch (err) {
      console.error('Error:', err);
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        type="file"
        onChange={(e) => setIdCardFile(e.target.files[0])}
        accept="image/*,.pdf"
      />
      <input
        type="file"
        onChange={(e) => setPhotoFile(e.target.files[0])}
        accept="image/*"
      />
      <button type="submit" disabled={loading}>
        إرسال الطلب
      </button>
    </form>
  );
}
```

## الخلاصة

- استخدم `multipart/form-data` لإرسال الملفات
- أضف `operations` و `map` و الملفات في `form-data`
- تأكد من أن `fieldCode` يطابق كود الحقل في الخدمة
- الملفات يتم حفظها تلقائياً ومساراتها تُضاف إلى `formData`

