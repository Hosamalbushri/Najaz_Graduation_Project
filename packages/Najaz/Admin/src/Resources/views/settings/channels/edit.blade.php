@php
    $locale = core()->getRequestedLocaleCode();

    $seo = $channel->translate($locale)['home_seo'] ?? $channel->home_seo;
@endphp

<x-admin::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('admin::app.settings.channels.edit.title')
    </x-slot>

    {!! view_render_event('bagisto.admin.settings.channels.edit.before', ['channel' => $channel]) !!}

    <!-- Channel Id Edit Form -->
    <x-admin::form  
        :action="route('admin.settings.channels.update', ['id' => $channel->id, 'locale' => $locale])"
        enctype="multipart/form-data"
    >
        @method('PUT')

        {!! view_render_event('bagisto.admin.settings.channels.edit.edit_form_controls.before', ['channel' => $channel]) !!}

        <div class="flex items-center justify-between">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('admin::app.settings.channels.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.settings.channels.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('admin::app.settings.channels.edit.back-btn')
                </a>

                <button 
                    type="submit" 
                    class="primary-button"
                    aria-label="Submit"
                >
                    @lang('admin::app.settings.channels.edit.save-btn')
                </button>
            </div>
        </div>

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <!-- Left Component -->
            <div class="flex flex-1 flex-col gap-2 max-xl:flex-auto">

                {!! view_render_event('bagisto.admin.settings.channels.edit.card.general.before', ['channel' => $channel]) !!}

                <!-- General Information -->
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                        @lang('admin::app.settings.channels.edit.general')
                    </p>

                    <!-- Code -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.channels.edit.code')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="code"
                            name="code"
                            rules="required"
                            :value="old('code') ?? $channel->code"
                            :label="trans('admin::app.settings.channels.edit.code')"
                            :placeholder="trans('admin::app.settings.channels.edit.code')"
                            disabled="disabled"
                        />

                        <input
                            type="hidden"
                            name="code"
                            value="{{ $channel->code }}"
                        />
                    
                        <x-admin::form.control-group.error control-name="code" />
                    </x-admin::form.control-group>

                    <!-- Name -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('admin::app.settings.channels.edit.name')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            :id="$locale . '[name]'"
                            :name="$locale . '[name]'"
                            rules="required"
                            :value="old('name') ?? $channel->name"
                            :label="trans('admin::app.settings.channels.edit.name')"
                            :placeholder="trans('admin::app.settings.channels.edit.name')"
                        />

                        <x-admin::form.control-group.error :control-name="$locale . '[name]'" />
                    </x-admin::form.control-group>

                    <!-- Description -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.channels.edit.description')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="textarea"
                            :id="$locale . '[description]'"
                            :name="$locale . '[description]'"
                            :value="old('description') ?? $channel->description"
                            :label="trans('admin::app.settings.channels.edit.description')"
                            :placeholder="trans('admin::app.settings.channels.edit.description')"
                        />

                        <x-admin::form.control-group.error control-name="$locale . '[description]'" />
                    </x-admin::form.control-group>


                    <!-- Host Name -->
                    <x-admin::form.control-group class="!mb-0">
                        <x-admin::form.control-group.label>
                            @lang('admin::app.settings.channels.edit.hostname')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            id="hostname"
                            name="hostname"
                            :value="old('hostname') ?? $channel->hostname"
                            :label="trans('admin::app.settings.channels.edit.hostname')"
                            :placeholder="trans('admin::app.settings.channels.edit.hostname-placeholder')"
                        />

                        <x-admin::form.control-group.error control-name="hostname" />
                    </x-admin::form.control-group>
                </div>

                {!! view_render_event('bagisto.admin.settings.channels.edit.card.general.after', ['channel' => $channel]) !!}


            </div>

            <!-- Right Component -->
            <div class="flex w-[360px] max-w-full flex-col gap-2 max-sm:w-full">

                {!! view_render_event('bagisto.admin.settings.channels.edit.card.accordion.currencies_and_locales.before', ['channel' => $channel]) !!}

                <!-- Currencies and Locale -->
                <x-admin::accordion>
                    <x-slot:header>
                        <div class="flex items-center justify-between">
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                {{ trans('admin::app.settings.channels.edit.currencies-and-locales') }}
                            </p>
                        </div>
                    </x-slot>
            
                    <x-slot:content>
                        <!-- Locales Checkboxes -->
                        <div class="mb-4">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.channels.edit.locales') 
                            </x-admin::form.control-group.label>

                            @php $selectedLocalesId = old('locales') ?? $channel->locales->pluck('id')->toArray(); @endphp
                            
                            @foreach (core()->getAllLocales() as $locale)
                                <x-admin::form.control-group class="!mb-2 flex items-center gap-2.5">
                                    <x-admin::form.control-group.control
                                        type="checkbox"
                                        :id="'locales_' . $locale->id" 
                                        name="locales[]"
                                        rules="required"
                                        :value="$locale->id"
                                        :for="'locales_' . $locale->id" 
                                        :label="trans('admin::app.settings.channels.edit.locales')"
                                        :checked="in_array($locale->id, $selectedLocalesId)"
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
                        <x-admin::form.control-group class="mb-4">
                            <x-admin::form.control-group.label class="required">
                                @lang('admin::app.settings.channels.edit.default-locale')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                id="default_locale_id"
                                name="default_locale_id"
                                rules="required"
                                :value="old('default_locale_id') ?? $channel->default_locale_id"
                                :label="trans('admin::app.settings.channels.edit.default-locale')"
                            >
                                @foreach (core()->getAllLocales() as $locale)
                                    <option value="{{ $locale->id }}">
                                        {{ $locale->name }}
                                    </option>
                                @endforeach
                            </x-admin::form.control-group.control>

                            <x-admin::form.control-group.error control-name="default_locale_id" />
                        </x-admin::form.control-group>

                    </x-slot>
                </x-admin::accordion>

                {!! view_render_event('bagisto.admin.settings.channels.edit.card.accordion.currencies_and_locales.after', ['channel' => $channel]) !!}

            </div>
        </div>

        {!! view_render_event('bagisto.admin.settings.channels.edit.edit_form_controls.after', ['channel' => $channel]) !!}

    </x-admin::form> 

    {!! view_render_event('bagisto.admin.settings.channels.edit.after', ['channel' => $channel]) !!}

</x-admin::layouts>
