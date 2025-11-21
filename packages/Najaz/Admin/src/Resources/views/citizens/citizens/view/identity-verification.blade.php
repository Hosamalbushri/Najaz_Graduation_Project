<div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
    <div class="flex justify-between">
        <p class="text-base font-semibold leading-none text-gray-800 dark:text-white">
            @lang('Admin::app.citizens.citizens.view.identity-verification.title')
        </p>
    </div>

    {!! view_render_event('bagisto.admin.citizens.citizens.view.identity-verification.before', ['citizen' => $citizen]) !!}

    <x-admin::datagrid
        :src="route('admin.citizens.view', [
            'id'   => $citizen->id,
            'type' => 'identity-verification'
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
                            v-for="(columnGroup, index) in [['status', 'created_at'], ['reviewed_at', 'reviewer_name'], ['notes']]"
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
                        <!-- Status, Created At Section -->
                        <div class="">
                            <div class="flex gap-2.5">
                                <div class="flex flex-col gap-1.5">
                                    <p v-html="record.status"></p>

                                    <p class="text-gray-600 dark:text-gray-300">
                                        @{{ record.created_at }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Reviewed At, Reviewed By -->
                        <div class="">
                            <div class="flex flex-col gap-1.5">
                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('Admin::app.citizens.citizens.view.identity-verification.reviewed-at'): @{{ record.reviewed_at }}
                                </p>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @lang('Admin::app.citizens.citizens.view.identity-verification.reviewed-by'): @{{ record.reviewer_name }}
                                </p>
                            </div>
                        </div>

                        <!-- Notes Section -->
                        <div class="">
                            <div class="flex flex-col gap-1.5">
                                <p class="text-gray-600 dark:text-gray-300 break-all" v-if="record.notes && record.notes !== '-'">
                                    @lang('Admin::app.citizens.citizens.view.identity-verification.notes'): @{{ record.notes }}
                                </p>
                                <p v-else class="text-gray-600 dark:text-gray-300">
                                    -
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-x-2">
                            <a :href="`{{ route('admin.identity-verifications.view', '') }}/${record.id}`">
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
                                    @lang('Admin::app.citizens.citizens.view.identity-verification.empty')
                                </p>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
        </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.citizens.citizens.view.identity-verification.after', ['citizen' => $citizen]) !!}
</div>


