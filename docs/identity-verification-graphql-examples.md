# GraphQL Examples - Identity Verification

## إنشاء طلب توثيق الهوية (Citizen API)

### Mutation Schema

```graphql
mutation RequestIdentityVerification(
  $files: [Upload!]
  $faceVideo: Upload
  $notes: String
) {
  requestIdentityVerification(
    input: {
      notes: $notes
      documents: $files
      face_video: $faceVideo
    }
  ) {
    success
    message
    verification {
      id
      status
      notes
      documents
      face_video
      createdAt
      citizen {
        id
        first_name
        last_name
        national_id
      }
    }
  }
}
```

### مثال كامل مع JavaScript/TypeScript

```javascript
// استخدام Apollo Client أو أي GraphQL client
import { gql } from '@apollo/client';

const REQUEST_IDENTITY_VERIFICATION = gql`
  mutation RequestIdentityVerification(
    $files: [Upload!]
    $faceVideo: Upload
    $notes: String
  ) {
    requestIdentityVerification(
      input: {
        notes: $notes
        documents: $files
        face_video: $faceVideo
      }
    ) {
      success
      message
      verification {
        id
        status
        notes
        documents
        face_video
        createdAt
      }
    }
  }
`;

// استخدام Mutation
const [requestVerification, { loading, error }] = useMutation(
  REQUEST_IDENTITY_VERIFICATION
);

// عند إرسال الطلب
const handleSubmit = async (files, faceVideo, notes) => {
  try {
    const { data } = await requestVerification({
      variables: {
        files: files, // Array of File objects
        faceVideo: faceVideo, // File object
        notes: notes || "طلب توثيق الهوية"
      },
      context: {
        headers: {
          Authorization: `Bearer ${citizenToken}`
        }
      }
    });

    if (data.requestIdentityVerification.success) {
      console.log('تم إرسال الطلب بنجاح:', data.requestIdentityVerification.verification);
    }
  } catch (err) {
    console.error('خطأ في إرسال الطلب:', err);
  }
};
```

### مثال مع React Native / Mobile

```javascript
import { useMutation } from '@apollo/client';
import * as DocumentPicker from 'expo-document-picker';
import * as ImagePicker from 'expo-image-picker';

const REQUEST_IDENTITY_VERIFICATION = gql`
  mutation RequestIdentityVerification(
    $files: [Upload!]
    $faceVideo: Upload
    $notes: String
  ) {
    requestIdentityVerification(
      input: {
        notes: $notes
        documents: $files
        face_video: $faceVideo
      }
    ) {
      success
      message
      verification {
        id
        status
        documents
        face_video
      }
    }
  }
`;

const IdentityVerificationScreen = () => {
  const [requestVerification] = useMutation(REQUEST_IDENTITY_VERIFICATION);

  const pickDocuments = async () => {
    const result = await DocumentPicker.getDocumentAsync({
      type: ['image/*', 'application/pdf'],
      multiple: true
    });

    return result.assets;
  };

  const pickFaceVideo = async () => {
    const result = await ImagePicker.launchCameraAsync({
      mediaTypes: ImagePicker.MediaTypeOptions.Videos,
      allowsEditing: true,
      quality: 1,
      videoMaxDuration: 30, // 30 seconds max
    });

    return result.assets[0];
  };

  const submitVerification = async () => {
    const documents = await pickDocuments();
    const faceVideo = await pickFaceVideo();

    try {
      const { data } = await requestVerification({
        variables: {
          files: documents.map(doc => ({
            file: doc.file,
            filename: doc.name
          })),
          faceVideo: {
            file: faceVideo.uri,
            filename: 'face-video.mp4'
          },
          notes: "طلب توثيق الهوية"
        }
      });

      if (data.requestIdentityVerification.success) {
        Alert.alert('نجح', 'تم إرسال طلب التوثيق بنجاح');
      }
    } catch (error) {
      Alert.alert('خطأ', error.message);
    }
  };

  return (
    <Button onPress={submitVerification} title="إرسال طلب التوثيق" />
  );
};
```

### استخدام Postman (multipart/form-data)

#### 1. إعداد Request

- **Method**: `POST`
- **URL**: `https://your-domain.com/graphql`
- **Headers**:
  - `Authorization`: `Bearer <citizen_jwt_token>`
  - `Content-Type`: `multipart/form-data` (يتم تعيينه تلقائياً)

#### 2. Body (form-data)

##### أ. operations (Text):

```json
{
  "query": "mutation RequestIdentityVerification($files: [Upload!], $faceVideo: Upload, $notes: String) { requestIdentityVerification(input: { notes: $notes, documents: $files, face_video: $faceVideo }) { success message verification { id status notes documents face_video createdAt } } }",
  "variables": {
    "files": [null, null],
    "faceVideo": null,
    "notes": "أرفقت صورة البطاقة من الجهتين وفيديو للوجه"
  }
}
```

##### ب. map (Text):

```json
{
  "0": ["variables.files.0"],
  "1": ["variables.files.1"],
  "2": ["variables.faceVideo"]
}
```

