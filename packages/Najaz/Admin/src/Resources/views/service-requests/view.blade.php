<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.service-requests.view.title', ['request_id' => $request->increment_id])
    </x-slot>

    <!-- Header -->
    <div class="grid">
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <div class="flex items-center gap-2.5">
                <p class="text-xl font-bold leading-6 text-gray-800 dark:text-white">
                    @lang('Admin::app.service-requests.view.title', ['request_id' => $request->increment_id])
                </p>

                <!-- Request Status -->
                <span class="label-{{ $request->status }} text-sm mx-1.5">
                    @lang("Admin::app.service-requests.view.$request->status")
                </span>
            </div>

            <!-- Back Button -->
            <a
                href="{{ route('admin.service-requests.index') }}"
                class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('admin::app.account.edit.back-btn')
            </a>
        </div>
    </div>

    @if (bouncer()->hasPermission('service-requests.update'))
        <div class="mt-5 flex-wrap items-center justify-between gap-x-1 gap-y-2">
            <div class="flex gap-1.5">
                @if ($request->status === 'pending')
                    <!-- Pending: Show "In Progress" and "Reject" buttons -->
                    @include('admin::service-requests.update-status', [
                        'serviceRequest' => $request,
                        'buttonIcon' => 'icon-checkmark',
                        'buttonLabel' => trans('Admin::app.service-requests.view.in-progress'),
                        'confirmMessage' => trans('Admin::app.service-requests.view.confirm-status-update', ['status' => trans('Admin::app.service-requests.view.in-progress')]),
                        'status' => 'in_progress'
                    ])

                    <button
                        type="button"
                        class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="$refs.rejectModal.toggle()"
                    >
                        <span class="icon-cancel-1 text-2xl"></span>
                        @lang('Admin::app.service-requests.view.reject')
                    </button>

                @elseif ($request->status === 'in_progress')
                    <!-- In Progress: Show "Complete" and "Reject" buttons -->
                    @include('admin::service-requests.update-status', [
                        'serviceRequest' => $request,
                        'buttonIcon' => 'icon-checkmark',
                        'buttonLabel' => trans('Admin::app.service-requests.view.complete'),
                        'confirmMessage' => trans('Admin::app.service-requests.view.confirm-status-update', ['status' => trans('Admin::app.service-requests.view.completed')]),
                        'status' => 'completed'
                    ])

                    <button
                        type="button"
                        class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                        @click="$refs.rejectModal.toggle()"
                    >
                        <span class="icon-cancel-1 text-2xl"></span>
                        @lang('Admin::app.service-requests.view.reject')
                    </button>

                @elseif ($request->status === 'completed')
                    <!-- Completed: Show "Print" and "Cancel" buttons -->
                    @if (
                        $request->service 
                        && $request->service->documentTemplate 
                        && $request->service->documentTemplate->is_active
                    )
                        <a
                            href="{{ route('admin.service-requests.print', $request->id) }}"
                            class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                        >
                            <span class="icon-printer text-2xl"></span>
                            @lang('Admin::app.service-requests.view.print')
                        </a>
                    @endif

                    @if (bouncer()->hasPermission('service-requests.cancel'))
                        <form
                            method="POST"
                            ref="cancelRequestForm"
                            action="{{ route('admin.service-requests.cancel', $request->id) }}"
                        >
                            @csrf
                        </form>

                        <button
                            type="button"
                            class="inline-flex w-full max-w-max cursor-pointer items-center justify-between gap-x-2 px-1 py-1.5 text-center font-semibold text-gray-600 transition-all hover:rounded-md hover:bg-gray-200 dark:text-gray-300 dark:hover:bg-gray-800"
                            @click="$emitter.emit('open-confirm-modal', {
                                message: '@lang('Admin::app.service-requests.view.cancel-msg')',
                                agree: () => {
                                    $refs.cancelRequestForm.submit()
                                }
                            })"
                        >
                            <span class="icon-cancel text-2xl"></span>
                            @lang('Admin::app.service-requests.view.cancel')
                        </button>
                    @endif
                @endif
            </div>
        </div>
    @endif

    <!-- Request details -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <!-- Left Component -->
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <!-- Document Content / Form Data -->
            <div class="box-shadow rounded bg-white dark:bg-gray-900">
                @if ($template && $template->is_active && $documentContent)
                    <x-admin::tabs.custom-tabs position="right">

                        <x-admin::tabs.item 
                            :title="trans('Admin::app.service-requests.view.form-data') . ' (' . count($request->formData) . ')'"
                            :isSelected="true"
                            class="!p-4"
                        >
                            <div class="grid">
                                @foreach ($request->formData as $formData)
                                    <div class="flex flex-col gap-2.5 px-4 py-6">
                                        <div class="flex flex-col gap-2.5">
                                            <div class="flex gap-2.5">
                                                <div class="grid place-content-start gap-1.5 flex-1">
                                                    <p class="break-all text-base font-semibold text-gray-800 dark:text-white">
                                                        {{ $formData->group_name }}
                                                    </p>
                                                </div>
                                            </div>

                                            @if ($formData->fields_data && is_array($formData->fields_data) && count($formData->fields_data) > 0)
                                                <div class="mt-4 grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));">
                                                    @foreach ($formData->fields_data as $fieldCode => $fieldValue)
                                                        @php
                                                            $fieldCodeLower = strtolower($fieldCode);
                                                            $isNationalIdField = in_array($fieldCodeLower, ['national_id', 'citizen_id', 'nationalid', 'citizenid', 'id_number', 'idnumber', 'national_number', 'identity_number']);
                                                            $nationalId = $isNationalIdField && !empty($fieldValue) ? preg_replace('/[^0-9]/', '', (string) $fieldValue) : null;
                                                            $citizenId = $nationalId && isset($nationalIdToCitizenMap[$nationalId]) ? $nationalIdToCitizenMap[$nationalId] : null;
                                                        @endphp
                                                        <div class="flex items-start gap-2 pl-4 min-w-0 w-full">
                                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap flex-shrink-0">
                                                                {{ $fieldLabelsMap[$fieldCode] ?? $fieldLabelsMap[$formData->group_code . '.' . $fieldCode] ?? $fieldCode }}:
                                                            </p>
                                                            <p class="text-base text-gray-800 dark:text-white break-words min-w-0 flex-1">
                                                                @if ($citizenId)
                                                                    <a 
                                                                        href="{{ route('admin.citizens.view', $citizenId) }}"
                                                                        class="text-blue-600 hover:underline dark:text-blue-400"
                                                                    >
                                                                        {{ $fieldValue }}
                                                                    </a>
                                                                @elseif (is_array($fieldValue))
                                                                    {{ json_encode($fieldValue, JSON_UNESCAPED_UNICODE) }}
                                                                @elseif (is_bool($fieldValue))
                                                                    {{ $fieldValue ? trans('Admin::app.service-requests.view.yes') : trans('Admin::app.service-requests.view.no') }}
                                                                @else
                                                                    {{ $fieldValue }}
                                                                @endif
                                                            </p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </x-admin::tabs.item>
                        <x-admin::tabs.item
                                :title="trans('Admin::app.service-requests.view.document-content')"
                                class="!p-4"
                                :isSelected="false"

                        >
                            <div class="document-content-view text-base leading-7 text-gray-800 dark:text-gray-200 [&_p]:mb-3 [&_h1]:text-2xl [&_h1]:font-bold [&_h1]:mb-4 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:mb-3 [&_h3]:text-lg [&_h3]:font-bold [&_h3]:mb-2 [&_ul]:list-disc [&_ul]:ml-6 [&_ul]:mb-3 [&_ol]:list-decimal [&_ol]:ml-6 [&_ol]:mb-3 [&_li]:mb-1 [&_strong]:font-bold [&_em]:italic [&_u]:underline [&_table]:w-full [&_table]:border-collapse [&_table]:mb-4 [&_th]:border [&_th]:border-gray-300 [&_th]:px-4 [&_th]:py-2 [&_th]:bg-gray-100 [&_td]:border [&_td]:border-gray-300 [&_td]:px-4 [&_td]:py-2">
                                {!! $documentContent !!}
                            </div>
                        </x-admin::tabs.item>

                    </x-admin::tabs>
                @else
                    <div class="flex gap-2 p-4 bg-white dark:bg-gray-900" style="justify-content: right;">
                        <button
                            type="button"
                            class="cursor-pointer px-4 py-2 text-sm font-semibold rounded-md transition-all border focus:opacity-90 primary-button"
                        >
                            @lang('Admin::app.service-requests.view.form-data') ({{ count($request->formData) }})
                        </button>
                    </div>

                    <div class="p-4">
                        <div class="grid">
                            @foreach ($request->formData as $formData)
                                <div class="flex flex-col gap-2.5 px-4 py-6">
                                    <div class="flex flex-col gap-2.5">
                                        <div class="flex gap-2.5">
                                            <div class="grid place-content-start gap-1.5 flex-1">
                                                <p class="break-all text-base font-semibold text-gray-800 dark:text-white">
                                                    {{ $formData->group_name }}
                                                </p>
                                            </div>
                                        </div>

                                        @if ($formData->fields_data && is_array($formData->fields_data) && count($formData->fields_data) > 0)
                                            <div class="mt-4 grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr));">
                                                @foreach ($formData->fields_data as $fieldCode => $fieldValue)
                                                    @php
                                                        $fieldCodeLower = strtolower($fieldCode);
                                                        $isNationalIdField = in_array($fieldCodeLower, ['national_id', 'citizen_id', 'nationalid', 'citizenid', 'id_number', 'idnumber', 'national_number', 'identity_number']);
                                                        $nationalId = $isNationalIdField && !empty($fieldValue) ? preg_replace('/[^0-9]/', '', (string) $fieldValue) : null;
                                                        $citizenId = $nationalId && isset($nationalIdToCitizenMap[$nationalId]) ? $nationalIdToCitizenMap[$nationalId] : null;
                                                    @endphp
                                                    <div class="flex items-start gap-2 pl-4 min-w-0 w-full">
                                                        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap flex-shrink-0">
                                                            {{ $fieldLabelsMap[$fieldCode] ?? $fieldLabelsMap[$formData->group_code . '.' . $fieldCode] ?? $fieldCode }}:
                                                        </p>
                                                        <p class="text-base text-gray-800 dark:text-white break-words min-w-0 flex-1">
                                                            @if ($citizenId)
                                                                <a 
                                                                    href="{{ route('admin.citizens.view', $citizenId) }}"
                                                                    class="text-blue-600 hover:underline dark:text-blue-400"
                                                                >
                                                                    {{ $fieldValue }}
                                                                </a>
                                                            @elseif (is_array($fieldValue))
                                                                {{ json_encode($fieldValue, JSON_UNESCAPED_UNICODE) }}
                                                            @elseif (is_bool($fieldValue))
                                                                {{ $fieldValue ? trans('Admin::app.service-requests.view.yes') : trans('Admin::app.service-requests.view.no') }}
                                                            @else
                                                                {{ $fieldValue }}
                                                            @endif
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Admin Notes -->
            <div class="box-shadow rounded bg-white dark:bg-gray-900">
                <p class="p-4 pb-0 text-base font-semibold text-gray-800 dark:text-white">
                    @lang('Admin::app.service-requests.view.admin-notes')
                </p>

                <x-admin::form action="{{ route('admin.service-requests.add-notes', $request->id) }}">
                    <div class="p-4">
                        <div class="mb-2.5">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.control
                                    type="textarea"
                                    id="admin_notes"
                                    name="admin_notes"
                                    rules="required"
                                    :label="trans('Admin::app.service-requests.view.admin-notes')"
                                    :placeholder="trans('Admin::app.service-requests.view.write-your-notes')"
                                    rows="3"
                                />

                                <x-admin::form.control-group.error control-name="admin_notes" />
                            </x-admin::form.control-group>
                        </div>

                        <div class="flex items-center justify-between">
                            <label
                                class="flex w-max cursor-pointer select-none items-center gap-1 p-1.5"
                                for="citizen_notified"
                            >
                                <input
                                    type="checkbox"
                                    name="citizen_notified"
                                    id="citizen_notified"
                                    value="1"
                                    class="peer hidden"
                                >

                                <span
                                    class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                    role="button"
                                    tabindex="0"
                                >
                                </span>

                                <p class="flex cursor-pointer items-center gap-x-1 font-semibold text-gray-600 hover:text-gray-800 dark:text-gray-300 dark:hover:text-gray-100">
                                    @lang('Admin::app.service-requests.view.notify-citizen')
                                </p>
                            </label>

                            <button
                                type="submit"
                                class="secondary-button"
                                aria-label="{{ trans('Admin::app.service-requests.view.submit-notes') }}"
                            >
                                @lang('Admin::app.service-requests.view.submit-notes')
                            </button>
                        </div>
                    </div>
                </x-admin::form>

                <span class="block w-full border-b dark:border-gray-800"></span>

                <!-- Admin Notes List -->
                @foreach ($request->adminNotes()->orderBy('id', 'desc')->get() as $adminNote)
                    <div class="grid gap-1.5 p-4">
                        <p class="break-all text-base leading-6 text-gray-800 dark:text-white">
                            {{ $adminNote->note }}
                        </p>

                        <!-- Notes List Title and Time -->
                        <p class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                            @if ($adminNote->citizen_notified)
                                <span class="icon-done h-fit rounded-full bg-blue-100 text-2xl text-blue-600"></span>

                                @if ($adminNote->admin)
                                    @lang('Admin::app.service-requests.view.citizen-notified', [
                                        'admin' => $adminNote->admin->name,
                                        'date' => core()->formatDate($adminNote->created_at, 'Y-m-d H:i:s a')
                                    ])
                                @else
                                    @lang('Admin::app.service-requests.view.citizen-notified-no-admin', [
                                        'date' => core()->formatDate($adminNote->created_at, 'Y-m-d H:i:s a')
                                    ])
                                @endif
                            @else
                                <span class="icon-cancel-1 h-fit rounded-full bg-red-100 text-2xl text-red-600"></span>

                                @if ($adminNote->admin)
                                    @lang('Admin::app.service-requests.view.citizen-not-notified', [
                                        'admin' => $adminNote->admin->name,
                                        'date' => core()->formatDate($adminNote->created_at, 'Y-m-d H:i:s a')
                                    ])
                                @else
                                    @lang('Admin::app.service-requests.view.citizen-not-notified-no-admin', [
                                        'date' => core()->formatDate($adminNote->created_at, 'Y-m-d H:i:s a')
                                    ])
                                @endif
                            @endif
                        </p>
                    </div>

                    <span class="block w-full border-b dark:border-gray-800"></span>
                @endforeach
            </div>
        </div>

        <!-- Right Component -->
        <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">
            <!-- Citizen Information -->
            <x-admin::accordion>
                <x-slot:header>
                    <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('Admin::app.service-requests.view.citizen')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="flex flex-col gap-1.5">
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ trim($request->citizen_first_name . ' ' . $request->citizen_middle_name . ' ' . $request->citizen_last_name) }}
                        </p>

                        <p class="text-gray-600 dark:text-gray-300">
                            @lang('Admin::app.service-requests.view.national-id'): {{ $request->citizen_national_id }}
                        </p>

                        <p class="text-gray-600 dark:text-gray-300">
                            @lang('Admin::app.service-requests.view.citizen-type'): {{ $request->citizen_type_name }}
                        </p>

                        <p class="text-gray-600 dark:text-gray-300">
                            @lang('Admin::app.service-requests.view.locale'): {{ $localeName ?? $request->locale }}
                        </p>

                        @if ($request->citizen)
                            <a
                                href="{{ route('admin.citizens.view', $request->citizen_id) }}"
                                class="text-blue-600 hover:underline"
                            >
                                @lang('Admin::app.service-requests.view.view-citizen')
                            </a>
                        @endif
                    </div>
                </x-slot>
            </x-admin::accordion>

            <!-- Service Information -->
            <x-admin::accordion>
                <x-slot:header>
                    <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('Admin::app.service-requests.view.service')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="flex flex-col gap-1.5">
                        <p class="font-semibold text-gray-800 dark:text-white">
                            {{ $request->service->name ?? '-' }}
                        </p>

                        @if ($request->service)
                            <a
                                href="{{ route('admin.services.edit', $request->service_id) }}"
                                class="text-blue-600 hover:underline"
                            >
                                @lang('Admin::app.service-requests.view.view-service')
                            </a>
                        @endif
                    </div>
                </x-slot>
            </x-admin::accordion>

            <!-- Request Information -->
            <x-admin::accordion>
                <x-slot:header>
                    <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                        @lang('Admin::app.service-requests.view.request-information')
                    </p>
                </x-slot>

                <x-slot:content>
                    <div class="flex w-full justify-start gap-5">
                        <div class="flex flex-col gap-y-1.5">
                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.service-requests.view.request-date')
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.service-requests.view.request-status')
                            </p>

                            @if ($request->completed_at)
                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('Admin::app.service-requests.view.completed-at')
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-col gap-y-1.5">
                            <!-- Request Date -->
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ core()->formatDate($request->created_at) }}
                            </p>

                            <!-- Request Status -->
                            <p class="text-gray-600 dark:text-gray-300">
                                @lang("Admin::app.service-requests.view.$request->status")
                            </p>

                            <!-- Completed At -->
                            @if ($request->completed_at)
                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ core()->formatDate($request->completed_at) }}
                                </p>
                            @endif
                        </div>
                    </div>
                </x-slot>
            </x-admin::accordion>

            <!-- Rejection Reason Display -->
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

            <!-- Beneficiaries -->
            @if ($request->beneficiaries->count() > 0)
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                            @lang('Admin::app.service-requests.view.beneficiaries') ({{ $request->beneficiaries->count() }})
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <div class="flex flex-col gap-2.5">
                            @foreach ($request->beneficiaries as $beneficiary)
                                <div class="flex flex-col gap-1.5 border-b  pb-2.5 dark:border-gray-800 last:border-b-0">
                                    <p class="font-semibold text-gray-800 dark:text-white">
                                        {{ trim($beneficiary->first_name . ' ' . $beneficiary->middle_name . ' ' . $beneficiary->last_name) }}
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.service-requests.view.national-id'): {{ $beneficiary->national_id }}
                                    </p>

                                    @if ($beneficiary->pivot->group_code)
                                        <p class="text-gray-600 dark:text-gray-300">
                                            @lang('Admin::app.service-requests.view.group'): {{ $beneficiary->pivot->group_code }}
                                        </p>
                                    @endif

                                    <a
                                        href="{{ route('admin.citizens.view', $beneficiary->id) }}"
                                        class="text-blue-600 hover:underline text-sm"
                                    >
                                        @lang('Admin::app.service-requests.view.view-citizen')
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </x-slot>
                </x-admin::accordion>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
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
                        <button
                            type="button"
                            @click="$refs.rejectModal.close()"
                            class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                        >
                            @lang('admin::app.components.modal.confirm.disagree-btn')
                        </button>

                        <button
                            type="submit"
                            class="primary-button"
                        >
                            @lang('Admin::app.service-requests.view.reject')
                        </button>
                    </div>
                </div>
            </x-admin::form>
        </x-slot>
    </x-admin::modal>

    <!-- Status Update Confirmation Modal -->
    <div
        x-data="{
            statusUpdateStatus: '',
            statusUpdateLabel: '',
            openStatusUpdateModal(status, statusLabel) {
                this.statusUpdateStatus = status;
                this.statusUpdateLabel = statusLabel;
                $refs.statusInput.value = status;
                $refs.statusUpdateMessage.innerHTML = '@lang('Admin::app.service-requests.view.confirm-status-update', ['status' => ''])'.replace(':status', statusLabel);
                $refs.statusUpdateModal.toggle();
            }
        }"
    >
        <x-admin::modal ref="statusUpdateModal">
            <x-slot:header>
                <p class="text-lg font-bold text-gray-800 dark:text-white">
                    @lang('Admin::app.service-requests.view.update-status')
                </p>
            </x-slot>

            <x-slot:content>
                <x-admin::form action="{{ route('admin.service-requests.update-status', $request->id) }}" ref="statusUpdateForm">
                    @csrf
                    <input type="hidden" name="status" ref="statusInput" value="">

                    <div class="flex flex-col gap-4">
                        <p class="text-base text-gray-600 dark:text-gray-300" ref="statusUpdateMessage">
                        </p>
                    </div>
                </x-admin::form>
            </x-slot>

            <x-slot:footer>
                <div class="flex items-center gap-x-2.5">
                    <button
                        type="button"
                        @click="$refs.statusUpdateModal.close()"
                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                    >
                        @lang('admin::app.components.modal.confirm.disagree-btn')
                    </button>

                    <button
                        type="button"
                        @click="$refs.statusUpdateForm.submit()"
                        class="primary-button"
                    >
                        @lang('Admin::app.service-requests.view.update-status')
                    </button>
                </div>
            </x-slot>
        </x-admin::modal>
    </div>
</x-admin::layouts>

