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

