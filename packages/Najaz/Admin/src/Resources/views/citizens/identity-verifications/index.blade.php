<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.citizens.identity-verifications.index.title')
    </x-slot>

    <div class="flex items-center justify-between">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.citizens.identity-verifications.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <!-- Export Modal -->
            <x-admin::datagrid.export src="{{ route('admin.identity-verifications.index') }}" />
        </div>
    </div>

    {!! view_render_event('bagisto.admin.identity-verifications.list.before') !!}

    <x-admin::datagrid
        :src="route('admin.identity-verifications.index')"
        ref="identityVerificationDatagrid"
    >
{{--        <template #header="{--}}
{{--            isLoading,--}}
{{--            available,--}}
{{--            applied,--}}
{{--            selectAll,--}}
{{--            sort,--}}
{{--            performAction--}}
{{--        }">--}}
{{--            <template v-if="isLoading">--}}
{{--                <x-admin::shimmer.datagrid.table.head />--}}
{{--            </template>--}}

{{--            <template v-else>--}}
{{--                <div class="row grid grid-cols-7 items-center border-b px-4 py-2.5 dark:border-gray-800">--}}
{{--                    <div class="flex select-none items-center gap-2.5">--}}
{{--                        <span class="icon-checkbox text-2xl cursor-pointer" @click="selectAll"></span>--}}
{{--                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">--}}
{{--                            @lang('admin::app.citizens.components.datagrid.filters.select')--}}
{{--                        </span>--}}
{{--                    </div>--}}

{{--                    <div class="flex select-none items-center gap-2.5">--}}
{{--                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">--}}
{{--                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.citizen-name')--}}
{{--                        </span>--}}
{{--                        <span class="icon-sort text-2xl cursor-pointer" @click="sort('citizen_name')"></span>--}}
{{--                    </div>--}}

{{--                    <div class="flex select-none items-center gap-2.5">--}}
{{--                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">--}}
{{--                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.national-id')--}}
{{--                        </span>--}}
{{--                    </div>--}}

{{--                    <div class="flex select-none items-center gap-2.5">--}}
{{--                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">--}}
{{--                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.status')--}}
{{--                        </span>--}}
{{--                    </div>--}}

{{--                    <div class="flex select-none items-center gap-2.5">--}}
{{--                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">--}}
{{--                            @lang('Admin::app.identity-verifications.index.datagrid.documents-count')--}}
{{--                        </span>--}}
{{--                    </div>--}}

{{--                    <div class="flex select-none items-center gap-2.5">--}}
{{--                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">--}}
{{--                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.reviewed-by')--}}
{{--                        </span>--}}
{{--                    </div>--}}

{{--                    <div class="flex select-none items-center gap-2.5">--}}
{{--                        <span class="text-sm font-medium text-gray-600 dark:text-gray-300">--}}
{{--                            @lang('Admin::app.citizens.identity-verifications.index.datagrid.created-at')--}}
{{--                        </span>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </template>--}}
{{--        </template>--}}

{{--        <template #body="{--}}
{{--            isLoading,--}}
{{--            available,--}}
{{--            applied,--}}
{{--            selectAll,--}}
{{--            sort,--}}
{{--            performAction--}}
{{--        }">--}}
{{--            <template v-if="isLoading">--}}
{{--                <x-admin::shimmer.datagrid.table.body />--}}
{{--            </template>--}}

{{--            <template v-else>--}}
{{--                <div--}}
{{--                    v-for="record in available.records"--}}
{{--                    class="row grid grid-cols-7 items-center border-b px-4 py-2.5 text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:bg-gray-950"--}}
{{--                >--}}
{{--                    <div class="flex items-center gap-2.5">--}}
{{--                        <input--}}
{{--                            type="checkbox"--}}
{{--                            :name="`mass_action_select_record_${record.id}`"--}}
{{--                            :id="`mass_action_select_record_${record.id}`"--}}
{{--                            :value="record.id"--}}
{{--                            class="peer hidden"--}}
{{--                            v-model="applied.massActions.indices"--}}
{{--                            @change="setCurrentSelectionMode"--}}
{{--                        >--}}

{{--                        <label--}}
{{--                            class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"--}}
{{--                            :for="`mass_action_select_record_${record.id}`"--}}
{{--                        >--}}
{{--                        </label>--}}

{{--                        <p class="text-gray-600 dark:text-gray-300">--}}
{{--                            @{{ record.id }}--}}
{{--                        </p>--}}
{{--                    </div>--}}

{{--                    <div class="flex flex-col gap-1.5">--}}
{{--                        <p class="text-gray-600 font-semibold text-gray-800 dark:text-white">--}}
{{--                            @{{ record.citizen_name ?? 'N/A' }}--}}
{{--                        </p>--}}
{{--                    </div>--}}

{{--                    <div class="flex items-center">--}}
{{--                        <p class="text-gray-600 dark:text-gray-300">--}}
{{--                            @{{ record.national_id ?? 'N/A' }}--}}
{{--                        </p>--}}
{{--                    </div>--}}

{{--                    <div class="flex items-center">--}}
{{--                        <p v-html="record.status"></p>--}}
{{--                    </div>--}}

{{--                    <div class="flex items-center">--}}
{{--                        <p v-html="record.documents"></p>--}}
{{--                    </div>--}}

{{--                    <div class="flex items-center">--}}
{{--                        <p class="text-gray-600 dark:text-gray-300">--}}
{{--                            @{{ record.reviewer_name ?? '-' }}--}}
{{--                        </p>--}}
{{--                    </div>--}}

{{--                    <div class="flex items-center justify-between gap-x-4">--}}
{{--                        <p class="text-gray-600 dark:text-gray-300">--}}
{{--                            @{{ record.created_at ?? 'N/A' }}--}}
{{--                        </p>--}}

{{--                        <div class="flex items-center">--}}
{{--                            <a--}}
{{--                                class="icon-sort-right rtl:icon-sort-left cursor-pointer p-1.5 text-2xl hover:rounded-md hover:bg-gray-200 dark:hover:bg-gray-800 ltr:ml-1 rtl:mr-1"--}}
{{--                                :href="`{{ route('admin.identity-verifications.view', '') }}/${record.id}`"--}}
{{--                            >--}}
{{--                            </a>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </template>--}}
{{--        </template>--}}
    </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.identity-verifications.list.after') !!}
</x-admin::layouts>

