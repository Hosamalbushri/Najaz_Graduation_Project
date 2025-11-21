# نظام إدارة حالات طلبات الخدمات

## نظرة عامة

تم تطوير نظام إدارة حالات طلبات الخدمات مع دعم رفض الطلبات وإدارة الملاحظات الإدارية. النظام يسمح للمسؤولين بتحديث حالة الطلب، رفضه مع إدخال سبب الرفض، وإضافة ملاحظات إدارية.

---

## المميزات الرئيسية

### 1. إدارة حالات الطلبات
- ✅ تحديث حالة الطلب (معلقة، قيد المعالجة، مكتملة، مرفوضة، ملغاة)
- ✅ رفض الطلب مع إدخال سبب الرفض
- ✅ إكمال الطلب تلقائياً
- ✅ إلغاء الطلب

### 2. عرض الأزرار حسب الحالة
- **معلقة (pending)**: زر "قيد المعالجة" وزر "رفض"
- **قيد المعالجة (in_progress)**: زر "إكمال"
- **مكتملة (completed)**: زر "طباعة" وزر "إلغاء"

### 3. إدارة الملاحظات الإدارية
- ✅ إضافة ملاحظات متعددة لكل طلب
- ✅ إعلام المواطن عند إضافة ملاحظة
- ✅ عرض تاريخ واسم المسؤول لكل ملاحظة

---

## التغييرات في قاعدة البيانات

### 1. إضافة حقل `rejection_reason`

**Migration**: `2025_01_21_000003_add_rejection_reason_to_service_requests_table.php`

```php
Schema::table('service_requests', function (Blueprint $table) {
    $table->text('rejection_reason')->nullable()->after('status');
});
```

**الوصف**: حقل نصي لحفظ سبب رفض الطلب.

---

## التغييرات في Models

### 1. تحديث `ServiceRequest` Model

**الملف**: `packages/Najaz/Request/src/Models/ServiceRequest.php`

**التغييرات**:
- إضافة `rejection_reason` إلى `$fillable`

```php
protected $fillable = [
    // ... existing fields
    'rejection_reason',
    // ... existing fields
];
```

---

## التغييرات في Controllers

### 1. تحديث `ServiceRequestController`

**الملف**: `packages/Najaz/Admin/src/Http/Controllers/Admin/ServiceRequests/ServiceRequestController.php`

#### أ. Method `updateStatus()`

**التغييرات**:
- إزالة `JsonResponse` من return type
- استخدام `redirect()->route()` بدلاً من `JsonResponse`
- دعم `rejection_reason` عند رفض الطلب
- تعيين `completed_at` تلقائياً عند إكمال الطلب
- مسح `rejection_reason` عند تغيير الحالة من مرفوضة

**الكود**:
```php
public function updateStatus(int $id)
{
    $validatedData = $this->validate(request(), [
        'status'           => 'required|string|in:pending,in_progress,completed,rejected,canceled',
        'rejection_reason' => 'required_if:status,rejected|nullable|string',
    ]);

    try {
        $updateData = ['status' => $validatedData['status']];

        // Add rejection reason if status is rejected
        if ($validatedData['status'] === 'rejected') {
            $updateData['rejection_reason'] = $validatedData['rejection_reason'] ?? null;
        } else {
            // Clear rejection reason if status is not rejected
            $updateData['rejection_reason'] = null;
        }

        // Set completed_at if status is completed
        if ($validatedData['status'] === 'completed') {
            $updateData['completed_at'] = now();
        }

        $request = $this->serviceRequestRepository->update($updateData, $id);

        session()->flash('success', trans('Admin::app.service-requests.view.status-update-success'));

        return redirect()->route('admin.service-requests.view', $request->id);

    } catch (\Exception $e) {
        session()->flash('error', $e->getMessage());

        return redirect()->back();
    }
}
```

#### ب. Method `cancel()`

**التغييرات**:
- إزالة `JsonResponse` من return type
- استخدام `redirect()->route()` بدلاً من `JsonResponse`

**الكود**:
```php
public function cancel(int $id)
{
    try {
        $request = $this->serviceRequestRepository->cancelRequest($id);

        session()->flash('success', trans('Admin::app.service-requests.view.cancel-success'));

        return redirect()->route('admin.service-requests.view', $request->id);
    } catch (\Exception $e) {
        session()->flash('error', $e->getMessage());

        return redirect()->back();
    }
}
```

#### ج. Method `addNotes()`

**التغييرات**:
- إزالة `JsonResponse` من return type
- استخدام `redirect()->route()` بدلاً من `JsonResponse`

