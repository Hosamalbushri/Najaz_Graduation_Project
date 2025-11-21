# GraphQL App Queries & Mutations
## استعلامات GraphQL للتطبيق (Citizen App)

---

## 🔐 المصادقة (Authentication)

### تسجيل الدخول
```graphql
mutation CitizenLogin($input: CitizenLoginInput!) {
  citizenLogin(input: $input) {
    success
    message
    accessToken
    tokenType
    expiresIn
    citizen {
      id
      firstName
      lastName
      email
      phone
      nationalId
      identityVerificationStatus
    }
    services {
      id
      name
      description
    }
  }
}
```

**Variables:**
```json
{
  "input": {
    "nationalId": "1234567890",
    "email": "citizen@example.com",
    "password": "password123",
    "remember": true,
    "deviceToken": "device-token-here",
    "deviceName": "iPhone"
  }
}
```

### تسجيل الخروج
```graphql
mutation CitizenLogout {
  citizenLogout {
    success
    message
  }
}
```

---

## 📝 التسجيل (Registration)

### إنشاء حساب جديد
```graphql
mutation CitizenSignUp($input: CitizenSignUpInput!) {
  citizenSignUp(input: $input) {
    success
    message
    accessToken
    tokenType
    expiresIn
    citizen {
      id
      firstName
      lastName
      email
      phone
      nationalId
    }
    services {
      id
      name
    }
  }
}
```

**Variables:**
```json
{
  "input": {
    "firstName": "أحمد",
    "middleName": "محمد",
    "lastName": "علي",
    "gender": "Male",
    "email": "ahmed@example.com",
    "phone": "+966501234567",
    "nationalId": "1234567890",
    "dateOfBirth": "1990-01-01",
    "citizenTypeId": 1,
    "password": "password123",
    "passwordConfirmation": "password123",
    "remember": true,
    "deviceToken": "device-token-here"
  }
}
```

---

## 👤 الملف الشخصي (Profile)

### الحصول على الملف الشخصي
```graphql
query MyProfile {
  myProfile {
    id
    firstName
    middleName
    lastName
    gender
    email
    phone
    nationalId
    dateOfBirth
    identityVerificationStatus
    citizenType {
      id
      name
    }
    identityVerification {
      id
      status
      notes
      documents
      face_video
      createdAt
      updatedAt
    }
  }
}
```

### تحديث الملف الشخصي
```graphql
mutation UpdateMyProfile($input: UpdateProfileInput!) {
  updateMyProfile(input: $input) {
    success
    message
    citizen {
      id
      firstName
      lastName
      email
      phone
      identityVerificationStatus
    }
  }
}
```

**Variables (إذا لم يكن موثق):**
```json
{
  "input": {
    "firstName": "أحمد",
    "middleName": "محمد",
    "lastName": "علي",
    "email": "ahmed@example.com",
    "phone": "+966501234567",
    "nationalId": "1234567890",
    "dateOfBirth": "1990-01-01",
    "gender": "Male",
    "currentPassword": "oldpassword",
    "newPassword": "newpassword123",
    "newPasswordConfirmation": "newpassword123"
  }
}
```

**Variables (إذا كان موثق - لا يمكن تحديث بيانات الهوية):**
```json
{
  "input": {
    "email": "newemail@example.com",
    "phone": "+966509876543",
    "currentPassword": "oldpassword",
    "newPassword": "newpassword123",
    "newPasswordConfirmation": "newpassword123"
  }
}
```

**ملاحظة:** إذا كان المواطن موثق (`identityVerificationStatus = true`)، لا يمكن تحديث:
- `firstName`, `middleName`, `lastName`
- `nationalId`
- `dateOfBirth`
- `gender`

---

## 🆔 توثيق الهوية (Identity Verification)

### الحصول على جميع طلبات التوثيق
```graphql
query MyIdentityVerifications {
  myIdentityVerifications {
    id
    status
    notes
    documents
    face_video
    reviewedBy
    reviewedAt
    createdAt
    updatedAt
  }
}
```