##### ج. ملفات (File):

| Key | Type | Value |
|-----|------|-------|
| 0 | File | front-id.jpg |
| 1 | File | back-id.jpg |
| 2 | File | face-video.mp4 |

### مثال مع cURL

```bash
curl -X POST https://your-domain.com/graphql \
  -H "Authorization: Bearer YOUR_CITIZEN_TOKEN" \
  -F 'operations={"query":"mutation RequestIdentityVerification($files: [Upload!], $faceVideo: Upload, $notes: String) { requestIdentityVerification(input: { notes: $notes, documents: $files, face_video: $faceVideo }) { success message verification { id status documents face_video } } }","variables":{"files":[null,null],"faceVideo":null,"notes":"طلب توثيق الهوية"}}' \
  -F 'map={"0":["variables.files.0"],"1":["variables.files.1"],"2":["variables.faceVideo"]}' \
  -F '0=@/path/to/front-id.jpg' \
  -F '1=@/path/to/back-id.jpg' \
  -F '2=@/path/to/face-video.mp4'
```

---

## إنشاء طلب توثيق الهوية (Admin API)

### Mutation Schema

```graphql
mutation CreateIdentityVerification(
  $citizenId: Int!
  $files: [Upload!]
  $faceVideo: Upload
  $notes: String
) {
  createIdentityVerification(
    input: {
      citizenId: $citizenId
      notes: $notes
      documents: $files
      face_video: $faceVideo
    }
  ) {
    success
    message
    verification {
      id
      status
      notes
      documents
      face_video
      citizen {
        id
        first_name
        last_name
        national_id
      }
      createdAt
    }
  }
}
```

### مثال JavaScript

```javascript
const CREATE_IDENTITY_VERIFICATION = gql`
  mutation CreateIdentityVerification(
    $citizenId: Int!
    $files: [Upload!]
    $faceVideo: Upload
    $notes: String
  ) {
    createIdentityVerification(
      input: {
        citizenId: $citizenId
        notes: $notes
        documents: $files
        face_video: $faceVideo
      }
    ) {
      success
      message
      verification {
        id
        status
        documents
        face_video
      }
    }
  }
`;

const [createVerification] = useMutation(CREATE_IDENTITY_VERIFICATION);

const handleCreate = async (citizenId, files, faceVideo, notes) => {
  const { data } = await createVerification({
    variables: {
      citizenId: citizenId,
      files: files,
      faceVideo: faceVideo,
      notes: notes
    },
    context: {
      headers: {
        Authorization: `Bearer ${adminToken}`
      }
    }
  });
};
```

---

## Query - جلب طلبات التوثيق

### للمواطن (Citizen API)

```graphql
query MyIdentityVerifications {
  myIdentityVerifications {
    id
    status
    notes
    documents
    face_video
    createdAt
    reviewedBy {
      id
      name
    }
    reviewedAt
  }
}

query MyLatestIdentityVerification {
  myLatestIdentityVerification {
    id
    status
    notes
    documents
    face_video
    createdAt
  }
}
```

### للإدارة (Admin API)

```graphql
query IdentityVerifications($input: FilterIdentityVerificationInput) {
  identityVerifications(input: $input) {
    data {
      id
      status
      notes
      documents
      face_video
      citizen {
        id
        first_name
        last_name
        national_id
      }
      reviewedBy {
        id
        name
      }
      createdAt
    }
    paginatorInfo {
      currentPage
      lastPage
      total
    }
  }
}

query IdentityVerification($id: ID!) {
  identityVerification(id: $id) {
    id
    status
    notes
    documents
    face_video
    citizen {
      id
      first_name
      last_name
      national_id
    }
    reviewedBy {
      id
      name
    }
    reviewedAt
    createdAt
  }
}
```

---

## تحديث حالة التوثيق (Admin API)

```graphql
mutation UpdateIdentityVerification(
  $id: ID!
  $status: IdentityVerificationStatus!
  $notes: String
) {
  updateIdentityVerification(
    id: $id
    input: {
      status: $status
      notes: $notes
    }
  ) {
    success
    message
    verification {
      id
      status
      notes
      reviewedBy {
        id
        name
      }
      reviewedAt
    }
  }
}
```

### الحالات المتاحة (IdentityVerificationStatus):

- `PENDING` - قيد المراجعة
- `APPROVED` - موثق
- `REJECTED` - مرفوض
- `NEEDS_MORE_INFO` - يحتاج معلومات إضافية

---

## ملاحظات مهمة

1. **المصادقة**: يجب إرسال `Authorization: Bearer <token>` في headers
2. **حجم الملفات**:
   - المستندات: 5MB كحد أقصى لكل ملف
   - الفيديو: 10MB كحد أقصى
3. **صيغ الملفات المدعومة**:
   - المستندات: `jpg`, `jpeg`, `png`, `pdf`
   - الفيديو: `mp4`, `mov`, `avi`, `webm`
4. **Citizen ID**: في Citizen API، يتم أخذ ID تلقائياً من token المصادقة
5. **multipart/form-data**: يجب استخدام multipart عند رفع الملفات

