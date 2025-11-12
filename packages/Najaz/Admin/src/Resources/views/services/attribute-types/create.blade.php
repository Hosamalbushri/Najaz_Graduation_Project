<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-types.create.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.attribute-types.store')"
        enctype="multipart/form-data"
    >
        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.attribute-types.create.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.attribute-types.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.attribute-types.create.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('Admin::app.services.attribute-types.create.save-btn')
                </button>
            </div>
        </div>

        <v-field-type-create>
            <x-admin::shimmer.catalog.attributes />
        </v-field-type-create>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-field-type-create-template">
            <!-- Body Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Sub Component -->
                <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                    <!-- Label -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.attribute-types.create.name')
                        </p>
                        <!-- Locales Inputs -->
                        @foreach ($locales as $locale)
                            <x-admin::form.control-group class="last:!mb-0">
                                <x-admin::form.control-group.label>
                                    {{ $locale->name . ' (' . strtoupper($locale->code) . ')' }}
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    :name="'name[' . $locale->code . ']'"
                                    :value="old('name.' . $locale->code)"
                                    :placeholder="$locale->name"
                                />
                            </x-admin::form.control-group>
                        @endforeach
                    </div>
                </div>

                <div class="flex w-[360px] max-w-full flex-col gap-2">
                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.attribute-types.create.general')
                            </p>
                        </x-slot>
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.attribute-types.create.code')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                        type="text"
                                        name="code"
                                        rules="required"
                                        :value="old('code')"
                                        :label="trans('Admin::app.services.attribute-types.create.code')"
                                        :placeholder="trans('Admin::app.services.attribute-types.create.code')"
                                />

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.attribute-types.create.type')
                                </x-admin::form.control-group.label>

                                <v-field
                                    type="select"
                                    name="type"
                                    rules="required"
                                    value="{{ old('type') }}"
                                    v-slot="{ field }"
                                    label="{{ trans('Admin::app.services.attribute-types.create.type') }}"
                                >
                                    <select
                                        id="type"
                                        class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        name="type"
                                        v-bind="field"
                                    >
                                        <option value="">@lang('Admin::app.services.attribute-types.create.select-type')</option>
                                        @foreach ($attributeTypes as $type)
                                            <option
                                                value="{{ $type }}"
                                                @selected(old('type') === $type)
                                            >
                                                @lang('Admin::app.services.attribute-types.options.' . $type)
                                            </option>
                                        @endforeach
                                    </select>
                                </v-field>

                                <x-admin::form.control-group.error control-name="type" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.create.position')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="number"
                                    name="position"
                                    v-model.number="position"
                                    :label="trans('Admin::app.services.attribute-types.create.position')"
                                    min="0"
                                />

                                <x-admin::form.control-group.error control-name="position" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.create.default-value')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="default_value"
                                    v-model="defaultValue"
                                    :label="trans('Admin::app.services.attribute-types.create.default-value')"
                                />

                                <x-admin::form.control-group.error control-name="default_value" />
                            </x-admin::form.control-group>

                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <input type="hidden" name="is_required" value="0" />

                                    <x-admin::form.control-group.control
                                        type="switch"
                                        name="is_required"
                                        value="1"
                                        ::checked="isRequired"
                                        @change="isRequired = $event.target.checked"
                                    />

                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.services.attribute-types.create.is-required')
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="hidden" name="is_unique" value="0" />

                                    <x-admin::form.control-group.control
                                        type="switch"
                                        name="is_unique"
                                        value="1"
                                        ::checked="isUnique"
                                        @change="isUnique = $event.target.checked"
                                    />

                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        @lang('Admin::app.services.attribute-types.create.is-unique')
                                    </span>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-admin::accordion>

                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.attribute-types.create.validation')
                            </p>
                        </x-slot:header>

                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.create.validation')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="validation"
                                    v-model="validationType"
                                    :label="trans('Admin::app.services.attribute-types.create.validation')"
                                >
                                    <option value="">
                                        @lang('Admin::app.services.attribute-types.create.select-validation')
                                    </option>

                                    @foreach ($validations as $validation)
                                        <option value="{{ $validation }}">
                                            @lang('Admin::app.services.attribute-types.validation-options.' . $validation)
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="validation" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group v-if="validationType === 'regex'">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.create.regex')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="regex"
                                    v-model="regex"
                                    :label="trans('Admin::app.services.attribute-types.create.regex')"
                                    placeholder="/^[0-9]+$/"
                                />

                                <x-admin::form.control-group.error control-name="regex" />
                            </x-admin::form.control-group>
                        </x-slot:content>
                    </x-admin::accordion>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-field-type-create', {
                template: '#v-field-type-create-template',
                data() {
                    return {
                        validationType: @json(old('validation')),
                        regex: @json(old('regex')),
                        position: @json(old('position')),
                        defaultValue: @json(old('default_value')),
                        isRequired: {{ old('is_required') ? 'true' : 'false' }},
                        isUnique: {{ old('is_unique') ? 'true' : 'false' }},
                    }
                },
                watch: {
                    validationType(value) {
                        if (value !== 'regex') {
                            this.regex = '';
                        }
                    },
                },
            });
        </script>
    @endPushOnce
</x-admin::layouts>

