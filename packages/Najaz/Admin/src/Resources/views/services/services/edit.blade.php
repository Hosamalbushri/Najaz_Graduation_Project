<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.edit.title')
    </x-slot>

    @php
        $currentLocale = core()->getRequestedLocale();
        $selectedCitizenTypeIds = array_map(
            'strval',
            old('citizen_type_ids', $service->citizenTypes->pluck('id')->toArray())
        );
        $citizenTypeTree = \Najaz\Service\Repositories\ServiceRepository::getCitizenTypeTree();
    @endphp

    {!! view_render_event('bagisto.admin.services.edit.before', ['service' => $service]) !!}

    <!-- Service Edit Form -->
    <x-admin::form
            :action="route('admin.services.update', $service->id)"
            enctype="multipart/form-data"
            method="PUT"
    >
        {!! view_render_event('bagisto.admin.services.edit.edit_form_controls.before', ['service' => $service]) !!}

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.services.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                        href="{{ route('admin.services.index') }}"
                        class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.services.edit.cancel-btn')
                </a>

                <!-- Save Button -->
                <button
                        type="submit"
                        class="primary-button"
                >
                    @lang('Admin::app.services.services.edit.save-btn')
                </button>
            </div>
        </div>

        <!-- Locale Switcher -->
        <div class="mt-7 flex items-center justify-between gap-4 max-md:flex-wrap">
            <div class="flex items-center gap-x-1">
                <x-admin::dropdown
                        position="bottom-{{ core()->getCurrentLocale()->direction === 'ltr' ? 'left' : 'right' }}"
                        :class="core()->getAllLocales()->count() <= 1 ? 'hidden' : ''"
                >
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
                        @foreach (core()->getAllLocales() as $locale)
                            <a
                                    href="?{{ Arr::query(['locale' => $locale->code]) }}"
                                    class="flex gap-2.5 px-5 py-2 text-base cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-950 dark:text-white {{ $locale->code == $currentLocale->code ? 'bg-gray-100 dark:bg-gray-950' : ''}}"
                            >
                                {{ $locale->name }}
                            </a>
                        @endforeach
                    </x-slot>
                </x-admin::dropdown>
            </div>
        </div>

        {!! view_render_event('bagisto.admin.services.edit.edit_form_controls.after', ['service' => $service]) !!}

        <!-- Full Panel -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Section -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <!-- General -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.edit.general')
                    </p>

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('Admin::app.services.services.edit.name')
                        </x-admin::form.control-group.label>

                        <v-field
                                type="text"
                                name="{{ $currentLocale->code }}[name]"
                                value="{{ old($currentLocale->code)['name'] ?? ($service->translate($currentLocale->code)['name'] ?? '') }}"
                                label="{{ trans('Admin::app.services.services.edit.name') }}"
                                rules="required"
                                v-slot="{ field, errors }"
                        >
                            <input
                                    type="text"
                                    name="{{ $currentLocale->code }}[name]"
                                    id="{{ $currentLocale->code }}[name]"
                                    v-bind="field"
                                    :class="[errors.length ? 'border border-red-600 hover:border-red-600' : '']"
                                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                    placeholder="{{ trans('Admin::app.services.services.edit.name') }}"
                            />
                        </v-field>

                        <x-admin::form.control-group.error :control-name="$currentLocale->code . '[name]'"/>
                    </x-admin::form.control-group>

                    <!-- Status -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.services.edit.status')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                                type="hidden"
                                name="status"
                                value="0"
                        />

                        <x-admin::form.control-group.control
                                type="switch"
                                name="status"
                                value="1"
                                :checked="old('status', $service->status)"
                                :label="trans('Admin::app.services.services.edit.status')"
                        />

                        <x-admin::form.control-group.error control-name="status"/>
                    </x-admin::form.control-group>

                    <!-- Sort Order -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.services.edit.sort-order')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                                type="number"
                                name="sort_order"
                                value="{{ old('sort_order', $service->sort_order) }}"
                                min="0"
                                :label="trans('Admin::app.services.services.edit.sort-order')"
                        />

                        <x-admin::form.control-group.error control-name="sort_order"/>
                    </x-admin::form.control-group>
                </div>

                <!-- Content -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.edit.content')
                    </p>

                    <!-- Description -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.services.edit.description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                                type="textarea"
                                id="description"
                                :name="$currentLocale->code . '[description]'"
                                :value="old($currentLocale->code)['description'] ?? ($service->translate($currentLocale->code)['description'] ?? '')"
                                :label="trans('Admin::app.services.services.edit.description')"
                                :placeholder="trans('Admin::app.services.services.edit.description')"
                                :tinymce="true"
                        />

                        <x-admin::form.control-group.error :control-name="$currentLocale->code . '[description]'"/>
                    </x-admin::form.control-group>
                </div>

                @include('admin::services.services.service-filed-groups.index', [
                    'service' => $service,
                ])
                {{--                @include('admin::services.service-field-manager.index', [--}}
                {{--                    'serviceId' => $service->id,--}}
                {{--                    'availableGroups' => $attributeGroupOptions ?? [],--}}
                {{--                ])--}}
            </div>

            <!-- Right Section -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-xl:flex-auto max-xl:w-full">
                <!-- Associations -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.edit.associations')
                    </p>

                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.services.edit.citizen-types')
                        </x-admin::form.control-group.label>

                        <x-admin::tree.view
                                input-type="checkbox"
                                selection-type="individual"
                                name-field="citizen_type_ids"
                                value-field="id"
                                id-field="id"
                                :items="json_encode($citizenTypeTree)"
                                :value="json_encode($selectedCitizenTypeIds)"
                                :fallback-locale="config('app.fallback_locale')"
                        />

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.services.services.edit.citizen-types-help')
                        </p>

                        <x-admin::form.control-group.error control-name="citizen_type_ids"/>
                    </x-admin::form.control-group>
                </div>

                <!-- Media -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.services.edit.media')
                    </p>

                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.services.edit.image')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                                type="image"
                                name="image"
                                value="{{ old('image', $service->image) }}"
                                placeholder="{{ trans('Admin::app.services.services.edit.image-placeholder') }}"
                                :label="trans('Admin::app.services.services.edit.image')"
                        />

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.services.services.edit.image-help')
                        </p>

                        <x-admin::form.control-group.error control-name="image"/>
                    </x-admin::form.control-group>
                </div>
            </div>
        </div>

        {!! view_render_event('bagisto.admin.services.edit.edit_form_controls.after', ['service' => $service]) !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.services.edit.after', ['service' => $service]) !!}

</x-admin::layouts>