**الكود**:
```php
public function addNotes(int $id)
{
    $validatedData = $this->validate(request(), [
        'admin_notes'      => 'required|string',
        'citizen_notified' => 'sometimes|boolean',
    ]);

    try {
        $this->adminNoteRepository->create([
            'service_request_id' => $id,
            'note'               => $validatedData['admin_notes'],
            'citizen_notified'   => $validatedData['citizen_notified'] ?? false,
            'admin_id'           => auth()->guard('admin')->id(),
        ]);

        session()->flash('success', trans('Admin::app.service-requests.view.notes-success'));

        return redirect()->route('admin.service-requests.view', $id);
    } catch (\Exception $e) {
        session()->flash('error', $e->getMessage());

        return redirect()->back();
    }
}
```

---

## التغييرات في Views

### 1. تحديث صفحة عرض الطلب

**الملف**: `packages/Najaz/Admin/src/Resources/views/service-requests/view.blade.php`

#### أ. الأزرار حسب الحالة

**معلقة (pending)**:
```blade
<!-- زر "قيد المعالجة" -->
<form method="POST" ref="inProgressForm" action="{{ route('admin.service-requests.update-status', $request->id) }}">
    @csrf
    <input type="hidden" name="status" value="in_progress">
</form>
<button type="button" class="..." @click="$refs.inProgressForm.submit()">
    <span class="icon-checkmark text-2xl"></span>
    @lang('Admin::app.service-requests.view.in-progress')
</button>

<!-- زر "رفض" -->
<button type="button" class="..." @click="$refs.rejectModal.toggle()">
    <span class="icon-cancel-1 text-2xl"></span>
    @lang('Admin::app.service-requests.view.reject')
</button>
```

**قيد المعالجة (in_progress)**:
```blade
<form method="POST" ref="completeForm" action="{{ route('admin.service-requests.update-status', $request->id) }}">
    @csrf
    <input type="hidden" name="status" value="completed">
</form>
<button type="button" class="..." @click="$refs.completeForm.submit()">
    <span class="icon-checkmark text-2xl"></span>
    @lang('Admin::app.service-requests.view.complete')
</button>
```

**مكتملة (completed)**:
```blade
<!-- زر "طباعة" -->
<a href="{{ route('admin.service-requests.print', $request->id) }}" class="...">
    <span class="icon-printer text-2xl"></span>
    @lang('Admin::app.service-requests.view.print')
</a>

<!-- زر "إلغاء" -->
<form method="POST" ref="cancelRequestForm" action="{{ route('admin.service-requests.cancel', $request->id) }}">
    @csrf
</form>
<button type="button" class="..." @click="$emitter.emit('open-confirm-modal', {...})">
    <span class="icon-cancel text-2xl"></span>
    @lang('Admin::app.service-requests.view.cancel')
</button>
```

#### ب. Modal رفض الطلب

**استخدام `x-admin::modal`**:
```blade
<x-admin::modal ref="rejectModal">
    <x-slot:header>
        <p class="text-lg font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.service-requests.view.reject')
        </p>
    </x-slot>

    <x-slot:content>
        <x-admin::form action="{{ route('admin.service-requests.update-status', $request->id) }}">
            @csrf
            <input type="hidden" name="status" value="rejected">

            <div class="flex flex-col gap-4">
                <p class="text-base text-gray-600 dark:text-gray-300">
                    @lang('Admin::app.service-requests.view.reject-msg')
                </p>

                <x-admin::form.control-group>
                    <x-admin::form.control-group.label class="required">
                        @lang('Admin::app.service-requests.view.rejection-reason')
                    </x-admin::form.control-group.label>

                    <x-admin::form.control-group.control
                        type="textarea"
                        name="rejection_reason"
                        rules="required"
                        :label="trans('Admin::app.service-requests.view.rejection-reason')"
                        :placeholder="trans('Admin::app.service-requests.view.rejection-reason-required')"
                        rows="4"
                    />

                    <x-admin::form.control-group.error control-name="rejection_reason" />
                </x-admin::form.control-group>

                <div class="flex items-center gap-x-2.5 justify-end">
                    <button type="button" @click="$refs.rejectModal.close()" class="...">
                        @lang('admin::app.components.modal.confirm.disagree-btn')
                    </button>
                    <button type="submit" class="primary-button">
                        @lang('Admin::app.service-requests.view.reject')
                    </button>
                </div>
            </div>
        </x-admin::form>
    </x-slot>
</x-admin::modal>
```

#### ج. عرض سبب الرفض

