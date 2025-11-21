<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
    <div class="flex justify-between">
        <!-- Total Beneficiary Service Requests Count -->
        <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
            @lang('Admin::app.citizens.citizens.view.beneficiary-service-requests.count', ['count' => $citizen->serviceRequestsAsBeneficiary ? $citizen->serviceRequestsAsBeneficiary->count() : 0])
        </p>
    </div>

    {!! view_render_event('bagisto.admin.citizens.citizens.view.beneficiary-service-requests.before', ['citizen' => $citizen]) !!}

    <x-admin::datagrid
        :src="route('admin.citizens.view', [
            'id'   => $citizen->id,
            'type' => 'beneficiary-service-requests'
        ])"
    >
        <!-- Datagrid Header -->
        <template #header="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.head :isMultiRow="true" />
            </template>

            <template v-else>
                <div class="row grid grid-cols-4 grid-rows-1 items-center border-b border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300">
                    <div
                        class="flex select-none items-center gap-2.5"
                        v-for="(columnGroup, index) in [['increment_id', 'created_at', 'status'], ['service_name', 'completed_at'], ['request_citizen_full_name', 'request_citizen_national_id', 'group_code']]"
                    >
                        <p class="text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                        class="after:content-['/'] last:after:content-['']"
                                        :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(columnTemp => columnTemp.index === column)?.sortable,
                                        }"
                                        @click="
                                            available.columns.find(columnTemp => columnTemp.index === column)?.sortable ? sort(available.columns.find(columnTemp => columnTemp.index === column)): {}
                                        "
                                    >
                                        @{{ available.columns.find(columnTemp => columnTemp.index === column)?.label }}
                                    </span>
                                </template>
                            </span>

                            <i
                                class="align-text-bottom text-base text-gray-800 dark:text-white ltr:ml-1.5 rtl:mr-1.5"
                                :class="[applied.sort.order === 'asc' ? 'icon-down-stat': 'icon-up-stat']"
                                v-if="columnGroup.includes(applied.sort.column)"
                            ></i>
                        </p>
                    </div>
                </div>
            </template>
        </template>

        <template #body="{
            isLoading,
            available,
            applied,
            selectAll,
            sort,
            performAction
        }">
            <template v-if="isLoading">
                <x-admin::shimmer.datagrid.table.body :isMultiRow="true" />
            </template>

            <template v-else>
                <div
                    v-if="available.meta.total"
                    class="row grid grid-cols-4 border-b px-4 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950"
                    v-for="record in available.records"
                >
                    <!-- Request ID, Created, Status Section -->
                    <div class="">
                        <div class="flex gap-2.5">
                            <div class="flex flex-col gap-1.5">
                                <p
                                    class="text-base font-semibold text-gray-800 dark:text-white"
                                >
                                    @{{ "@lang('Admin::app.service-requests.index.datagrid.order-id')".replace(':id', record.increment_id) }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ record.created_at }}
                                </p>

                                <p v-html="record.status"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Service Name, Completed At -->
                    <div class="">
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @{{ record.service_name ?? 'N/A' }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300" v-if="record.completed_at">
                                @lang('Admin::app.service-requests.index.datagrid.completed-at'): @{{ record.completed_at }}
                            </p>
                        </div>
                    </div>

                    <!-- Request Citizen Name, National ID, Group Code Section -->
                    <div class="">
                        <div class="flex flex-col gap-1.5">
                            <p class="text-base text-gray-800 dark:text-white">
                                @{{ record.request_citizen_full_name ?? 'N/A' }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                @{{ record.request_citizen_national_id ?? 'N/A' }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300" v-if="record.group_code">
                                @lang('Admin::app.service-requests.view.group'): @{{ record.group_code }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-x-2">
                        <a :href="`{{ route('admin.service-requests.view', '') }}/${record.id}`">
                            <span class="icon-sort-right rtl:icon-sort-left cursor-pointer p-1.5 text-2xl hover:rounded-md hover:bg-gray-200 dark:hover:bg-gray-800 ltr:ml-1 rtl:mr-1"></span>
                        </a>
                    </div>
                </div>

                <div v-else class="table-responsive grid w-full">
                    <div class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10">
                        <!-- Placeholder Image -->
                        <img
                            src="{{ bagisto_asset('images/empty-placeholders/orders.svg') }}"
                            class="h-20 w-20 dark:mix-blend-exclusion dark:invert"
                        />

                        <div class="flex flex-col items-center">
                            <p class="text-base font-semibold text-gray-400">
                                @lang('Admin::app.citizens.citizens.view.beneficiary-service-requests.empty')
                            </p>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.citizens.citizens.view.beneficiary-service-requests.after', ['citizen' => $citizen]) !!}
</div>

