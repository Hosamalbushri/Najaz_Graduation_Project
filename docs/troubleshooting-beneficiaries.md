# استكشاف أخطاء الأطراف المستفيدة (Beneficiaries)

## المشكلة: لم يتم إنشاء الأطراف المستفيدة

إذا لم يتم ربط الأطراف المستفيدة بالطلب، تحقق من النقاط التالية:

---

## 1. التحقق من `is_notifiable` في قاعدة البيانات

الأطراف المستفيدة يتم استخراجها فقط من المجموعات التي `is_notifiable = 1`.

### التحقق:

```sql
SELECT 
    sag.id as group_id,
    sag.code as group_code,
    sag.name as group_name,
    sags.custom_code,
    sags.is_notifiable,
    s.id as service_id,
    s.name as service_name
FROM service_attribute_group_service sags
JOIN service_attribute_groups sag ON sags.service_attribute_group_id = sag.id
JOIN services s ON sags.service_id = s.id
WHERE s.id = 1;  -- استبدل 1 بـ service_id الخاص بك
```

**يجب أن يكون `is_notifiable = 1` للمجموعات التي تريد استخراج الأطراف منها.**

### التحديث:

```sql
UPDATE service_attribute_group_service
SET is_notifiable = 1
WHERE service_id = 1 
  AND service_attribute_group_id IN (
    SELECT id FROM service_attribute_groups 
    WHERE code IN ('hosam', 'personal_data')  -- استبدل بأكواد المجموعات الخاصة بك
  );
```

---

## 2. التحقق من وجود حقل رقم الهوية

النظام يبحث عن حقل يحتوي على رقم الهوية في كل مجموعة `is_notifiable`.

### الحقول المدعومة:

- `national_id`
- `citizen_id`
- `nationalId`
- `citizenId`
- `id_number`
- `idNumber`
- `national_number`
- `identity_number`

### التحقق:

```sql
SELECT 
    saf.code as field_code,
    saf.label,
    sag.code as group_code,
    sag.name as group_name
FROM service_attribute_fields saf
JOIN service_attribute_groups sag ON saf.service_attribute_group_id = sag.id
JOIN service_attribute_group_service sags ON sag.id = sags.service_attribute_group_id
WHERE sags.service_id = 1
  AND sags.is_notifiable = 1
  AND LOWER(saf.code) IN (
    'national_id', 'citizen_id', 'nationalid', 'citizenid',
    'id_number', 'idnumber', 'national_number', 'identity_number'
  );
```

**يجب أن يوجد حقل واحد على الأقل من الحقول أعلاه في كل مجموعة `is_notifiable`.**

---

## 3. التحقق من البيانات المرسلة في `form_data`

### المطلوب:

1. **استخدام `customCode`** كمفتاح (مثل `wife_data`, `husband_data`)
2. **إرسال رقم الهوية** في الحقل الصحيح

### مثال صحيح:

```json
{
  "wife_data": {
    "id_number": "1234567890"  // ✅ يجب أن يكون موجود
  },
  "husband_data": {
    "id_number": "0987654321"  // ✅ يجب أن يكون موجود
  }
}
```

### مثال خاطئ:

```json
{
  "wife_data": {
    "citizen_name": "علي"  // ❌ لا يوجد id_number
  }
}
```

---

## 4. التحقق من وجود المواطن في قاعدة البيانات

النظام يبحث عن المواطن برقم الهوية. يجب أن يكون المواطن موجوداً في جدول `citizens`.

### التحقق:

```sql
SELECT id, first_name, last_name, national_id
FROM citizens
WHERE national_id IN ('1234567890', '0987654321');  -- استبدل بأرقام الهوية المرسلة
```

**يجب أن يكون المواطن موجوداً في قاعدة البيانات.**

---

## 5. التحقق من تطابق رقم الهوية

النظام ينظف رقم الهوية (يزيل المسافات والشرطات) قبل البحث.

### أمثلة:

- `"1234567890"` ✅
- `"1234-5678-90"` ✅ (سيتم تنظيفه إلى `1234567890`)
- `"1234 5678 90"` ✅ (سيتم تنظيفه إلى `1234567890`)

