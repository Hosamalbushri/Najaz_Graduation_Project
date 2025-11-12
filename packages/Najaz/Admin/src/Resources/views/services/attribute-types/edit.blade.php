<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-types.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.attribute-types.update', $attributeType->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.attribute-types.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.attribute-types.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.attribute-types.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('Admin::app.services.attribute-types.edit.save-btn')
                </button>
            </div>
        </div>

        <v-field-type-edit>
            <x-admin::shimmer.catalog.attributes />
        </v-field-type-edit>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-field-type-edit-template">
            <!-- Body Content -->
            <div class="mt-3.5 flex gap-2.5 max-xl:flex-wrap">
                <!-- Left Sub Component -->
                <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                    <!-- Label -->
                    <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                        <p class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.attribute-types.edit.name')
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
                                    :value="old('name.' . $locale->code, $attributeType->translate($locale->code)?->name ?? '')"
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
                                @lang('Admin::app.services.attribute-types.edit.general')
                            </p>
                        </x-slot>
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.attribute-types.edit.code')
                                </x-admin::form.control-group.label>
                                <x-admin::form.control-group.control
                                        type="text"
                                        class="cursor-not-allowed"
                                        name="code"
                                        rules="required"
                                        :value="old('code') ?? $attributeType->code"
                                        disabled="true"
                                        :label="trans('Admin::app.services.attribute-types.edit.code')"
                                        :placeholder="trans('Admin::app.services.attribute-types.edit.code')"
                                />
                                <x-admin::form.control-group.control
                                        type="hidden"
                                        name="code"
                                        :value="$attributeType->code"
                                />

                                <x-admin::form.control-group.error control-name="code" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.attribute-types.edit.type')
                                </x-admin::form.control-group.label>

                                @php
                                    $selectedOption = old('type') ?: $attributeType->type;
                                @endphp
                                <x-admin::form.control-group.control
                                        type="select"
                                        id="type"
                                        class="cursor-not-allowed"
                                        name="type"
                                        rules="required"
                                        :value="$selectedOption"
                                        :disabled="(boolean) $selectedOption"
                                        :label="trans('Admin::app.services.attribute-types.edit.type')"
                                >
                                    @foreach($attributeTypes as $type)
                                        <option
                                                value="{{ $type }}"
                                                {{ $selectedOption == $type ? 'selected' : '' }}
                                        >
                                            @lang('Admin::app.services.attribute-types.options.' . $type)
                                        </option>
                                    @endforeach
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.control
                                        type="hidden"
                                        name="type"
                                        :value="$attributeType->type"
                                />

                                <x-admin::form.control-group.error control-name="type" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.edit.position')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="number"
                                    name="position"
                                    v-model.number="position"
                                    :label="trans('Admin::app.services.attribute-types.edit.position')"
                                    min="0"
                                />

                                <x-admin::form.control-group.error control-name="position" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.edit.default-value')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="default_value"
                                    v-model="defaultValue"
                                    :label="trans('Admin::app.services.attribute-types.edit.default-value')"
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
                                        @lang('Admin::app.services.attribute-types.edit.is-required')
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
                                        @lang('Admin::app.services.attribute-types.edit.is-unique')
                                    </span>
                                </div>
                            </div>
                        </x-slot:content>
                    </x-admin::accordion>

                    <x-admin::accordion>
                        <x-slot:header>
                            <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.attribute-types.edit.validation')
                            </p>
                        </x-slot:header>

                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-types.edit.validation')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="validation"
                                    v-model="validationType"
                                    :label="trans('Admin::app.services.attribute-types.edit.validation')"
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
                                    @lang('Admin::app.services.attribute-types.edit.regex')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="regex"
                                    v-model="regex"
                                    :label="trans('Admin::app.services.attribute-types.edit.regex')"
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
            app.component('v-field-type-edit', {
                template: '#v-field-type-edit-template',
                data() {
                    return {
                        validationType: @json(old('validation', $attributeType->validation)),
                        regex: @json(old('regex', $attributeType->regex)),
                        position: @json(old('position', $attributeType->position)),
                        defaultValue: @json(old('default_value', $attributeType->default_value)),
                        isRequired: {{ old('is_required', $attributeType->is_required) ? 'true' : 'false' }},
                        isUnique: {{ old('is_unique', $attributeType->is_unique) ? 'true' : 'false' }},
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