```blade
@if ($request->status === 'rejected' && $request->rejection_reason)
    <x-admin::accordion>
        <x-slot:header>
            <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                @lang('Admin::app.service-requests.view.rejection-reason')
            </p>
        </x-slot>

        <x-slot:content>
            <div class="p-4">
                <p class="text-base leading-6 text-gray-800 dark:text-white">
                    {{ $request->rejection_reason }}
                </p>
            </div>
        </x-slot>
    </x-admin::accordion>
@endif
```

#### د. تحسين عرض حقول النماذج

**إزالة الحدود**:
- إزالة `border-b` و `dark:border-gray-800` من divs المجموعات
- إزالة `border-l-4` من divs الحقول

**تخطيط مرن للأعمدة**:
```blade
<div class="mt-4 grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));">
    @foreach ($formData->fields_data as $fieldCode => $fieldValue)
        <div class="flex items-start gap-2 pl-4 min-w-0 w-full">
            <!-- Field content -->
        </div>
    @endforeach
</div>
```

**الوصف**: 
- إذا كان النص كبير: يأخذ العمود المساحة الكاملة
- إذا كان النص صغير: يمكن أن يكون بجانب أعمدة أخرى
- الأعمدة تنتقل تلقائياً إلى سطر جديد عند الحاجة

---

## الترجمات المضافة

### العربية (`packages/Najaz/Admin/src/Resources/lang/ar/app.php`)

```php
'reject' => 'رفض',
'complete' => 'إكمال',
'rejection-reason' => 'سبب الرفض',
'reject-msg' => 'يرجى إدخال سبب الرفض',
'rejection-reason-required' => 'سبب الرفض مطلوب',
'in_progress' => 'قيد المعالجة',
```

### الإنجليزية (`packages/Najaz/Admin/src/Resources/lang/en/app.php`)

```php
'reject' => 'Reject',
'complete' => 'Complete',
'rejection-reason' => 'Rejection Reason',
'reject-msg' => 'Please enter the rejection reason',
'rejection-reason-required' => 'Rejection reason is required',
'in_progress' => 'In Progress',
```

---

## النمط المستخدم في Controllers

تم توحيد النمط المستخدم في جميع الـ methods:

### النجاح:
```php
session()->flash('success', trans('...'));
return redirect()->route('admin.service-requests.view', $id);
```

### الخطأ:
```php
session()->flash('error', $e->getMessage());
return redirect()->back();
```

**الفوائد**:
- ✅ توحيد النمط مع باقي Controllers في المشروع
- ✅ استخدام `session()->flash()` لعرض الرسائل
- ✅ استخدام `redirect()->route()` لإعادة التوجيه
- ✅ سهولة الصيانة والتطوير

---

## ملاحظات مهمة

1. **حقل `rejection_reason`**: يتم مسحه تلقائياً عند تغيير الحالة من "مرفوضة" إلى أي حالة أخرى.

2. **حقل `completed_at`**: يتم تعيينه تلقائياً عند تغيير الحالة إلى "مكتملة".

3. **الأزرار**: تظهر حسب الحالة الحالية للطلب فقط.

4. **Modal الرفض**: يستخدم `x-admin::modal` القياسي مع `x-admin::form`.

5. **التخطيط المرن**: حقول النماذج تستخدم grid layout مرن يتكيف مع حجم المحتوى.

---

## الملفات المعدلة

1. `packages/Najaz/Request/src/Database/Migrations/2025_01_21_000003_add_rejection_reason_to_service_requests_table.php` (جديد)
2. `packages/Najaz/Request/src/Models/ServiceRequest.php`
3. `packages/Najaz/Admin/src/Http/Controllers/Admin/ServiceRequests/ServiceRequestController.php`
4. `packages/Najaz/Admin/src/Resources/views/service-requests/view.blade.php`
5. `packages/Najaz/Admin/src/Resources/lang/ar/app.php`
6. `packages/Najaz/Admin/src/Resources/lang/en/app.php`

---

## كيفية الاستخدام

### 1. رفض طلب:
1. اضغط على زر "رفض" عندما تكون الحالة "معلقة"
2. أدخل سبب الرفض في الـ modal
3. اضغط على "رفض" لإرسال الطلب

### 2. تحديث الحالة:
1. اضغط على الزر المناسب حسب الحالة:
   - "قيد المعالجة" (من معلقة)
   - "إكمال" (من قيد المعالجة)
2. سيتم تحديث الحالة تلقائياً

### 3. إضافة ملاحظة:
1. اكتب الملاحظة في حقل "ملاحظات الأدمن"
2. اختر "إعلام المواطن" إذا أردت
3. اضغط على "إرسال الملاحظات"

---

## تاريخ التحديث

- **التاريخ**: 2025-01-21
- **الإصدار**: 1.0.0


