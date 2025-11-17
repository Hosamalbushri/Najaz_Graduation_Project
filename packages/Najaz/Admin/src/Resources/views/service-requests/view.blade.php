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

    <div class="mt-5 flex-wrap items-center justify-between gap-x-1 gap-y-2">
        <div class="flex gap-1.5">
            @if (
                in_array($request->status, ['pending', 'in_progress'])
                && bouncer()->hasPermission('service-requests.cancel')
            )
                <form
                    method="POST"
                    ref="cancelRequestForm"
                    action="{{ route('admin.service-requests.cancel', $request->id) }}"
                >
                    @csrf
                </form>

                <div
                    class="transparent-button px-1 py-1.5 hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                    @click="$emitter.emit('open-confirm-modal', {
                        message: '@lang('Admin::app.service-requests.view.cancel-msg')',
                        agree: () => {
                            this.$refs['cancelRequestForm'].submit()
                        }
                    })"
                >
                    <span
                        class="icon-cancel text-2xl"
                        role="presentation"
                        tabindex="0"
                    >
                    </span>

                    <a href="javascript:void(0);">
                        @lang('Admin::app.service-requests.view.cancel')
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Request details -->
    <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
        <!-- Left Component -->
        <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
            <!-- Form Data -->
            <div class="box-shadow rounded bg-white dark:bg-gray-900">
                <div class="flex justify-between p-4">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.service-requests.view.form-data') ({{ count($request->formData) }})
                    </p>
                </div>

                <!-- Form Data Groups -->
                <div class="grid">
                    @foreach ($request->formData as $formData)
                        <div class="flex flex-col gap-2.5 border-b border-slate-300 px-4 py-6 dark:border-gray-800">
                            <div class="flex gap-2.5">
                                <div class="grid place-content-start gap-1.5 flex-1">
                                    <p class="break-all text-base font-semibold text-gray-800 dark:text-white">
                                        {{ $formData->group_name }}
                                    </p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.service-requests.view.group-code'): {{ $formData->group_code }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Notes -->
            @if ($request->notes)
                <div class="box-shadow rounded bg-white dark:bg-gray-900">
                    <p class="p-4 pb-0 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.service-requests.view.notes')
                    </p>

                    <div class="p-4">
                        <p class="break-all text-base leading-6 text-gray-800 dark:text-white">
                            {{ $request->notes }}
                        </p>
                    </div>
                </div>
            @endif

            <!-- Admin Notes Form -->
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
                                    :value="$request->admin_notes"
                                />

                                <x-admin::form.control-group.error control-name="admin_notes" />
                            </x-admin::form.control-group>
                        </div>

                        <button
                            type="submit"
                            class="secondary-button"
                            aria-label="{{ trans('Admin::app.service-requests.view.submit-notes') }}"
                        >
                            @lang('Admin::app.service-requests.view.submit-notes')
                        </button>
                    </div>
                </x-admin::form>
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
                            @lang('Admin::app.service-requests.view.locale'): {{ $request->locale }}
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

                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.service-requests.view.submitted-at')
                            </p>

                            @if ($request->completed_at)
                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('Admin::app.service-requests.view.completed-at')
                                </p>
                            @endif

                            <p class="text-gray-600 dark:text-gray-300">
                                @lang('Admin::app.service-requests.view.assigned-to')
                            </p>
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

                            <!-- Submitted At -->
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ $request->submitted_at ? core()->formatDate($request->submitted_at) : '-' }}
                            </p>

                            <!-- Completed At -->
                            @if ($request->completed_at)
                                <p class="text-gray-600 dark:text-gray-300">
                                    {{ core()->formatDate($request->completed_at) }}
                                </p>
                            @endif

                            <!-- Assigned To -->
                            <p class="text-gray-600 dark:text-gray-300">
                                {{ $request->assignedAdmin ? $request->assignedAdmin->name : trans('Admin::app.service-requests.view.unassigned') }}
                            </p>
                        </div>
                    </div>
                </x-slot>
            </x-admin::accordion>

            <!-- Status Update -->
            @if (bouncer()->hasPermission('service-requests.update'))
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-600 dark:text-gray-300">
                            @lang('Admin::app.service-requests.view.update-status')
                        </p>
                    </x-slot>

                    <x-slot:content>
                        <x-admin::form action="{{ route('admin.service-requests.update-status', $request->id) }}">
                            <div class="flex flex-col gap-2.5">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.control
                                        type="select"
                                        id="status"
                                        name="status"
                                        rules="required"
                                        :label="trans('Admin::app.service-requests.view.status')"
                                        :value="$request->status"
                                    >
                                        <option value="pending">@lang('Admin::app.service-requests.view.pending')</option>
                                        <option value="in_progress">@lang('Admin::app.service-requests.view.in-progress')</option>
                                        <option value="completed">@lang('Admin::app.service-requests.view.completed')</option>
                                        <option value="rejected">@lang('Admin::app.service-requests.view.rejected')</option>
                                        <option value="cancelled">@lang('Admin::app.service-requests.view.cancelled')</option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="status" />
                                </x-admin::form.control-group>

                                <button
                                    type="submit"
                                    class="secondary-button"
                                >
                                    @lang('Admin::app.service-requests.view.update')
                                </button>
                            </div>
                        </x-admin::form>
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
                                <div class="flex flex-col gap-1.5 border-b border-slate-300 pb-2.5 dark:border-gray-800 last:border-b-0">
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
</x-admin::layouts>