### الحصول على أحدث طلب توثيق
```graphql
query MyLatestIdentityVerification {
  myLatestIdentityVerification {
    id
    status
    notes
    documents
    face_video
    reviewedBy
    reviewedAt
    createdAt
    updatedAt
  }
}
```

### إنشاء طلب توثيق جديد
```graphql
mutation RequestIdentityVerification(
  $frontDocument: Upload
  $backDocument: Upload
  $faceVideo: Upload
) {
  requestIdentityVerification(
    input: {
      front_document: $frontDocument
      back_document: $backDocument
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
      createdAt
    }
  }
}
```

**Postman (Multipart Form Data):**
- `operations` (Text):
```json
{
  "query": "mutation RequestIdentityVerification($frontDocument: Upload, $backDocument: Upload, $faceVideo: Upload) { requestIdentityVerification(input: { front_document: $frontDocument, back_document: $backDocument, face_video: $faceVideo }) { success message verification { id status documents face_video createdAt } } }",
  "variables": {
    "frontDocument": null,
    "backDocument": null,
    "faceVideo": null
  }
}
```
- `map` (Text):
```json
{
  "0": ["variables.frontDocument"],
  "1": ["variables.backDocument"],
  "2": ["variables.faceVideo"]
}
```
- `0` (File): front-id.jpg
- `1` (File): back-id.jpg
- `2` (File): face-video.mp4

### تحديث طلب التوثيق (المستندات فقط)
```graphql
mutation UpdateMyIdentityVerification(
  $id: ID!
  $frontDocument: Upload
  $backDocument: Upload
  $faceVideo: Upload
) {
  updateMyIdentityVerification(
    id: $id
    input: {
      front_document: $frontDocument
      back_document: $backDocument
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
      updatedAt
    }
  }
}
```

**Postman (Multipart Form Data):**
- `operations` (Text):
```json
{
  "query": "mutation UpdateMyIdentityVerification($id: ID!, $frontDocument: Upload, $backDocument: Upload, $faceVideo: Upload) { updateMyIdentityVerification(id: $id, input: { front_document: $frontDocument, back_document: $backDocument, face_video: $faceVideo }) { success message verification { id status documents face_video updatedAt } } }",
  "variables": {
    "id": "1",
    "frontDocument": null,
    "backDocument": null,
    "faceVideo": null
  }
}
```
- `map` (Text):
```json
{
  "0": ["variables.frontDocument"],
  "1": ["variables.backDocument"],
  "2": ["variables.faceVideo"]
}
```
- `0` (File): front-id.jpg
- `1` (File): back-id.jpg
- `2` (File): face-video.mp4

**ملاحظة:** مسموح فقط إذا كانت الحالة `pending` أو `rejected`

### حذف طلب التوثيق
```graphql
mutation DeleteMyIdentityVerification($id: ID!) {
  deleteMyIdentityVerification(id: $id) {
    success
    message
  }
}
```

**Variables:**
```json
{
  "id": "1"
}
```

**ملاحظة:** مسموح فقط إذا كانت الحالة `pending`

---

## 🛠️ الخدمات (Services)

