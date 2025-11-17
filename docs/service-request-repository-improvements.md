# تحسينات Repository لطلبات الخدمة

## نظرة عامة

تم تحسين منطق العمليات في نظام طلبات الخدمة من خلال:

1. ✅ نقل كل المنطق من Mutation إلى Repository
2. ✅ إضافة Transactions (معاملات قاعدة البيانات)
3. ✅ إضافة Try-Catch للتعامل مع الأخطاء
4. ✅ إضافة Events (الأحداث)

---

## البنية الجديدة

### 1. Events (الأحداث)

تم إنشاء ثلاثة أحداث في `packages/Najaz/Request/src/Events/`:

- **`ServiceRequestCreated`**: يتم إطلاقه عند إنشاء طلب خدمة جديد
- **`ServiceRequestUpdated`**: يتم إطلاقه عند تحديث طلب خدمة
- **`ServiceRequestCancelled`**: يتم إطلاقه عند إلغاء طلب خدمة

**الأحداث النصية (String-based Events)** - متوافقة مع نمط Bagisto:

- `service.request.save.before` - قبل إنشاء الطلب (يستقبل `[$data, $citizenId]`)
- `service.request.save.after` - بعد إنشاء الطلب (يستقبل `$request`)
- `service.request.update.before` - قبل تحديث الطلب (يستقبل `[$id, $data]`)
- `service.request.update.after` - بعد تحديث الطلب (يستقبل `$request`)
- `service.request.cancel.before` - قبل إلغاء الطلب (يستقبل `$request`)
- `service.request.cancel.after` - بعد إلغاء الطلب (يستقبل `$request`)

**مثال على الاستخدام**:
```php
// String-based events (نمط Bagisto)
Event::listen('service.request.save.after', function ($request) {
    // إرسال إشعار
    // إرسال بريد إلكتروني
});

// Event classes (أفضل من الناحية البرمجية)
use Najaz\Request\Events\ServiceRequestCreated;

Event::listen(ServiceRequestCreated::class, function ($event) {
    $serviceRequest = $event->serviceRequest;
    // ...
});
```

### 2. Repository Methods

#### `createServiceRequestIfNotThenRetry(array $data, int $citizenId)`

الدالة الأساسية لإنشاء طلب خدمة (مطابقة لنمط Bagisto):

- ✅ استخدام `DB::beginTransaction()` و `DB::commit()` و `DB::rollBack()`
- ✅ إطلاق Event `service.request.save.before` قبل الإنشاء
- ✅ التحقق من أن الخدمة متاحة لنوع المواطن
- ✅ تنظيف `form_data` (إزالة الحقول غير المرتبطة)
- ✅ التحقق من الحقول المطلوبة
- ✅ التحقق من قواعد التحقق
- ✅ إنشاء الطلب
- ✅ ربط الأطراف المستفيدة
- ✅ إطلاق Event `service.request.save.after` بعد الإنشاء
- ✅ تسجيل الأخطاء في Log
- ✅ استخدام `finally` block لضمان commit

#### `createWithValidation(array $data, int $citizenId)`

دالة wrapper تستدعي `createServiceRequestIfNotThenRetry`:

**مثال**:
```php
$request = $repository->createWithValidation([
    'service_id' => 1,
    'form_data' => [...],
    'notes' => 'ملاحظات',
], $citizenId);
```

#### `updateRequest(array $data, int $id)`

تحديث طلب خدمة:

- ✅ تحديث البيانات
- ✅ إطلاق Event `ServiceRequestUpdated`
- ✅ استخدام Transaction

#### `cancelRequest(int $id)`

إلغاء طلب خدمة:

- ✅ تحديث الحالة إلى `cancelled`
- ✅ إطلاق Event `ServiceRequestCancelled`
- ✅ استخدام Transaction

---

## Mutation المبسط

الآن `ServiceRequestMutation` أصبح بسيطاً جداً:

```php
public function store($rootValue, array $args): array
{
    $citizen = najaz_graphql()->authorize('citizen-api');

    najaz_graphql()->validate($args, [
        'service_id'  => ['required', 'integer', 'exists:services,id'],
        'form_data'  => ['required', 'array'],
        'notes'      => ['nullable', 'string'],
    ]);

    $request = $this->serviceRequestRepository->createWithValidation($args, $citizen->id);

    return [
        'success' => true,
        'message' => trans('najaz_graphql::app.citizens.service_request.created'),
        'request' => $request,
    ];
}
```

**المزايا**:
- ✅ كود أنظف وأسهل للقراءة
- ✅ فصل الاهتمامات (Separation of Concerns)
- ✅ إعادة استخدام أسهل
- ✅ اختبار أسهل

