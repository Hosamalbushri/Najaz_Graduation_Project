<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-groups.edit.title')
    </x-slot>

    @php
        $currentLocale = core()->getRequestedLocale();
    @endphp

    {!! view_render_event('bagisto.admin.attribute-groups.edit.before', ['attributeGroup' => $attributeGroup]) !!}

    <!-- Attribute Group Edit Form -->
    <x-admin::form
        :action="route('admin.attribute-groups.update', $attributeGroup->id)"
        enctype="multipart/form-data"
        method="PUT"
    >
        {!! view_render_event('bagisto.admin.attribute-groups.edit.edit_form_controls.before', ['attributeGroup' => $attributeGroup]) !!}

        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.attribute-groups.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.attribute-groups.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.attribute-groups.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('Admin::app.services.attribute-groups.edit.save-btn')
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

        {!! view_render_event('bagisto.admin.attribute-groups.edit.edit_form_controls.after', ['attributeGroup' => $attributeGroup]) !!}

        <!-- Full Panel -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Section -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">
                <!-- General -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.attribute-groups.edit.general')
                    </p>

                    <!-- Default Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('Admin::app.services.attribute-groups.edit.default-name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="default_name"
                            :value="old('default_name', $attributeGroup->default_name)"
                            :label="trans('Admin::app.services.attribute-groups.edit.default-name')"
                            :placeholder="trans('Admin::app.services.attribute-groups.edit.default-name')"
                        />

                        <x-admin::form.control-group.error control-name="default_name" />
                    </x-admin::form.control-group>

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('Admin::app.services.attribute-groups.edit.name')
                            <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600">
                                {{ $currentLocale->name }}
                            </span>
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="{{ $currentLocale->code }}[name]"
                            :value="old($currentLocale->code)['name'] ?? ($attributeGroup->translate($currentLocale->code)?->name ?? '')"
                            :placeholder="$currentLocale->name"
                        />

                        <x-admin::form.control-group.error :control-name="$currentLocale->code . '[name]'" />
                    </x-admin::form.control-group>

                    <!-- Description -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.attribute-groups.edit.description')
                            <span class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-semibold leading-normal text-gray-600">
                                {{ $currentLocale->name }}
                            </span>
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            name="{{ $currentLocale->code }}[description]"
                            :value="old($currentLocale->code)['description'] ?? ($attributeGroup->translate($currentLocale->code)?->description ?? '')"
                        />

                        <x-admin::form.control-group.error :control-name="$currentLocale->code . '[description]'" />
                    </x-admin::form.control-group>
                </div>

                <!-- Fields Manager Component -->
                @include('admin::services.attribute-groups.fields-manager', [
                    'groupId' => $attributeGroup->id,
                    'groupType' => $attributeGroup->group_type,
                    'attributeTypes' => $attributeTypes,
                    'validations' => $validations,
                    'validationLabels' => $validationLabels,
                    'initialFields' => $initialFields ?? [],
                ])
            </div>

            <!-- Right Section -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-xl:flex-auto max-xl:w-full">
                <!-- General Info -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.attribute-groups.edit.general')
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <!-- Code -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.edit.code')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="code"
                                :value="$attributeGroup->code"
                            />

                            <x-admin::form.control-group.control
                                type="text"
                                name="code_display"
                                class="cursor-not-allowed"
                                disabled="true"
                                :value="old('code', $attributeGroup->code)"
                                :placeholder="trans('Admin::app.services.attribute-groups.edit.code')"
                                readonly
                            />

                            <x-admin::form.control-group.error control-name="code" />
                        </x-admin::form.control-group>

                        <!-- Group Type -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.edit.group-type')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="hidden"
                                name="group_type"
                                :value="$attributeGroup->group_type"
                            />

                            <x-admin::form.control-group.control
                                type="select"
                                name="group_type_display"
                                class="cursor-not-allowed"
                                disabled="true"
                                readonly
                                :value="old('group_type', $attributeGroup->group_type ?? 'general')"
                                :label="trans('Admin::app.services.attribute-groups.edit.group-type')"
                            >
                                <option value="general" @selected(old('group_type', $attributeGroup->group_type ?? 'general') === 'general')>
                                    @lang('Admin::app.services.attribute-groups.options.group-type.general')
                                </option>

                                <option value="citizen" @selected(old('group_type', $attributeGroup->group_type ?? 'general') === 'citizen')>
                                    @lang('Admin::app.services.attribute-groups.options.group-type.citizen')
                                </option>
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="group_type" />

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @lang('Admin::app.services.attribute-groups.edit.group-type-help')
                            </p>
                        </x-admin::form.control-group>

                        <!-- Sort Order -->
                        <x-admin::form.control-group class="!mb-0">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.edit.sort-order')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="number"
                                name="sort_order"
                                :value="old('sort_order', $attributeGroup->sort_order)"
                                :placeholder="trans('Admin::app.services.attribute-groups.edit.sort-order')"
                            />

                            <x-admin::form.control-group.error control-name="sort_order" />
                        </x-admin::form.control-group>
                    </x-slot:content>
                </x-admin::accordion>
            </div>
        </div>

        {!! view_render_event('bagisto.admin.attribute-groups.edit.edit_form_controls.after', ['attributeGroup' => $attributeGroup]) !!}

    </x-admin::form>

    {!! view_render_event('bagisto.admin.attribute-groups.edit.after', ['attributeGroup' => $attributeGroup]) !!}

</x-admin::layouts>
