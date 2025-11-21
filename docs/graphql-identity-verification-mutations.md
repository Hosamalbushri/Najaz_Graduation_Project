# GraphQL Identity Verification Mutations

## التعديل (Update)

### Schema
```graphql
mutation UpdateIdentityVerification($id: ID!, $frontDocument: Upload, $backDocument: Upload, $documents: [Upload!], $faceVideo: Upload) {
  updateIdentityVerification(
    id: $id
    input: {
      front_document: $frontDocument
      back_document: $backDocument
      documents: $documents
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
      updatedAt
    }
  }
}
```

### Postman (Multipart)

**Operations** (Text):
```json
{
  "query": "mutation UpdateIdentityVerification($id: ID!, $frontDocument: Upload, $backDocument: Upload, $faceVideo: Upload) { updateIdentityVerification(id: $id, input: { front_document: $frontDocument, back_document: $backDocument, face_video: $faceVideo }) { success message verification { id status documents face_video updatedAt } } }",
  "variables": {
    "id": "1",
    "frontDocument": null,
    "backDocument": null,
    "faceVideo": null
  }
}
```

**Map** (Text):
```json
{
  "0": ["variables.frontDocument"],
  "1": ["variables.backDocument"],
  "2": ["variables.faceVideo"]
}
```

**Files**:
- `0` (File): `front-id.jpg`
- `1` (File): `back-id.jpg`
- `2` (File): `face-video.mp4`

### مثال: تحديث الفيديو فقط
```json
{
  "query": "mutation UpdateIdentityVerification($id: ID!, $faceVideo: Upload) { updateIdentityVerification(id: $id, input: { face_video: $faceVideo }) { success message verification { id status face_video } } }",
  "variables": {
    "id": "1",
    "faceVideo": null
  }
}
```

**Map**:
```json
{
  "0": ["variables.faceVideo"]
}
```

**Files**:
- `0` (File): `face-video.mp4`

---

## الحذف (Delete)

### Schema
```graphql
mutation DeleteIdentityVerification($id: ID!) {
  deleteIdentityVerification(id: $id) {
    success
    message
  }
}
```

### Postman (JSON)

**Body** (raw → JSON):
```json
{
  "query": "mutation DeleteIdentityVerification($id: ID!) { deleteIdentityVerification(id: $id) { success message } }",
  "variables": {
    "id": "1"
  }
}
```

---

## الشروط والقيود

### التعديل:
- ✅ مسموح فقط إذا كانت الحالة `pending` أو `rejected`
- ✅ يجب رفع ملف واحد على الأقل (مستندات أو فيديو)
- ✅ عند رفع مستندات جديدة، يتم حذف القديمة تلقائياً
- ✅ إذا كانت الحالة `rejected` وتم التعديل، تُعاد الحالة إلى `pending` ويتم مسح ملاحظات الرفض

### الحذف:
- ✅ مسموح فقط إذا كانت الحالة `pending`
- ✅ يتم حذف جميع الملفات المرتبطة (المستندات والفيديو)

---

## الاستجابات

### نجاح التعديل:
```json
{
  "data": {
    "updateIdentityVerification": {
      "success": true,
      "message": "تم تحديث مستندات توثيق الهوية بنجاح.",
      "verification": {
        "id": "1",
        "status": "pending",
        "documents": ["path/to/front.jpg", "path/to/back.jpg"],
        "face_video": "path/to/video.mp4",
        "updatedAt": "2025-01-15 12:00:00"
      }
    }
  }
}
```

### نجاح الحذف:
```json
{
  "data": {
    "deleteIdentityVerification": {
      "success": true,
      "message": "تم حذف طلب توثيق الهوية بنجاح."
    }
  }
}
```

### خطأ (الحالة غير صحيحة):
```json
{
  "data": {
    "updateIdentityVerification": {
      "success": false,
      "message": "يمكن تحديث توثيق الهوية فقط عندما تكون الحالة قيد المراجعة أو مرفوضة.",
      "verification": null
    }
  }
}
```

