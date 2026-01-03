<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.services.index.title')
    </x-slot>

    @pushOnce('styles')
        <style>
            /* شبكة عرض الخدمات: عمودين على الشاشات المتوسطة فما فوق */
            @media (min-width: 768px) {
                .services-dg-grid {
                    grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
                }
            }

            /* تحسينات احترافية للتصميم */
            .service-row {
                transition: all 0.2s ease;
            }

            .service-row:hover {
                background-color: rgba(249, 250, 251, 0.8);
            }

            .dark .service-row:hover {
                background-color: rgba(17, 24, 39, 0.6);
            }

            .service-info-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.5rem;
                background-color: rgba(243, 244, 246, 0.8);
                border-radius: 0.375rem;
                font-size: 0.75rem;
                font-weight: 500;
            }

            .dark .service-info-badge {
                background-color: rgba(31, 41, 55, 0.8);
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
                <div class="row grid services-dg-grid gap-6 items-center border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <div
                            class="flex min-w-0 select-none items-center gap-3"
                            v-for="(columnGroup, index) in [
                            ['name', 'service_number', 'category_name', 'description'],
                            ['base_image', 'service_id', 'sort_order', 'status'],
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

                        <p class="min-w-0 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <span class="[&>*]:after:content-['_/_']">
                                <template v-for="column in columnGroup">
                                    <span
                                            class="after:content-['/'] last:after:content-['']"
                                            :class="{
                                            'font-semibold text-gray-900 dark:text-white': applied.sort.column == column,
                                            'cursor-pointer hover:text-gray-900 dark:hover:text-white': available.columns.find(c => c.index === column)?.sortable,
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
                                    class="align-text-bottom text-sm text-gray-900 dark:text-white ltr:ml-2 rtl:mr-2"
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
                        class="service-row row grid services-dg-grid gap-6 border-b border-gray-100 px-6 py-4 dark:border-gray-800"
                        v-for="record in available.records"
                >
                    {{-- Column 1: Name / Category / Description (+ checkbox) --}}
                    <div class="flex min-w-0 gap-4">
                        @if ($hasPermission)
                            <div class="pt-0.5">
                                <input
                                        type="checkbox"
                                        :name="`mass_action_select_record_${record.service_id}`"
                                        :id="`mass_action_select_record_${record.service_id}`"
                                        :value="record.service_id"
                                        class="peer hidden"
                                        v-model="applied.massActions.indices"
                                >

                                <label
                                        class="icon-uncheckbox peer-checked:icon-checked cursor-pointer rounded transition-colors text-2xl text-gray-400 peer-checked:text-blue-600 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400"
                                        :for="`mass_action_select_record_${record.service_id}`"
                                ></label>
                            </div>
                        @endif

                        <div class="flex min-w-0 flex-col gap-2.5 flex-1">
                            <div class="flex flex-col gap-1.5">
                                <h3 class="break-words text-base font-semibold leading-6 text-gray-900 dark:text-white">
                                    @{{ record.name }}
                                </h3>
                                <p class="text-sm text-gray-800 dark:text-gray-200">
                                    @{{ record.service_number }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                    @{{ record.category_name ?? 'N/A' }}
                                </span>
                            </div>

                            <p class="text-sm leading-5 text-gray-600 dark:text-gray-400 break-words line-clamp-2">
                                @{{ record.description || '—' }}
                            </p>
                        </div>
                    </div>

                    {{-- Column 2: Image / ID / Sort Order / Status / Actions --}}
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-4 flex-1 min-w-0">
                            <div class="relative flex-shrink-0">
                                <template v-if="record.base_image">
                                    <img
                                        class="h-20 w-20 rounded-lg object-cover border-2 border-gray-200 shadow-sm dark:border-gray-700"
                                        :src='record.base_image'
                                        alt="Service image"
                                    />
                                    <span class="absolute -bottom-1 -right-1 flex min-w-[18px] items-center justify-center rounded-full bg-darkPink px-1.5 py-0.5 text-[10px] font-bold leading-tight text-white shadow-md ring-2 ring-white dark:ring-gray-900">
                                        @{{ record.images_count }}
                                    </span>
                                </template>

                                <template v-else>
                                    <div class="h-20 w-20 rounded-lg border-2 border-dashed border-gray-300 bg-gradient-to-br from-gray-50 to-gray-100 dark:border-gray-700 dark:from-gray-800 dark:to-gray-900 flex items-center justify-center shadow-sm">
                                        <img src="{{ bagisto_asset('images/product-placeholders/front.svg')}}" class="h-8 w-8 opacity-30 dark:opacity-20">
                                    </div>
                                </template>
                            </div>

                            <div class="flex flex-col gap-3 flex-1 min-w-0">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="service-info-badge text-gray-700 dark:text-gray-300">
                                            <span class="ml-1 font-semibold">@{{ record.service_id }}</span>
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="service-info-badge text-gray-700 dark:text-gray-300">
                                            <span class="font-medium">@lang('Admin::app.services.services.index.datagrid.sort-order') -  </span>
                                            <span class="ml-1 font-semibold">@{{ record.sort_order }}</span>
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center" v-html="record.status"></div>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-1.5 shrink-0"
                            v-if="available.actions.length"
                        >
                            <button
                                type="button"
                                class="group relative flex items-center justify-center rounded-lg p-2.5 text-2xl text-gray-500 transition-all hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                                :class="action.icon"
                                v-text="! action.icon ? action.title : ''"
                                v-for="action in record.actions"
                                @click="performAction(action)"
                                :title="action.title"
                            >
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </template>
    </x-admin::datagrid>

    {!! view_render_event('bagisto.admin.services.list.after') !!}
</x-admin::layouts>
