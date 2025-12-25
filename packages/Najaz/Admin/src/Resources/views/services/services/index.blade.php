<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.services.index.title')
    </x-slot>

    @pushOnce('styles')
        <style>
            /* شبكة عرض الخدمات: 3 أعمدة على الشاشات المتوسطة فما فوق */
            @media (min-width: 768px) {
                .services-dg-grid {
                    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) 220px;
                }
            }
        </style>
    @endPushOnce

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.services.services.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <a
                    href="{{ route('admin.services.document-templates.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
            >
                @lang('Admin::app.services.document-templates.index.title')
            </a>

            @if (bouncer()->hasPermission('services.services.create'))
                <a
                        href="{{ route('admin.services.create') }}"
                        class="primary-button"
                >
                    @lang('Admin::app.services.services.index.create-btn')
                </a>
            @endif
        </div>
    </div>

    {!! view_render_event('bagisto.admin.services.list.before') !!}

    @php
        $hasPermission =
            bouncer()->hasPermission('services.services.edit')
            || bouncer()->hasPermission('services.services.delete');
    @endphp

    <x-admin::datagrid
            :src="route('admin.services.index')"
            :isMultiRow="true"
            ref="serviceDatagrid"
    >
        {{-- Header --}}
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
                <div class="row grid services-dg-grid gap-2 items-center border-b px-4 py-2.5 dark:border-gray-800">
                    <div
                            class="flex min-w-0 select-none items-center gap-2.5"
                            v-for="(columnGroup, index) in [
                            ['name', 'category_name', 'description'],
                            ['service_id', 'sort_order', 'status'],
                        ]"
                    >
                        @if ($hasPermission)
                            <label
                                    class="flex w-max cursor-pointer select-none items-center gap-1"
                                    for="mass_action_select_all_records"
                                    v-if="! index"
                            >
                                <input
                                        type="checkbox"
                                        name="mass_action_select_all_records"
                                        id="mass_action_select_all_records"
                                        class="peer hidden"
                                        :checked="['all', 'partial'].includes(applied.massActions.meta.mode)"
                                        @change="selectAll"
                                >

                                <span
                                        class="icon-uncheckbox cursor-pointer rounded-md text-2xl"
                                        :class="[
                                        applied.massActions.meta.mode === 'all' ? 'peer-checked:icon-checked peer-checked:text-blue-600' : (
                                            applied.massActions.meta.mode === 'partial' ? 'peer-checked:icon-checkbox-partial peer-checked:text-blue-600' : ''
                                        ),
                                    ]"
                                ></span>
                            </label>
                        @endif

                        <p class="min-w-0 text-gray-600 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                            class="after:content-['/'] last:after:content-['']"
                                            :class="{
                                            'font-medium text-gray-800 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-800 dark:hover:text-white': available.columns.find(c => c.index === column)?.sortable,
                                        }"
                                            @click="
                                            available.columns.find(c => c.index === column)?.sortable
                                                ? sort(available.columns.find(c => c.index === column))
                                                : {}
                                        "
                                    >
                                        @{{ available.columns.find(c => c.index === column)?.label }}
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

        {{-- Body --}}
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
                        class="row grid services-dg-grid gap-2 border-b px-2 py-2.5 transition-all hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-950 sm:px-4"
                        v-for="record in available.records"
                >
                    {{-- Column 1: Name / Category / Description (+ checkbox) --}}
                    <div class="flex min-w-0 gap-2.5">
                        @if ($hasPermission)
                            <div class="pt-1">
                                <input
                                        type="checkbox"
                                        :name="`mass_action_select_record_${record.service_id}`"
                                        :id="`mass_action_select_record_${record.service_id}`"
                                        :value="record.service_id"
                                        class="peer hidden"
                                        v-model="applied.massActions.indices"
                                >

                                <label
                                        class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded-md text-2xl peer-checked:text-blue-600"
                                        :for="`mass_action_select_record_${record.service_id}`"
                                ></label>
                            </div>
                        @endif

                        <div class="flex min-w-0 flex-col gap-1.5">
                            <p class="break-words text-base font-semibold text-gray-800 dark:text-white">
                                @{{ record.name }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300">
                                <span class="font-medium">@lang('Admin::app.services.services.index.datagrid.category'):</span>
                                @{{ record.category_name ?? 'N/A' }}
                            </p>

                            <p class="text-gray-600 dark:text-gray-300 break-words">
                                <span class="font-medium">@lang('Admin::app.services.services.index.datagrid.description'):</span>
                                @{{ record.description ?? '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Column 2: ID / Sort Order / Created --}}
                    <div class="flex min-w-0 flex-col gap-1.5 text-gray-600 dark:text-gray-300">
                        <p>
                            <span class="font-medium">@lang('Admin::app.services.services.index.datagrid.id'):</span>
                            @{{ record.service_id }}
                        </p>

                        <p>
                            <span class="font-medium">@lang('Admin::app.services.services.index.datagrid.sort-order'):</span>
                            @{{ record.sort_order }}
                        </p>

                        <p class="break-words">
                        <p v-html="record.status"></p>

                        </p>
                    </div>

                    {{-- Column 3: Status / Actions --}}
                    <div class="flex items-center justify-end gap-x-3">
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span
                                    class="cursor-pointer rounded-md p-1.5 text-2xl transition-all hover:bg-gray-200 dark:hover:bg-gray-800"
                                    :class="action.icon"
                                    v-text="! action.icon ? action.title : ''"
                                    v-for="action in record.actions"
                                    @click="performAction(action)"
                            ></span>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.services.list.after') !!}
</x-admin::layouts>
