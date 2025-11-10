<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.data-groups.create.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.data-groups.store')"
        enctype="multipart/form-data"
    >

        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.data-groups.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
{{--                    href="{{ route('admin.catalog.attributes.index') }}"--}}
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
{{--                    @lang('admin::app.catalog.attributes.create.back-btn')--}}
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('admin::app.catalog.attributes.create.save-btn')
                </button>
            </div>
        </div>
        <v-data-group-create>
            <x-admin::shimmer.catalog.attributes />

        </v-data-group-create>

    </x-admin::form>


    @pushOnce('scripts')
        <script
            type="text/x-template"
                id="v-data-group-create-template">
            <!-- Body Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Sub Component -->
                <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                    <!-- Label -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('admin::app.catalog.attributes.create.label')
                        </p>
                    <!-- Locales Inputs -->
                    @foreach ($locales as $locale)
                        <x-admin::form.control-group class="last:!mb-0">
                            <x-admin::form.control-group.label>
                                {{ $locale->name . ' (' . strtoupper($locale->code) . ')' }}
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                :name="$locale->code . '[name]'"
                                :value="old($locale->code . '[name]')"
                                :placeholder="$locale->name"
                            />
                        </x-admin::form.control-group>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>

                                </x-admin::form.control-group.label>
                                {{ 'Description'.$locale->name . ' (' . strtoupper($locale->code) . ')' }}
                                <x-admin::form.control-group.control
                                    type="textarea"
                                    :name="$locale->code . '[description]'"
                                    :value="old($locale->code . '[description]')"
                                    label="Description"
                                />

                                <x-admin::form.control-group.error control-name="description" />
                            </x-admin::form.control-group>
                    @endforeach
                    </div>
                </div>

                <div class="flex w-[360px] max-w-full flex-col gap-2">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('admin::app.catalog.attributes.create.general')
                            </p>
                        </x-slot>
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('admin::app.catalog.attributes.create.code')
                                </x-admin::form.control-group.label>

                                <v-field
                                    type="text"
                                    name="code"
                                    rules="required"
                                    value="{{ old('code') }}"
                                    v-slot="{ field }"
                                    label="{{ trans('admin::app.catalog.attributes.create.code') }}"
                                >
                                    <input
                                        type="text"
                                        id="code"
                                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        name="code"
                                        v-bind="field"
                                        placeholder="{{ trans('admin::app.catalog.attributes.create.code') }}"
                                    />
                                </v-field>

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>


                        </x-slot:content>


                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-data-group-create', {
                template: '#v-data-group-create-template',
            });
        </script>
    @endPushOnce
</x-admin::layouts>