لكن يجب أن يطابق الرقم المخزن في قاعدة البيانات بعد التنظيف.

---

## 6. اختبار يدوي

### الخطوات:

1. **احصل على النموذج**:
```graphql
query {
  citizenService(id: 1) {
    form {
      groups {
        code
        customCode
        isNotifiable
        fields {
          code
          label
        }
      }
    }
  }
}
```

2. **تحقق من**:
   - ✅ `isNotifiable: true` للمجموعات المطلوبة
   - ✅ وجود حقل `id_number` أو `national_id` في كل مجموعة
   - ✅ `customCode` موجود (مثل `wife_data`, `husband_data`)

3. **أرسل الطلب** مع البيانات الصحيحة:
```json
{
  "input": {
    "serviceId": 1,
    "formData": {
      "wife_data": {
        "id_number": "1234567890"  // ✅ رقم هوية موجود في قاعدة البيانات
      },
      "husband_data": {
        "id_number": "0987654321"  // ✅ رقم هوية موجود في قاعدة البيانات
      }
    }
  }
}
```

4. **تحقق من النتيجة**:
```graphql
query {
  myServiceRequest(id: 5) {
    id
    beneficiaries {
      id
      firstName
      lastName
      nationalId
    }
  }
}
```

---

## 7. سجلات الأخطاء (Logging)

إذا أردت إضافة logging للتحقق من المشكلة، يمكنك إضافة:

```php
// في linkBeneficiaries method
\Log::info('Notifiable groups found', [
    'count' => $notifiableGroups->count(),
    'groups' => $notifiableGroups->map(fn($g) => [
        'code' => $g->code,
        'custom_code' => $g->pivot->custom_code ?? null,
        'is_notifiable' => $g->pivot->is_notifiable ?? false,
    ])->toArray()
]);

\Log::info('Form data received', ['form_data' => $formData]);

\Log::info('Beneficiaries found', [
    'count' => count($beneficiaries),
    'beneficiaries' => $beneficiaries
]);
```

---

## 8. قائمة التحقق السريعة

- [ ] `is_notifiable = 1` في `service_attribute_group_service`
- [ ] وجود حقل `id_number` أو `national_id` في المجموعات `is_notifiable`
- [ ] استخدام `customCode` في `form_data` (مثل `wife_data`, `husband_data`)
- [ ] إرسال `id_number` في `form_data` لكل مجموعة
- [ ] المواطن موجود في قاعدة البيانات برقم الهوية المرسل
- [ ] رقم الهوية يطابق `national_id` في جدول `citizens`

---

## 9. أمثلة على المشاكل الشائعة

### المشكلة 1: `is_notifiable` غير مفعل

**الحل**: 
```sql
UPDATE service_attribute_group_service
SET is_notifiable = 1
WHERE service_id = 1 AND service_attribute_group_id = 5;
```

### المشكلة 2: لا يوجد حقل `id_number`

**الحل**: أضف حقل `id_number` إلى المجموعة في لوحة التحكم.

### المشكلة 3: استخدام `code` بدلاً من `customCode`

**الخطأ**:
```json
{
  "hosam": {  // ❌ code الأصلي
    "id_number": "1234567890"
  }
}
```

**الصحيح**:
```json
{
  "wife_data": {  // ✅ customCode
    "id_number": "1234567890"
  }
}
```

### المشكلة 4: المواطن غير موجود

**الحل**: تأكد من أن المواطن مسجل في النظام برقم الهوية الصحيح.

---

## 10. اختبار سريع

```graphql
mutation Test {
  createServiceRequest(
    input: {
      serviceId: 1
      formData: {
        "wife_data": {
          "id_number": "1234567890"  # تأكد من وجود هذا المواطن
        }
      }
    }
  ) {
    success
    request {
      id
      beneficiaries {
        id
        firstName
        nationalId
      }
    }
  }
}
```

إذا لم يظهر `beneficiaries`، راجع النقاط أعلاه.

