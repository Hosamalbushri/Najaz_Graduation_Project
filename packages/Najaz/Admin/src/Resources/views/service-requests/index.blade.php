<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('Admin::app.service-requests.index.title')
    </x-slot>

    @pushOnce('styles')
        <style>
            /* شبكة عرض طلبات الخدمة: 3 أعمدة على الشاشات المتوسطة فما فوق */
            @media (min-width: 768px) {
                .service-requests-dg-grid {
                    grid-template-columns: minmax(0, 2fr) minmax(0, 1.8fr) minmax(0, 0.8fr);
                }
            }

            /* تحسينات احترافية للتصميم */
            .service-request-row {
                transition: all 0.2s ease;
            }

            .service-request-row:hover {
                background-color: rgba(249, 250, 251, 0.8);
            }

            .dark .service-request-row:hover {
                background-color: rgba(17, 24, 39, 0.6);
            }

            .service-request-info-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.25rem 0.5rem;
                background-color: rgba(243, 244, 246, 0.8);
                border-radius: 0.375rem;
                font-size: 0.75rem;
                font-weight: 500;
            }

            .dark .service-request-info-badge {
                background-color: rgba(31, 41, 55, 0.8);
            }
        </style>
    @endPushOnce

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            @lang('Admin::app.service-requests.index.title')
        </p>

        <div class="flex items-center gap-x-2.5">
            <x-admin::datagrid.export src="{{ route('admin.service-requests.index') }}" />
        </div>
    </div>

    {!! view_render_event('bagisto.admin.service-requests.list.before') !!}

    <x-admin::datagrid :src="route('admin.service-requests.index')" :isMultiRow="true">
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
                <div class="row grid service-requests-dg-grid gap-2 items-center border-b border-gray-200 bg-gray-50 px-4 py-2.5 dark:border-gray-700 dark:bg-gray-800">
                    <div
                            class="flex min-w-0 select-none items-center gap-2.5"
                            v-for="(columnGroup, index) in [
                            ['service_name', 'increment_id', 'status', 'created_at'],
                            ['citizen_full_name', 'citizen_national_id', 'citizen_type_name'],
                            [],
                        ]"
                    >
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
                        class="service-request-row row grid service-requests-dg-grid border-b border-gray-100 px-2 py-2.5 dark:border-gray-800 md:gap-1.5 sm:px-4"
                        v-for="record in available.records"
                >
                    {{-- Column 1: Service Name / Order ID / Status / Date --}}
                    <div class="flex min-w-0 gap-2.5">
                        <div class="flex min-w-0 flex-col gap-1.5 flex-1">
                            <div class="flex flex-col gap-1.5">
                                <h3 class="break-words text-base font-semibold text-gray-900 dark:text-white">
                                    @{{ record.service_name ?? 'N/A' }}
                                </h3>
                                <p class="text-gray-800 dark:text-gray-200">
                                    #@{{ record.increment_id }}
                                </p>
                            </div>

                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center" v-html="record.status"></div>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ record.created_at || '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Column 2: Citizen Name / National ID / Citizen Type --}}
                    <div class="flex min-w-0 gap-2.5">
                        <div class="flex min-w-0 flex-col gap-1.5 flex-1">
                            <div class="flex flex-col gap-1.5">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                        @{{ record.citizen_full_name || '—' }}
                                    </span>
                                </div>

                                <p class="text-gray-600 dark:text-gray-300">
                                    @{{ record.citizen_national_id || '—' }}
                                </p>

                                <div class="flex items-center gap-2">
                                    <span class="service-request-info-badge text-gray-700 dark:text-gray-300">
                                        @{{ record.citizen_type_name || '—' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Column 3: Actions --}}
                    <div class="flex items-center justify-end gap-1.5 shrink-0">
                        <div
                            class="flex items-center gap-1.5"
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

    {!! view_render_event('bagisto.admin.service-requests.list.after') !!}
</x-admin::layouts>
