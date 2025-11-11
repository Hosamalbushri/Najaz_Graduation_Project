<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-groups.create.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.attribute-groups.store')"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.attribute-groups.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.attribute-groups.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.attribute-groups.create.back-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('Admin::app.services.attribute-groups.create.save-btn')
                </button>
            </div>
        </div>

        <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
            <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <div class="mb-4 flex flex-col gap-1">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.attribute-groups.create.labels')
                        </p>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.services.attribute-groups.create.labels-help')
                        </p>
                    </div>

                    <div class="space-y-5">
                        @foreach ($locales as $locale)
                            <div class="grid gap-4 md:grid-cols-2 md:gap-6">
                                <x-admin::form.control-group class="md:col-span-1">
                                    <x-admin::form.control-group.label class="required">
                                        {{ __('Admin::app.services.attribute-groups.create.name') }} ({{ mb_strtoupper($locale->code) }})
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="name[{{ $locale->code }}]"
                                        :value="old('name.' . $locale->code)"
                                        :placeholder="$locale->name"
                                        rules="required"
                                    />

                                    <x-admin::form.control-group.error :control-name="'name.' . $locale->code" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="md:col-span-1">
                                    <x-admin::form.control-group.label>
                                        {{ __('Admin::app.services.attribute-groups.create.description') }} ({{ mb_strtoupper($locale->code) }})
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="textarea"
                                        rows="3"
                                        name="description[{{ $locale->code }}]"
                                        :value="old('description.' . $locale->code)"
                                        :placeholder="$locale->name"
                                    />

                                    <x-admin::form.control-group.error :control-name="'description.' . $locale->code" />
                                </x-admin::form.control-group>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex w-[360px] max-w-full flex-col gap-2">
                <x-admin::accordion>
                    <x-slot:header>
                        <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.attribute-groups.create.general')
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.attribute-groups.create.code')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="code"
                                rules="required"
                                :value="old('code')"
                                placeholder="{{ trans('Admin::app.services.attribute-groups.create.code') }}"
                            />

                            <x-admin::form.control-group.error control-name="code" />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.create.sort-order')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="number"
                                name="sort_order"
                                :value="old('sort_order', 0)"
                                placeholder="{{ trans('Admin::app.services.attribute-groups.create.sort-order') }}"
                            />

                            <x-admin::form.control-group.error control-name="sort_order" />
                        </x-admin::form.control-group>
                    </x-slot:content>
                </x-admin::accordion>
            </div>
        </div>
    </x-admin::form>
</x-admin::layouts>