<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.channels.create.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.channels.create.before') !!}

    <x-admin::form
        action="{{ route('admin.settings.channels.store') }}"
        enctype="multipart/form-data"
    >

        {!! view_render_event('admin.settings.channels.create.create_form_controls.before') !!}

        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.channels.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.settings.channels.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.settings.channels.create.cancel')
                </a>

                <!-- Save Button -->
                <button 
                    type="submit" 
                    class="primary-button"
                >
                    @lang('admin::app.settings.channels.create.save-btn')
                </button>
            </div>
        </div>

        <!-- body content -->
        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left sub-component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                {!! view_render_event('bagisto.admin.settings.channels.create.card.general.before') !!}

                <!-- General Information -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.channels.create.general')
                    </p>

                    <!-- Code -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.channels.create.code')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="code"
                            name="code"
                            rules="required"
                            :value="old('code')"
                            :label="trans('admin::app.settings.channels.create.code')"
                            :placeholder="trans('admin::app.settings.channels.create.code')"
                        />

                        <x-admin::form.control-group.error control-name="code" />
                    </x-admin::form.control-group>

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.channels.create.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="name"
                            name="name"
                            rules="required"
                            :value="old('name')"
                            :label="trans('admin::app.settings.channels.create.name')"
                            :placeholder="trans('admin::app.settings.channels.create.name')"
                        />

                        <x-admin::form.control-group.error control-name="name" />
                    </x-admin::form.control-group>

                    <!-- Description -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.channels.create.description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            id="description"
                            name="description"
                            :value="old('description')"
                            :label="trans('admin::app.settings.channels.create.description')"
                            :placeholder="trans('admin::app.settings.channels.create.description')"
                        />

                        <x-admin::form.control-group.error control-name="description" />
                    </x-admin::form.control-group>


                    <!-- Host Name -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.channels.create.hostname')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="hostname"
                            name="hostname"
                            :value="old('hostname')"
                            :label="trans('admin::app.settings.channels.create.hostname')"
                            :placeholder="trans('admin::app.settings.channels.create.hostname-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="hostname" />
                    </x-admin::form.control-group>
                </div>

                {!! view_render_event('bagisto.admin.settings.channels.create.card.general.after') !!}


            </div>

            <!-- Right sub-component -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

                {!! view_render_event('bagisto.admin.settings.channels.create.card.accordion.currencies_and_locales.before') !!}

                <!-- Currencies and Locales -->
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            {{ trans('admin::app.settings.channels.create.currencies-and-locales') }}
                        </p>
                    </x-slot>
            
                    <x-slot:content>
                        <!-- Locale Checkboxes  -->
                        <div class="mb-4">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.channels.create.locales')
                            </x-admin::form.control-group.label>
                        
                            @foreach (core()->getAllLocales() as $locale)
                                <x-admin::form.control-group class="!mb-2 flex items-center gap-2.5">
                                    <x-admin::form.control-group.control
                                        type="checkbox"
                                        :id="'locales_' . $locale->id"
                                        name="locales[]"
                                        rules="required"
                                        :value="$locale->id"
                                        :for="'locales_' . $locale->id"
                                        :label="trans('admin::app.settings.channels.create.locales')"
                                    />

                                    <label
                                        class="cursor-pointer text-xs font-medium text-gray-600 dark:text-gray-300"
                                        for="locales_{{ $locale->id }}"
                                    >
                                        {{ $locale->name }} 
                                    </label>
                                </x-admin::form.control-group>
                            @endforeach

                            <x-admin::form.control-group.error control-name="locales[]" />
                        </div>

                        <!-- Default Locale Selector -->
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.channels.create.default-locale')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                id="default_locale_id"
                                name="default_locale_id"
                                rules="required"
                                :value="old('default_locale_id')"
                                :label="trans('admin::app.settings.channels.create.default-locale')"
                            >
                                <!-- Default Option -->
                                <option value="">
                                    @lang('admin::app.settings.channels.create.select-default-locale')
                                </option>

                                @foreach (core()->getAllLocales() as $locale)
                                    <option value="{{ $locale->id }}" {{ old('default_locale_id') == $locale->id ? 'selected' : '' }}>
                                        {{ $locale->name }}
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="default_locale_id" />
                        </x-admin::form.control-group>

                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.settings.channels.create.card.accordion.currencies_and_locales.after') !!}

            </div>
        </div>

        {!! view_render_event('admin.settings.channels.create.create_form_controls.after') !!}

    </x-admin::form> 

    {!! view_render_event('bagisto.admin.settings.channels.create.after') !!}
</x-admin::layouts>
