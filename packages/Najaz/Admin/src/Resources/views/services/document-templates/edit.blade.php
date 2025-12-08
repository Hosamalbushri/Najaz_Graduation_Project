<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.document-templates.edit.title', ['service' => $service->name])
    </x-slot>

    @php
        $currentLocale = core()->getRequestedLocale();
        $locales = core()->getAllLocales();
        
        // Build available fields list based on current locale
        $availableFields = app(\Najaz\Service\Repositories\DocumentTemplateRepository::class)
            ->buildAvailableFieldsForTemplate($service, $currentLocale->code);
    @endphp

    <v-document-template-editor
        :template-id="{{ $template->id ?? null }}"
        :service-id="{{ $service->id }}"
        :template-data="{{ json_encode($template) }}"
        :available-fields="{{ json_encode($availableFields) }}"
        :current-locale='@json($currentLocale)'
        :locales='@json($locales)'
    ></v-document-template-editor>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-document-template-editor-template">
            <div>
                <x-admin::form
                    v-slot="{ meta, errors, handleSubmit }"
                    as="div"
                >
                    <form
                        @submit="handleSubmit($event, saveTemplate)"
                        ref="templateForm"
                    >
                        @csrf

                        <!-- Page Header -->
                        <div class="mb-6 flex items-center justify-between gap-4 rounded-lg border-b border-gray-200 bg-white px-6 py-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 max-sm:flex-wrap max-sm:px-4">
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                @lang('Admin::app.services.document-templates.edit.title', ['service' => $service->name])
                                </h1>
                            </div>

                            <div class="flex items-center gap-x-3">
                                <!-- Back Button -->
                                <a
                                    href="{{ route('admin.services.document-templates.index') }}"
                                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                                >
                                    @lang('Admin::app.services.document-templates.edit.cancel-btn')
                                </a>

                                <!-- Save Button -->
                                <x-admin::button
                                    button-type="submit"
                                    class="primary-button"
                                    :title="trans('Admin::app.services.document-templates.edit.save-btn')"
                                    ::loading="isSaving"
                                    ::disabled="isSaving"
                                />
                            </div>
                        </div>

                        <!-- Locale Switcher -->
                        <div class="mt-7 flex items-center justify-between gap-4 max-md:flex-wrap">
                            <div class="flex items-center gap-x-1">
                                <!-- Locale Switcher -->
                                <x-admin::dropdown :class="$locales->count() <= 1 ? 'hidden' : ''">
                                    <!-- Dropdown Toggler -->
                                    <x-slot:toggle>
                                        <button
                                            type="button"
                                            class="transparent-button px-1 py-1.5 hover:bg-gray-200 focus:bg-gray-200 dark:text-white dark:hover:bg-gray-800 dark:focus:bg-gray-800"
                                        >
                                            <span class="icon-language text-2xl"></span>

                                            {{ $currentLocale->name }}

                                            <input
                                                type="hidden"
                                                name="locale"
                                                value="{{ $currentLocale->code }}"
                                            />

                                            <span class="icon-sort-down text-2xl"></span>
                                        </button>
                                    </x-slot>

                                    <!-- Dropdown Content -->
                                    <x-slot:content class="!p-0">
                                        @foreach ($locales->sortBy('name') as $locale)
                                            <a
                                                href="?locale={{ $locale->code }}"
                                                class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : '' }}"
                                            >
                                                {{ $locale->name }}
                                            </a>
                                        @endforeach
                                    </x-slot>
                                </x-admin::dropdown>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <!-- First Row: Template Content (Full Width) -->
                        <div class="mt-6">
                            <div class="box-shadow rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                                <x-admin::accordion>
                                    <x-slot:header>
                                        <div class="flex items-center justify-between w-full px-1">
                                            <div class="flex items-center gap-3">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-base font-semibold text-gray-900 dark:text-white">
                                                @lang('Admin::app.services.document-templates.edit.title-label')
                                            </p>
                                            </div>
                                            
                                            <div class="flex items-center gap-3" @click.stop>
                                                <label class="flex items-center gap-2.5 cursor-pointer rounded-lg px-3 py-1.5 transition-colors duration-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        @lang('Admin::app.services.document-templates.edit.is-active')
                                                    </span>
                                                    <x-admin::form.control-group.control
                                                        type="switch"
                                                        name="is_active"
                                                        value="1"
                                                        :checked="old('is_active', $template->is_active ?? true)"
                                                    />
                                                    <x-admin::form.control-group.control
                                                        type="hidden"
                                                        name="is_active"
                                                        value="0"
                                                    />
                                                </label>
                                            </div>
                                        </div>
                                    </x-slot:header>

                                    <x-slot:content>
                                        <div class="px-1 pb-1">
                                        <x-admin::form.control-group>
                                                <x-admin::form.control-group.label class="required mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                @lang('Admin::app.services.document-templates.edit.template-content')
                                            </x-admin::form.control-group.label>

                                            <!-- Field Insertion Toolbar -->
                                            <div class="mb-6 rounded-xl border border-gray-200 bg-gradient-to-br from-blue-50/50 via-white to-gray-50/50 p-5 shadow-sm transition-all duration-300 dark:border-gray-700 dark:from-gray-800/50 dark:via-gray-800/30 dark:to-gray-900/50">
                                                <!-- Toolbar Header -->
                                                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:justify-between">
                                                    <!-- Title Section -->
                                                    <div class="flex items-start gap-3">
                                                        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-100 to-blue-200 shadow-sm dark:from-blue-900/40 dark:to-blue-800/40">
                                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                        </svg>
                                                        </div>
                                                        <div>
                                                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                                            @lang('Admin::app.services.document-templates.edit.insert-field')
                                                            </h3>
                                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                                @lang('Admin::app.services.document-templates.edit.select-fields-description')
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Search input -->
                                                    <div class="relative w-full sm:w-80 sm:flex-shrink-0">
                                                        <i class="icon-search absolute top-1/2 -translate-y-1/2 flex items-center text-lg text-gray-400 ltr:left-3 rtl:right-3"></i>
                                                        <input
                                                            type="text"
                                                            class="w-full rounded-md border px-10 py-2.5 text-sm text-text-secondary transition-all hover:border-border-hover focus:border-border-focus dark:border-border-default dark:bg-surface-card dark:text-text-secondary dark:hover:border-border-hover dark:focus:border-border-focus"
                                                            placeholder="@lang('Admin::app.services.document-templates.edit.search-field')"
                                                            v-model.lazy="fieldSearchQuery"
                                                            v-debounce="500"
                                                        >
                                                    </div>
                                                </div>
                                                
                                                <!-- Tabs Navigation -->
                                                <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                                                    <nav class="-mb-px flex space-x-1 overflow-x-auto" aria-label="Tabs">
                                                        <!-- Group Tabs -->
                                                        <button
                                                            v-for="(fields, group) in filteredGroupedFields"
                                                            :key="group"
                                                            @click="activeFieldTab = group"
                                                            :class="[
                                                                'group relative flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition-all duration-200',
                                                                activeFieldTab === group
                                                                    ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'
                                                            ]"
                                                            type="button"
                                                        >
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                                            </svg>
                                                            <span>@{{ group }}</span>
                                                            <span
                                                                :class="[
                                                                    'ml-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                                                    activeFieldTab === group
                                                                        ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                                                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                                                                ]"
                                                            >
                                                                @{{ fields.length }}
                                                            </span>
                                                        </button>
                                                    </nav>
                                                </div>
                                                
                                                <!-- Tab Content: Group Fields -->
                                                <div
                                                            v-for="(fields, group) in filteredGroupedFields"
                                                            :key="group"
                                                    v-show="activeFieldTab === group"
                                                    class="space-y-3"
                                                        >
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <button
                                                                v-for="field in fields"
                                                                :key="field.code"
                                                            @click="insertField(field.code)"
                                                            class="group inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-transparent hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200 ease-in-out hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-1"
                                                            type="button"
                                                            >
                                                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400 transition-transform duration-200 group-hover:rotate-90 group-hover:text-blue-600 dark:group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                            </svg>
                                                            <span class="group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">@{{ field.label }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Quick Access and Used Fields Tabs -->
                                            <div class="mb-6 rounded-xl border border-gray-200 bg-gradient-to-br from-green-50/50 via-white to-blue-50/50 p-5 shadow-sm transition-all duration-300 dark:border-gray-700 dark:from-gray-800/50 dark:via-gray-800/30 dark:to-gray-900/50">
                                                <!-- Tabs Navigation -->
                                                <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                                                    <nav class="-mb-px flex space-x-1 overflow-x-auto" aria-label="Quick Tabs">
                                                        <!-- Quick Access Tab -->
                                                        <button
                                                            @click="activeQuickTab = 'quick'"
                                                            :class="[
                                                                'group relative flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition-all duration-200',
                                                                activeQuickTab === 'quick'
                                                                    ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'
                                                            ]"
                                                            type="button"
                                                        >
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                                            </svg>
                                                            <span>@lang('Admin::app.services.document-templates.edit.quick-access-tab')</span>
                                                            <span
                                                                :class="[
                                                                    'ml-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                                                    activeQuickTab === 'quick'
                                                                        ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                                                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                                                                ]"
                                                            >
                                                                @{{ quickAccessFields.length }}
                                                            </span>
                                                        </button>
                                                        
                                                        <!-- Used Fields Tab -->
                                                        <button
                                                            @click="activeQuickTab = 'used'"
                                                            :class="[
                                                                'group relative flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition-all duration-200',
                                                                activeQuickTab === 'used'
                                                                    ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                                                    : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300'
                                                            ]"
                                                            type="button"
                                                        >
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span>@lang('Admin::app.services.document-templates.edit.used-fields-tab')</span>
                                                            <span
                                                                :class="[
                                                                    'ml-1 rounded-full px-2 py-0.5 text-xs font-medium',
                                                                    activeQuickTab === 'used'
                                                                        ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                                                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                                                                ]"
                                                            >
                                                                @{{ usedFieldsList.length }}
                                                            </span>
                                                        </button>
                                                    </nav>
                                                </div>
                                                
                                                <!-- Tab Content: Quick Access -->
                                                <div v-show="activeQuickTab === 'quick'" class="space-y-3">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                                            @lang('Admin::app.services.document-templates.edit.quick-access')
                                                        </span>
                                                        <button
                                                            v-for="quickField in quickAccessFields"
                                                            :key="quickField.code"
                                                            @click="insertField(quickField.code)"
                                                            class="group inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-transparent hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-all duration-200 ease-in-out hover:scale-105 active:scale-95 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:ring-offset-1"
                                                            type="button"
                                                        >
                                                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400 transition-transform duration-200 group-hover:rotate-90 group-hover:text-blue-600 dark:group-hover:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                            </svg>
                                                            <span class="group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-200">@{{ quickField.label }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <!-- Tab Content: Used Fields -->
                                                <div v-show="activeQuickTab === 'used'" class="space-y-3">
                                                    <div v-if="usedFieldsList.length === 0" class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-8 text-center dark:border-gray-700 dark:bg-gray-800/50">
                                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                            @lang('Admin::app.services.document-templates.edit.no-fields-used')
                                                        </p>
                                                    </div>
                                                    <div v-else class="flex flex-wrap items-center gap-2">
                                                        <span
                                                            v-for="usedField in usedFieldsList"
                                                            :key="usedField.code"
                                                            class="group inline-flex items-center gap-2 rounded-lg bg-transparent px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                        >
                                                            <svg class="h-4 w-4 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            <span>@{{ usedField.label }}</span>
                                                            <button
                                                                @click="confirmDeleteAllInstances(usedField.code, usedField.label)"
                                                                class="ml-1.5 rounded-full p-1 transition-all duration-200 hover:bg-red-100 dark:hover:bg-red-900/30 hover:scale-110 active:scale-95 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:ring-offset-1"
                                                                type="button"
                                                                :title="deleteAllInstancesText.replace(':field', usedField.label)"
                                                            >
                                                                <svg class="h-3.5 w-3.5 text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <x-admin::form.control-group.control
                                                type="textarea"
                                                id="template_content"
                                                name="template_content"
                                                rules="required"
                                                :value="old('template_content', $template->template_content ?? '')"
                                                :label="trans('Admin::app.services.document-templates.edit.template-content')"
                                                :placeholder="trans('Admin::app.services.document-templates.edit.template-content-placeholder')"
                                                :tinymce="true"
                                            />

                                            <x-admin::form.control-group.error control-name="template_content" />

                                                <div class="mt-4 flex items-start gap-3 rounded-lg border border-blue-200 bg-gradient-to-r from-blue-50 to-blue-50/50 p-4 shadow-sm dark:border-blue-800/50 dark:from-blue-900/20 dark:to-blue-900/10">
                                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/40">
                                                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-sm font-medium text-blue-900 dark:text-blue-100">
                                                            @lang('Admin::app.services.document-templates.edit.tip-label')
                                                        </p>
                                                        <p class="mt-1 text-xs leading-relaxed text-blue-700 dark:text-blue-200">
                                                    @lang('Admin::app.services.document-templates.edit.template-help')
                                                </p>
                                                    </div>
                                            </div>
                                        </x-admin::form.control-group>
                                        </div>
                                    </x-slot:content>
                                </x-admin::accordion>
                            </div>
                        </div>

                        <!-- Second Row: Footer Text and Header Image (Side by Side) -->
                        <div class="mt-6 flex gap-4 max-xl:flex-wrap">
                            <!-- Footer Text Accordion -->
                            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                                <div class="box-shadow rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                                    <x-admin::accordion>
                                        <x-slot:header>
                                            <div class="flex items-center gap-3 px-1">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                                                    <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-base font-semibold text-gray-900 dark:text-white">
                                                @lang('Admin::app.services.document-templates.edit.footer-text')
                                            </p>
                                            </div>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <div class="px-1 pb-1">
                                            <x-admin::form.control-group class="!mb-0">
                                                    <x-admin::form.control-group.label class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    @lang('Admin::app.services.document-templates.edit.footer-text')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="textarea"
                                                    id="footer_text"
                                                    name="footer_text"
                                                    v-model="footerText"
                                                    :value="old('footer_text', $template->footer_text ?? '')"
                                                    :label="trans('Admin::app.services.document-templates.edit.footer-text')"
                                                    :placeholder="trans('Admin::app.services.document-templates.edit.footer-text')"
                                                    :tinymce="true"
                                                />

                                                <x-admin::form.control-group.error control-name="footer_text" />
                                            </x-admin::form.control-group>
                                            </div>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>
                            </div>

                            <!-- Header Image Accordion -->
                            <div class="flex w-[360px] max-w-full flex-col gap-2 max-xl:flex-auto max-xl:w-full">
                                <div class="box-shadow rounded-xl border border-gray-200 bg-white shadow-sm transition-shadow duration-300 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                                    <x-admin::accordion>
                                        <x-slot:header>
                                            <div class="flex items-center gap-3 px-1">
                                                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                                                    <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                                <p class="text-base font-semibold text-gray-900 dark:text-white">
                                                @lang('Admin::app.services.document-templates.edit.header-image')
                                            </p>
                                            </div>
                                        </x-slot:header>

                                        <x-slot:content>
                                            <div class="px-1 pb-1">
                                            <x-admin::form.control-group class="!mb-0">
                                                    <x-admin::form.control-group.label class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    @lang('Admin::app.services.document-templates.edit.header-image')
                                                </x-admin::form.control-group.label>

                                                <x-admin::form.control-group.control
                                                    type="image"
                                                    name="header_image"
                                                    :value="old('header_image', $template->header_image ?? '')"
                                                    :label="trans('Admin::app.services.document-templates.edit.header-image')"
                                                />

                                                <x-admin::form.control-group.error control-name="header_image" />
                                            </x-admin::form.control-group>
                                            </div>
                                        </x-slot:content>
                                    </x-admin::accordion>
                                </div>
                            </div>
                        </div>
                    </form>
                </x-admin::form>
            </div>
        </script>

        <script type="module">
            app.component('v-document-template-editor', {
                template: '#v-document-template-editor-template',

                props: {
                    templateId: {
                        type: Number,
                        default: null,
                    },
                    serviceId: {
                        type: Number,
                        required: true,
                    },
                    templateData: {
                        type: Object,
                        default: null,
                    },
                    availableFields: {
                        type: Array,
                        default: () => [],
                    },
                    currentLocale: {
                        type: Object,
                        default: () => ({ code: 'ar', name: 'العربية' }),
                    },
                    locales: {
                        type: Array,
                        default: () => [],
                    },
                },

                data() {
                    // Get translation for current locale
                    const translations = this.templateData?.translations || [];
                    const currentTranslation = translations.find(t => t.locale === this.currentLocale.code) || {};
                    
                    return {
                        templateContent: currentTranslation.template_content || '',
                        footerText: currentTranslation.footer_text || '',
                        selectedField: '',
                        fieldSearchQuery: '',
                        activeFieldTab: null,
                        activeQuickTab: 'quick',
                        isSaving: false,
                        isUpdatingContent: false,
                        clickToDeleteText: '@lang('Admin::app.services.document-templates.edit.click-to-delete')',
                        deleteAllInstancesText: '@lang('Admin::app.services.document-templates.edit.delete-all-instances')',
                        confirmDeleteFieldText: '@lang('Admin::app.services.document-templates.edit.confirm-delete-field')',
                        confirmDeleteAllInstancesText: '@lang('Admin::app.services.document-templates.edit.confirm-delete-all-instances')',
                        fieldDeletedSuccessText: '@lang('Admin::app.services.document-templates.edit.field-deleted-success')',
                        confirmAddFieldText: '@lang('Admin::app.services.document-templates.edit.confirm-add-field')',
                        fieldAddedSuccessText: '@lang('Admin::app.services.document-templates.edit.field-added-success')',
                        saveErrorText: '@lang('Admin::app.services.document-templates.edit.save-error')',
                    };
                },
                
                watch: {
                    // Watch for locale changes in URL (page reload)
                    '$route.query.locale'() {
                        // Page will reload when locale changes, so this is mainly for future use
                    }
                },
                
                mounted() {
                    this.initTinyMCE();
                    
                    // Listen for theme changes to re-apply styles
                    this.$emitter.on('change-theme', (theme) => {
                        const tinyEditor = this.getTinyMCEEditor();
                        if (tinyEditor && tinyEditor.initialized) {
                            setTimeout(() => {
                                this.addFieldBadgeStyles(tinyEditor);
                            }, 200);
                        }
                    });
                    
                    // Set default tab to first group if available
                    this.$nextTick(() => {
                        const groups = Object.keys(this.groupedFields);
                        if (groups.length > 0 && !this.activeFieldTab) {
                            this.activeFieldTab = groups[0];
                        }
                    });
                },

                beforeUnmount() {
                    // Remove event listeners
                    this.$emitter.off('change-theme');
                },

                computed: {
                    // Group fields by their group property
                    groupedFields() {
                        const grouped = {};
                        this.availableFields.forEach(field => {
                            const group = field.group || 'أخرى';
                            if (!grouped[group]) {
                                grouped[group] = [];
                            }
                            grouped[group].push(field);
                        });
                        return grouped;
                    },
                    
                    // Filter grouped fields based on search query
                    filteredGroupedFields() {
                        if (!this.fieldSearchQuery.trim()) {
                            return this.groupedFields;
                        }
                        
                        const query = this.fieldSearchQuery.toLowerCase().trim();
                        const filtered = {};
                        
                        Object.keys(this.groupedFields).forEach(group => {
                            const filteredFields = this.groupedFields[group].filter(field => {
                                return field.label.toLowerCase().includes(query) ||
                                       field.code.toLowerCase().includes(query);
                            });
                            
                            if (filteredFields.length > 0) {
                                filtered[group] = filteredFields;
                            }
                        });
                        
                        return filtered;
                    },
                    
                    // Quick access fields (commonly used)
                    quickAccessFields() {
                        const commonCodes = [
                            'citizen_full_name',
                            'citizen_national_id',
                            'request_increment_id',
                            'request_date',
                            'current_date'
                        ];
                        
                        return this.availableFields.filter(field => 
                            commonCodes.includes(field.code)
                        ).slice(0, 6);
                    },
                    
                    // List of fields currently used in the template
                    usedFieldsList() {
                        if (!this.templateContent) return [];
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = this.templateContent;
                        // Search for both span[data-field] and span[data-template-field]
                        const fieldSpans = tempDiv.querySelectorAll('span[data-field], span[data-template-field]');
                        
                        const usedFieldsMap = new Map();
                        fieldSpans.forEach(span => {
                            const fieldCode = span.getAttribute('data-field');
                            if (fieldCode && !usedFieldsMap.has(fieldCode)) {
                                const field = this.availableFields.find(f => f.code === fieldCode);
                                if (field) {
                                    usedFieldsMap.set(fieldCode, field);
                                }
                            }
                        });
                        
                        return Array.from(usedFieldsMap.values());
                    },
                },

                methods: {
                    /**
                     * Initialize TinyMCE editor and set up event handlers
                     */
                    initTinyMCE() {
                        this.$nextTick(() => {
                            const initEditor = () => {
                                const tinyEditor = tinymce.get('template_content');
                                if (tinyEditor && tinyEditor.initialized) {
                                    // Add styles first
                                    this.addFieldBadgeStyles(tinyEditor);
                                    
                                    // Wait for iframe to be ready
                                    setTimeout(() => {
                                        // Double check editor is still ready
                                        if (tinyEditor.initialized && tinyEditor.getBody()) {
                                    this.addFieldBadgeStyles(tinyEditor);
                                    this.updateEditorContent();
                                    this.attachBadgeDeleteHandlers();
                                    this.preventFieldPlaceholderEditing(tinyEditor);
                                    
                                            // Re-apply styles after content update
                                            this.$nextTick(() => {
                                                this.addFieldBadgeStyles(tinyEditor);
                                            });
                                        }
                                    }, 300);
                                    
                                    // Set up event handlers
                                    tinyEditor.on('keyup', () => {
                                        if (!this.isUpdatingContent && tinyEditor.initialized) {
                                            this.syncContentFromTinyMCE();
                                        }
                                    });
                                    
                                    tinyEditor.on('change', () => {
                                        if (!this.isUpdatingContent && tinyEditor.initialized) {
                                            this.syncContentFromTinyMCE();
                                        }
                                    });
                                    
                                    tinyEditor.on('NodeChange', (e) => {
                                        if (tinyEditor.initialized) {
                                        this.handleNodeChange(e, tinyEditor);
                                        }
                                    });
                                    
                                    // Update editor content when ready
                                    tinyEditor.on('init', () => {
                                        setTimeout(() => {
                                            this.addFieldBadgeStyles(tinyEditor);
                                            this.updateEditorContent();
                                        }, 100);
                                    });
                                } else {
                                    setTimeout(initEditor, 100);
                                }
                            };
                            
                            initEditor();
                        });
                    },
                    
                    /**
                     * Get TinyMCE editor instance for template content
                     */
                    getTinyMCEEditor() {
                        const editor = tinymce.get('template_content');
                        // Return editor only if it's initialized
                        if (editor && editor.initialized) {
                            return editor;
                        }
                        return null;
                    },
                    
                    /**
                     * Get TinyMCE editor instance for footer text
                     */
                    getFooterTextTinyMCEEditor() {
                        return tinymce.get('footer_text');
                    },
                    
                    /**
                     * Add custom styles for field badges in TinyMCE
                     */
                    addFieldBadgeStyles(tinyEditor) {
                        const styleContent = `
                        `;
                        
                        // Add styles to TinyMCE iframe
                        this.$nextTick(() => {
                            const applyIframeStyles = () => {
                            const editorBody = tinyEditor.getBody();
                            if (editorBody) {
                                const iframe = tinyEditor.getContentAreaContainer().querySelector('iframe');
                                if (iframe && iframe.contentDocument) {
                                        const iframeDoc = iframe.contentDocument;
                                        const existingStyle = iframeDoc.getElementById('field-badge-styles');
                                        if (existingStyle) {
                                            existingStyle.remove();
                                        }
                                        
                                        // Check if dark mode is active
                                        const isDark = document.documentElement.classList.contains('dark');
                                        
                                        // Apply dark mode class to iframe html and body
                                        if (isDark) {
                                            iframeDoc.documentElement.classList.add('dark');
                                            iframeDoc.body.classList.add('dark');
                                        } else {
                                            iframeDoc.documentElement.classList.remove('dark');
                                            iframeDoc.body.classList.remove('dark');
                                        }
                                        
                                        const iframeStyle = iframeDoc.createElement('style');
                                        iframeStyle.id = 'field-badge-styles';
                                        iframeStyle.textContent = styleContent;
                                        iframeDoc.head.appendChild(iframeStyle);
                                }
                            }
                            };
                            
                            // Apply styles immediately
                            applyIframeStyles();
                            
                            // Re-apply styles after delays to ensure iframe is ready
                            setTimeout(applyIframeStyles, 100);
                            setTimeout(applyIframeStyles, 300);
                        });
                    },
                    
                    
                    /**
                     * Prevent editing of field placeholders in TinyMCE
                     */
                    preventFieldPlaceholderEditing(tinyEditor) {
                        const editorBody = tinyEditor.getBody();
                        if (!editorBody) return;
                        
                        // Prevent text editing inside field badges but allow style editing
                        editorBody.addEventListener('keydown', (e) => {
                            const selection = tinyEditor.selection.getNode();
                            const fieldBadge = selection && selection.classList && selection.classList.contains('field-badge') 
                                ? selection 
                                : selection && selection.closest ? selection.closest('.field-badge') : null;
                            
                            if (fieldBadge) {
                                // Allow arrow keys for navigation, but prevent text input
                                if (e.key.length === 1 || e.key === 'Backspace' || e.key === 'Delete') {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                                }
                            }
                        });
                        
                        editorBody.addEventListener('paste', (e) => {
                            const selection = tinyEditor.selection.getNode();
                            const fieldBadge = selection && selection.classList && selection.classList.contains('field-badge') 
                                ? selection 
                                : selection && selection.closest ? selection.closest('.field-badge') : null;
                            
                            if (fieldBadge) {
                                e.preventDefault();
                                e.stopPropagation();
                                return false;
                            }
                        });
                        
                        // Prevent text input but allow style changes
                        editorBody.addEventListener('input', (e) => {
                            const target = e.target;
                            if (target && target.classList && target.classList.contains('field-badge')) {
                                // Restore original text content if user tries to edit
                                const fieldCode = target.getAttribute('data-field');
                                if (fieldCode) {
                                    const field = this.availableFields.find(f => f.code === fieldCode);
                                    const displayText = field ? field.label : fieldCode;
                                    if (target.textContent !== displayText) {
                                        target.textContent = displayText;
                                    }
                                }
                            }
                        });
                    },
                    
                    /**
                     * Handle node change events in TinyMCE to prevent editing field placeholders
                     */
                    handleNodeChange(e, tinyEditor) {
                        const node = e.element;
                        const fieldBadge = node && node.classList && node.classList.contains('field-badge') 
                            ? node 
                            : node && node.closest ? node.closest('.field-badge') : null;
                        
                        if (fieldBadge) {
                            if (fieldBadge.nextSibling) {
                                tinyEditor.selection.setCursorLocation(fieldBadge.nextSibling, 0);
                            } else if (fieldBadge.parentNode) {
                                const range = tinyEditor.dom.createRng();
                                range.setStartAfter(fieldBadge);
                                range.setEndAfter(fieldBadge);
                                tinyEditor.selection.setRng(range);
                            }
                        }
                    },
                    
                    /**
                     * Attach click handlers for deleting field badges in TinyMCE
                     */
                    attachBadgeDeleteHandlers() {
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor) return;
                        
                        const editorBody = tinyEditor.getBody();
                        if (!editorBody) return;
                        
                        const existingHandler = editorBody._badgeDeleteHandler;
                        if (existingHandler) {
                            editorBody.removeEventListener('click', existingHandler);
                        }
                        
                        const handler = (e) => {
                            const badge = e.target.closest('[data-delete-field]');
                            if (badge) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                const fieldCode = badge.getAttribute('data-delete-field');
                                const field = this.availableFields.find(f => f.code === fieldCode);
                                const fieldLabel = field ? field.label : fieldCode;
                                
                                // Find the actual field badge element (parent of the delete button)
                                const fieldBadge = badge.closest('.field-badge');
                                
                                this.$emitter.emit('open-confirm-modal', {
                                    message: this.confirmDeleteFieldText.replace(':field', fieldLabel),
                                    agree: () => {
                                        if (fieldBadge) {
                                            // Remove only the selected field from the editor
                                            fieldBadge.remove();
                                            
                                            // Also remove spaces before and after if they exist
                                            const prevSibling = fieldBadge.previousSibling;
                                            const nextSibling = fieldBadge.nextSibling;
                                            
                                            // Remove space before if it's just a space
                                            if (prevSibling && 
                                                prevSibling.nodeType === 3 && 
                                                prevSibling.textContent.trim() === '' && 
                                                prevSibling.textContent === ' ') {
                                                prevSibling.remove();
                                            }
                                            
                                            // Remove space after if it's just a space
                                            if (nextSibling && 
                                                nextSibling.nodeType === 3 && 
                                                nextSibling.textContent.trim() === '' && 
                                                nextSibling.textContent === ' ') {
                                                nextSibling.remove();
                                            }
                                            
                                            // Sync content to update templateContent (only the selected field is removed)
                                            this.syncContentFromTinyMCE();
                                            
                                            // Show success message
                                            this.$emitter.emit('add-flash', {
                                                type: 'success',
                                                message: this.fieldDeletedSuccessText.replace(':field', fieldLabel)
                                            });
                                        }
                                    }
                                });
                                return;
                            }
                            
                            const fieldPlaceholder = e.target.closest('.field-badge');
                            if (fieldPlaceholder && e.detail === 2) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                const fieldCode = fieldPlaceholder.getAttribute('data-field');
                                if (fieldCode) {
                                    const field = this.availableFields.find(f => f.code === fieldCode);
                                    const fieldLabel = field ? field.label : fieldCode;
                                    
                                    this.$emitter.emit('open-confirm-modal', {
                                        message: this.confirmDeleteFieldText.replace(':field', fieldLabel),
                                        agree: () => {
                                            // Remove only the selected field from the editor
                                            fieldPlaceholder.remove();
                                            
                                            // Also remove spaces before and after if they exist
                                            const prevSibling = fieldPlaceholder.previousSibling;
                                            const nextSibling = fieldPlaceholder.nextSibling;
                                            
                                            // Remove space before if it's just a space
                                            if (prevSibling && 
                                                prevSibling.nodeType === 3 && 
                                                prevSibling.textContent.trim() === '' && 
                                                prevSibling.textContent === ' ') {
                                                prevSibling.remove();
                                            }
                                            
                                            // Remove space after if it's just a space
                                            if (nextSibling && 
                                                nextSibling.nodeType === 3 && 
                                                nextSibling.textContent.trim() === '' && 
                                                nextSibling.textContent === ' ') {
                                                nextSibling.remove();
                                            }
                                            
                                            // Sync content to update templateContent (only the selected field is removed)
                                            this.syncContentFromTinyMCE();
                                            
                                            // Show success message
                                            this.$emitter.emit('add-flash', {
                                                type: 'success',
                                                message: this.fieldDeletedSuccessText.replace(':field', fieldLabel)
                                            });
                                        }
                                    });
                                }
                            }
                        };
                        
                        editorBody._badgeDeleteHandler = handler;
                        editorBody.addEventListener('click', handler);
                    },
                    
                    /**
                     * Insert a field into the TinyMCE editor at the current cursor position
                     * @param {string} fieldCode - The code of the field to insert
                     */
                    insertField(fieldCode) {
                        if (!fieldCode) return;

                        const field = this.availableFields.find(f => f.code === fieldCode);
                        if (!field) return;

                        const displayText = field.label || fieldCode;
                        
                        // Show confirmation modal
                        this.$emitter.emit('open-confirm-modal', {
                            message: this.confirmAddFieldText.replace(':field', displayText),
                            agree: () => {
                                this.doInsertField(fieldCode, field, displayText);
                            }
                        });
                    },
                    
                    /**
                     * Actually insert the field into the editor (called after confirmation)
                     * @param {string} fieldCode - The code of the field to insert
                     * @param {object} field - The field object
                     * @param {string} displayText - The display text for the field
                     */
                    doInsertField(fieldCode, field, displayText) {
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor || !tinyEditor.initialized) {
                            this.$nextTick(() => {
                                setTimeout(() => {
                                    this.doInsertField(fieldCode, field, displayText);
                                }, 100);
                            });
                            return;
                        }

                        // Ensure editor body is ready
                        if (!tinyEditor.getBody()) {
                            setTimeout(() => {
                                this.doInsertField(fieldCode, field, displayText);
                            }, 100);
                            return;
                        }

                        try {
                            this.syncContentFromTinyMCE();
                            
                            const tooltipText = this.clickToDeleteText.replace(':field', displayText);
                            
                            // Default styles for new field - only border, no colors
                            const isDark = document.documentElement.classList.contains('dark');
                            const defaultBorder = isDark ? '#6b7280' : '#9ca3af';

                            const badgeHtml = `<span class="field-badge" data-field="${this.escapeHtml(fieldCode)}" data-delete-field="${this.escapeHtml(fieldCode)}" contenteditable="false" spellcheck="false" draggable="false" title="${this.escapeHtml(tooltipText)}" style="display: inline-flex !important; align-items: center !important; gap: 6px !important; border: 1px solid ${defaultBorder} !important; border-radius: 6px !important; padding: 5px 10px !important; margin: 0 2px !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; font-size: 0.8125rem !important; font-weight: 500 !important; user-select: none !important; cursor: move !important; transition: all 0.15s ease !important; vertical-align: middle !important; line-height: 1.4 !important; position: relative !important;"><i class="icon-edit" style="font-size: 0.75rem !important; opacity: 0.7 !important; flex-shrink: 0 !important;"></i>${this.escapeHtml(displayText)}</span>`;

                            tinyEditor.execCommand('mceInsertContent', false, badgeHtml);
                            
                            // Apply styles after insertion
                            this.$nextTick(() => {
                                this.addFieldBadgeStyles(tinyEditor);
                                this.syncContentFromTinyMCE();
                            });
                            
                            this.attachBadgeDeleteHandlers();
                            this.selectedField = '';
                            
                            // Show success message
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: this.fieldAddedSuccessText.replace(':field', displayText)
                            });
                        } catch (error) {
                            console.warn('Error inserting field:', error);
                            // Retry after a delay
                            setTimeout(() => {
                                this.doInsertField(fieldCode, field, displayText);
                            }, 200);
                        }
                    },
                    
                    /**
                     * Sync content from TinyMCE editor to Vue data model
                     * Converts field badges to code tags for storage
                     */
                    syncContentFromTinyMCE() {
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor || !tinyEditor.initialized) return;
                        
                        try {
                        this.isUpdatingContent = true;
                            let html = tinyEditor.getContent({ format: 'html' });
                            
                            if (!html || !html.trim()) {
                                this.templateContent = '';
                                this.isUpdatingContent = false;
                                return;
                            }
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = html;
                        
                        const spans = tempDiv.querySelectorAll('span[data-field]');
                        spans.forEach(span => {
                            const fieldCode = span.getAttribute('data-field');
                                if (!fieldCode || !span.parentNode) return;
                                
                            const field = this.availableFields.find(f => f.code === fieldCode);
                            const displayText = field ? field.label : fieldCode;
                        
                            const saveSpan = document.createElement('span');
                            saveSpan.setAttribute('data-field', fieldCode);
                            saveSpan.setAttribute('data-template-field', 'true');
                            saveSpan.setAttribute('class', 'field-badge');
                            
                            // Default styles to save with field - only border, no colors
                            const defaultStyles = {
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: '6px',
                                border: '1px solid #9ca3af',
                                borderRadius: '6px',
                                padding: '5px 10px',
                                margin: '0 2px',
                                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
                                fontSize: '0.8125rem',
                                fontWeight: '500',
                                userSelect: 'none',
                                cursor: 'move',
                                transition: 'all 0.15s ease',
                                verticalAlign: 'middle',
                                lineHeight: '1.4',
                                position: 'relative'
                            };
                            
                            // Apply default styles
                            Object.keys(defaultStyles).forEach(prop => {
                                const cssProp = prop.replace(/([A-Z])/g, '-$1').toLowerCase();
                                saveSpan.style.setProperty(cssProp, defaultStyles[prop], 'important');
                            });
                            
                            // Preserve any custom styles that user might have added (override defaults)
                            // But exclude color-related properties (background, color, box-shadow)
                            if (span.style && span.style.cssText) {
                                const excludedProperties = ['background', 'background-color', 'color', 'box-shadow'];
                                const customStyles = span.style.cssText.split(';').filter(s => s.trim());
                                customStyles.forEach(style => {
                                    if (style.trim()) {
                                        const [prop, value] = style.split(':').map(s => s.trim());
                                        if (prop && value) {
                                            const cssProp = prop.replace(/([A-Z])/g, '-$1').toLowerCase();
                                            // Only apply if not a color-related property
                                            if (!excludedProperties.includes(cssProp.toLowerCase())) {
                                                saveSpan.style.setProperty(cssProp, value, 'important');
                                            }
                                        }
                                    }
                                });
                            }
                            
                            // Check for dark mode and apply dark border color
                            const isDark = document.documentElement.classList.contains('dark');
                            if (isDark) {
                                saveSpan.style.setProperty('border-color', '#6b7280', 'important');
                            }
                            
                            // Add edit icon
                            const icon = document.createElement('i');
                            icon.className = 'icon-edit';
                            icon.style.cssText = 'font-size: 0.75rem; opacity: 0.7; flex-shrink: 0;';
                            saveSpan.appendChild(icon);
                            
                            // Add text content
                            saveSpan.appendChild(document.createTextNode(displayText));
                            
                            // Add space before and after the field if not already present
                            if (span.parentNode) {
                                // Check if there's already a space before
                                const prevSibling = span.previousSibling;
                                const hasSpaceBefore = prevSibling && 
                                    prevSibling.nodeType === 3 && 
                                    prevSibling.textContent.trim() === '' && 
                                    prevSibling.textContent.length > 0;
                                
                                // Check if there's already a space after
                                const nextSibling = span.nextSibling;
                                const hasSpaceAfter = nextSibling && 
                                    nextSibling.nodeType === 3 && 
                                    nextSibling.textContent.trim() === '' && 
                                    nextSibling.textContent.length > 0;
                                
                                // Store next sibling before replacement
                                const nextSiblingAfter = nextSibling;
                                
                                // Add space before if not present
                                if (!hasSpaceBefore) {
                                    const spaceBefore = document.createTextNode(' ');
                                    span.parentNode.insertBefore(spaceBefore, span);
                                }
                                
                                // Replace the span
                                span.parentNode.replaceChild(saveSpan, span);
                                
                                // Add space after if not present
                                if (!hasSpaceAfter) {
                                    const spaceAfter = document.createTextNode(' ');
                                    if (nextSiblingAfter) {
                                        saveSpan.parentNode.insertBefore(spaceAfter, nextSiblingAfter);
                                    } else {
                                        saveSpan.parentNode.appendChild(spaceAfter);
                                    }
                                }
                            }
                        });
                        
                        this.templateContent = tempDiv.innerHTML;
                        } catch (error) {
                            console.warn('Error syncing content from TinyMCE:', error);
                        } finally {
                        this.$nextTick(() => {
                            this.isUpdatingContent = false;
                        });
                        }
                    },
                    
                    /**
                     * Update TinyMCE editor content from Vue data model
                     * Converts code tags to field badges for display
                     */
                    updateEditorContent() {
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor) return;
                        
                        // Check if editor is initialized and ready
                        if (!tinyEditor.initialized || !tinyEditor.getBody()) {
                            // Retry after a short delay
                            setTimeout(() => {
                                this.updateEditorContent();
                            }, 100);
                            return;
                        }
                        
                        try {
                        let htmlContent = this.templateContent || '';
                            
                            // If content is empty, just set empty content
                            if (!htmlContent.trim()) {
                                tinyEditor.setContent('');
                                this.attachBadgeDeleteHandlers();
                                return;
                            }
                        
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = htmlContent;
                        
                        const savedFields = tempDiv.querySelectorAll('span[data-template-field]');
                        savedFields.forEach(savedField => {
                            const fieldCode = savedField.getAttribute('data-field');
                            if (!fieldCode) return;
                                
                            const field = this.availableFields.find(f => f.code === fieldCode);
                            const displayText = field ? field.label : fieldCode;
                                
                            const tooltipText = this.clickToDeleteText.replace(':field', displayText);
                            
                            // Create badge element with class
                            const badgeElement = document.createElement('span');
                            badgeElement.setAttribute('class', 'field-badge');
                            badgeElement.setAttribute('data-field', fieldCode);
                            badgeElement.setAttribute('data-delete-field', fieldCode);
                            badgeElement.setAttribute('contenteditable', 'false');
                            badgeElement.setAttribute('spellcheck', 'false');
                            badgeElement.setAttribute('draggable', 'false');
                            badgeElement.setAttribute('title', tooltipText);
                            
                            // Apply saved styles from database (preserve all inline styles except colors)
                            if (savedField.style && savedField.style.cssText) {
                                // Copy styles but remove color-related properties
                                const excludedProperties = ['background', 'background-color', 'color', 'box-shadow'];
                                const savedStyles = savedField.style.cssText.split(';').filter(s => s.trim());
                                const filteredStyles = [];
                                
                                savedStyles.forEach(style => {
                                    if (style.trim()) {
                                        const [prop, value] = style.split(':').map(s => s.trim());
                                        if (prop && value) {
                                            const cssProp = prop.replace(/([A-Z])/g, '-$1').toLowerCase();
                                            // Only include if not a color-related property
                                            if (!excludedProperties.includes(cssProp.toLowerCase())) {
                                                filteredStyles.push(`${cssProp}: ${value}`);
                                            }
                                        }
                                    }
                                });
                                
                                if (filteredStyles.length > 0) {
                                    badgeElement.style.cssText = filteredStyles.join('; ') + ';';
                                }
                            } else {
                                // If no styles saved, apply default styles - only border, no colors
                                const isDark = document.documentElement.classList.contains('dark');
                                const defaultBorder = isDark ? '#6b7280' : '#9ca3af';
                                
                                badgeElement.style.cssText = `display: inline-flex !important; align-items: center !important; gap: 6px !important; border: 1px solid ${defaultBorder} !important; border-radius: 6px !important; padding: 5px 10px !important; margin: 0 2px !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important; font-size: 0.8125rem !important; font-weight: 500 !important; user-select: none !important; cursor: move !important; transition: all 0.15s ease !important; vertical-align: middle !important; line-height: 1.4 !important; position: relative !important;`;
                            }
                            
                            // Check if icon already exists in saved field
                            let existingIcon = savedField.querySelector('.icon-edit');
                            if (!existingIcon) {
                                // Add edit icon if not present
                                const icon = document.createElement('i');
                                icon.className = 'icon-edit';
                                icon.style.cssText = 'font-size: 0.75rem !important; opacity: 0.7 !important; flex-shrink: 0 !important;';
                                badgeElement.insertBefore(icon, badgeElement.firstChild);
                            } else {
                                // Preserve existing icon with its styles
                                const icon = existingIcon.cloneNode(true);
                                if (!icon.style.cssText) {
                                    icon.style.cssText = 'font-size: 0.75rem !important; opacity: 0.7 !important; flex-shrink: 0 !important;';
                                }
                                badgeElement.insertBefore(icon, badgeElement.firstChild);
                            }
                            
                            // Add text content (preserve text from saved field or use displayText)
                            const textNodes = Array.from(savedField.childNodes).filter(node => node.nodeType === 3);
                            const textContent = textNodes.map(node => node.textContent).join('').trim() || displayText;
                            badgeElement.appendChild(document.createTextNode(textContent));
                            
                            if (savedField.parentNode) {
                                savedField.parentNode.replaceChild(badgeElement, savedField);
                            }
                        });
                        
                        htmlContent = tempDiv.innerHTML;
                            
                            // Use setContent with format option to prevent parsing errors
                            tinyEditor.setContent(htmlContent || '', { format: 'html' });
                            
                            this.$nextTick(() => {
                                this.addFieldBadgeStyles(tinyEditor);
                        this.attachBadgeDeleteHandlers();
                            });
                        } catch (error) {
                            console.warn('Error updating editor content:', error);
                            // If error occurs, try again after a delay
                            setTimeout(() => {
                                this.updateEditorContent();
                            }, 200);
                        }
                    },
                    
                    /**
                     * Escape HTML special characters to prevent XSS
                     * @param {string} text - Text to escape
                     * @returns {string} Escaped text
                     */
                    escapeHtml(text) {
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    },
                    
                    /**
                     * Show confirmation modal before deleting all instances of a field
                     * @param {string} fieldCode - The code of the field to delete
                     * @param {string} fieldLabel - The label of the field
                     */
                    confirmDeleteAllInstances(fieldCode, fieldLabel) {
                        this.$emitter.emit('open-confirm-modal', {
                            message: this.confirmDeleteAllInstancesText.replace(':field', fieldLabel),
                            agree: () => {
                                this.deleteField(fieldCode);
                            }
                        });
                    },
                    
                    /**
                     * Delete all instances of a field from the template
                     * @param {string} fieldCode - The code of the field to delete
                     */
                    deleteField(fieldCode) {
                        if (!fieldCode) return;
                        
                        const tinyEditor = this.getTinyMCEEditor();
                        if (!tinyEditor || !tinyEditor.initialized) return;
                        
                        try {
                            this.syncContentFromTinyMCE();
                            
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = this.templateContent || '';
                            
                            const savedFields = tempDiv.querySelectorAll(`span[data-template-field][data-field="${fieldCode}"]`);
                            savedFields.forEach(savedField => {
                                savedField.remove();
                            });
                            
                            this.templateContent = tempDiv.innerHTML;
                            
                            this.$nextTick(() => {
                                this.updateEditorContent();
                            });
                            
                            // Get field label for success message
                            const field = this.availableFields.find(f => f.code === fieldCode);
                            const fieldLabel = field ? field.label : fieldCode;
                            
                            // Show success message
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: this.fieldDeletedSuccessText.replace(':field', fieldLabel)
                            });
                        } catch (error) {
                            console.warn('Error deleting field:', error);
                        }
                    },
                    
                    /**
                     * Sync footer text from TinyMCE editor to Vue data model
                     */
                    syncFooterTextFromTinyMCE() {
                        const footerEditor = this.getFooterTextTinyMCEEditor();
                        if (footerEditor) {
                            this.footerText = footerEditor.getContent();
                        }
                    },

                    /**
                     * Save the document template
                     * Collects all used fields and submits the form
                     */
                    saveTemplate(params, { resetForm, setErrors }) {
                        this.syncContentFromTinyMCE();
                        this.syncFooterTextFromTinyMCE();
                        
                        this.isSaving = true;

                        const usedFields = [];
                        
                        if (this.templateContent) {
                            const tempDiv = document.createElement('div');
                            tempDiv.innerHTML = this.templateContent;
                            
                            const savedFields = tempDiv.querySelectorAll('span[data-template-field]');
                            savedFields.forEach(savedField => {
                                const fieldCode = savedField.getAttribute('data-field');
                                if (fieldCode && !usedFields.includes(fieldCode)) {
                                    usedFields.push(fieldCode);
                                }
                            });
                        }

                        const form = this.$el.querySelector('form');
                        const formData = new FormData(form);
                        
                        // إضافة used_fields كمصفوفة
                        usedFields.forEach(field => {
                            formData.append('used_fields[]', field);
                        });
                        
                        formData.append('template_content', this.templateContent);
                        formData.append('footer_text', this.footerText);
                        formData.append('locale', this.currentLocale.code);

                        const url = this.templateId 
                            ? `/admin/services/document-templates/${this.templateId}`
                            : '{{ route("admin.services.document-templates.store") }}';
                        
                        if (this.templateId) {
                            formData.append('_method', 'PUT');
                        }

                        this.$axios.post(url, formData)
                            .then((response) => {
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: response.data.message
                                });
                                
                                if (!this.templateId && response.data.data?.id) {
                                    window.location.href = `/admin/services/document-templates/${response.data.data.id}/edit`;
                                } else {
                                    window.location.reload();
                                }
                            })
                            .catch(error => {
                                this.isSaving = false;
                                
                                if (error.response?.status === 422) {
                                    setErrors(error.response.data.errors);
                                } else {
                                    this.$emitter.emit('add-flash', {
                                        type: 'error',
                                        message: error.response?.data?.message || this.saveErrorText
                                    });
                                }
                            });
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>