---

## Transactions (المعاملات)

جميع العمليات الآن تتم باستخدام `DB::beginTransaction()` و `DB::commit()` و `DB::rollBack()` (مطابقة لنمط Bagisto):

```php
DB::beginTransaction();

try {
    // العمليات هنا
    Event::dispatch('service.request.save.before', [$data, $citizenId]);
    
    // ... إنشاء الطلب ...
    
    Event::dispatch('service.request.save.after', $request);
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('...', [...]);
    throw $e;
} finally {
    DB::commit();
}
```

**المزايا**:
- ✅ ضمان تكامل البيانات (Data Integrity)
- ✅ التراجع التلقائي عند حدوث خطأ
- ✅ منع البيانات غير المكتملة
- ✅ مطابقة لنمط Bagisto الأصلي

---

## Error Handling (التعامل مع الأخطاء)

تم إضافة Try-Catch شامل مع Logging (مطابق لنمط Bagisto):

```php
DB::beginTransaction();

try {
    // العمليات
} catch (CustomException $e) {
    DB::rollBack();
    Log::error('ServiceRequestRepository:createServiceRequestIfNotThenRetry: '.$e->getMessage(), ['data' => $data]);
    throw $e; // إعادة رمي CustomException كما هي
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('ServiceRequestRepository:createServiceRequestIfNotThenRetry: '.$e->getMessage(), ['data' => $data]);
    throw new CustomException(
        trans('najaz_graphql::app.citizens.service_request.create_error', [
            'message' => $e->getMessage(),
        ])
    );
} finally {
    DB::commit();
}
```

**المزايا**:
- ✅ معالجة شاملة للأخطاء
- ✅ رسائل خطأ واضحة
- ✅ تسجيل الأخطاء في Log
- ✅ منع تسريب معلومات حساسة
- ✅ مطابقة لنمط Bagisto الأصلي

---

## Events (الأحداث)

يمكنك الآن الاستماع للأحداث في أي مكان في التطبيق:

### إنشاء Listener

```php
// في EventServiceProvider أو أي مكان آخر
use Najaz\Request\Events\ServiceRequestCreated;
use Illuminate\Support\Facades\Event;

Event::listen(ServiceRequestCreated::class, function ($event) {
    $serviceRequest = $event->serviceRequest;
    
    // إرسال إشعار
    // إرسال بريد إلكتروني
    // تحديث إحصائيات
    // إلخ...
});
```

### استخدام String-based Events (اختياري)

يمكنك أيضاً استخدام الأحداث النصية:

```php
Event::listen('service.request.created', function ($serviceRequest) {
    // ...
});
```

---

## الفوائد الرئيسية

### 1. **فصل الاهتمامات**
- Mutation: فقط التحقق من الصلاحيات والتحقق الأساسي
- Repository: كل منطق العمل

### 2. **إعادة الاستخدام**
- يمكن استخدام `createWithValidation` من أي مكان (Controllers, Jobs, Commands, إلخ)

### 3. **الاختبار**
- أسهل في الاختبار (Unit Testing)
- يمكن اختبار Repository بشكل منفصل

### 4. **الأمان**
- Transactions تضمن تكامل البيانات
- Error handling شامل

### 5. **المرونة**
- Events تسمح بإضافة وظائف جديدة بدون تعديل الكود الأساسي

---

## مثال كامل

### قبل التحسين:
```php
// في Mutation - كل المنطق هنا (500+ سطر)
public function store($rootValue, array $args): array
{
    // التحقق
    // تنظيف البيانات
    // التحقق من الحقول
    // إنشاء الطلب
    // ربط الأطراف
    // إلخ...
}
```

### بعد التحسين:
```php
// في Mutation - بسيط ونظيف (20 سطر)
public function store($rootValue, array $args): array
{
    $citizen = najaz_graphql()->authorize('citizen-api');
    
    najaz_graphql()->validate($args, [...]);
    
    $request = $this->serviceRequestRepository->createWithValidation($args, $citizen->id);
    
    return ['success' => true, 'request' => $request];
}
```

---

## الخطوات التالية

يمكنك الآن:

1. ✅ إنشاء Listeners للأحداث (إرسال إشعارات، بريد إلكتروني، إلخ)
2. ✅ إضافة المزيد من التحقق في Repository
3. ✅ إضافة Logging للأحداث
4. ✅ إنشاء Jobs للأعمال الثقيلة (مثل إرسال الإشعارات)

---

## ملاحظات

- جميع العمليات الآن آمنة مع Transactions
- الأخطاء يتم التعامل معها بشكل شامل
- الكود أصبح أكثر قابلية للصيانة والتطوير