### الحصول على جميع الخدمات
```graphql
query CitizenServices {
  citizenServices {
    id
    name
    description
    status
    image
    sortOrder
    form {
      groups {
        code
        label
        description
        sortOrder
        fields {
          code
          label
          type
          isRequired
          defaultValue
          validationRules
          sortOrder
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

### الحصول على خدمة محددة
```graphql
query CitizenService($id: ID!) {
  citizenService(id: $id) {
    id
    name
    description
    status
    image
    sortOrder
    form {
      groups {
        code
        label
        description
        sortOrder
        fields {
          code
          label
          type
          isRequired
          defaultValue
          validationRules
          sortOrder
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

**Variables:**
```json
{
  "id": "1"
}
```

---

## 📋 طلبات الخدمات (Service Requests)

### الحصول على جميع طلبات الخدمات
```graphql
query MyServiceRequests($serviceId: Int, $status: ServiceRequestStatus) {
  myServiceRequests(serviceId: $serviceId, status: $status) {
    id
    serviceId
    status
    formData
    notes
    completedAt
    createdAt
    updatedAt
    service {
      id
      name
      description
    }
    beneficiaries {
      id
      firstName
      lastName
      nationalId
    }
  }
}
```

**Variables (اختياري):**
```json
{
  "serviceId": 1,
  "status": "PENDING"
}
```

### الحصول على طلب خدمة محدد
```graphql
query MyServiceRequest($id: ID!) {
  myServiceRequest(id: $id) {
    id
    serviceId
    status
    formData
    notes
    completedAt
    createdAt
    updatedAt
    service {
      id
      name
      description
    }
    beneficiaries {
      id
      firstName
      lastName
      nationalId
    }
  }
}
```

**Variables:**
```json
{
  "id": "1"
}
```

### إنشاء طلب خدمة جديد
```graphql
mutation CreateServiceRequest($input: CreateServiceRequestInput!) {
  createServiceRequest(input: $input) {
    success
    message
    request {
      id
      status
      formData
      notes
      createdAt
      service {
        id
        name
      }
    }
  }
}
```

**Variables:**
```json
{
  "input": {
    "serviceId": 1,
    "formData": {
      "field1": "value1",
      "field2": "value2"
    },
    "notes": "ملاحظات إضافية"
  }
}
```

### تحديث طلب خدمة
```graphql
mutation UpdateServiceRequest($id: ID!, $input: UpdateServiceRequestInput!) {
  updateServiceRequest(id: $id, input: $input) {
    success
    message
    request {
      id
      status
      formData
      notes
      updatedAt
    }
  }
}
```

**Variables:**
```json
{
  "id": "1",
  "input": {
    "formData": {
      "field1": "updated_value1",
      "field2": "updated_value2"
    },
    "notes": "ملاحظات محدثة"
  }
}
```

**ملاحظة:** مسموح فقط إذا كانت الحالة `pending` أو `in_progress`

### إلغاء طلب خدمة
```graphql
mutation CancelServiceRequest($id: ID!) {
  cancelServiceRequest(id: $id) {
    success
    message
  }
}
```

**Variables:**
```json
{
  "id": "1"
}
```

**ملاحظة:** مسموح فقط إذا كانت الحالة `pending` أو `in_progress`

---

## 📝 ملاحظات مهمة

### المصادقة
- جميع الاستعلامات (ما عدا `citizenLogin` و `citizenSignUp`) تتطلب token في header:
  ```
  Authorization: Bearer YOUR_TOKEN
  ```

### رفع الملفات
- لرفع الملفات (في طلبات التوثيق)، استخدم `multipart/form-data` في Postman
- اتبع تنسيق `graphql-multipart-request-spec`

### حالات التوثيق
- `pending`: قيد المراجعة
- `approved`: موثق
- `rejected`: مرفوض

### حالات طلبات الخدمات
- `PENDING`: قيد الانتظار
- `IN_PROGRESS`: قيد المعالجة
- `COMPLETED`: مكتمل
- `REJECTED`: مرفوض
- `CANCELLED`: ملغى

---

## 🔒 القيود والأذونات

### توثيق الهوية
- ✅ المواطن فقط يمكنه إنشاء/تعديل/حذف طلبات التوثيق
- ✅ التعديل مسموح فقط إذا كانت الحالة `pending` أو `rejected`
- ✅ الحذف مسموح فقط إذا كانت الحالة `pending`

### الملف الشخصي
- ✅ إذا كان المواطن موثق، لا يمكن تحديث بيانات الهوية (الاسم، رقم الهوية، تاريخ الميلاد، الجنس)
- ✅ يمكن تحديث البريد الإلكتروني والهاتف وكلمة المرور حتى لو كان موثق

### طلبات الخدمات
- ✅ المواطن يمكنه إنشاء/تعديل/إلغاء طلباته فقط
- ✅ التعديل مسموح فقط إذا كانت الحالة `pending` أو `in_progress`
- ✅ الإلغاء مسموح فقط إذا كانت الحالة `pending` أو `in_progress`

