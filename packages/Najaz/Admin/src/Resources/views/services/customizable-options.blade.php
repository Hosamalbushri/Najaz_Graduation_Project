@php
    $currentLocale = core()->getRequestedLocale();
    $options = $service->id ? $service->customizable_options()->get() : collect([]);
@endphp

{!! view_render_event('bagisto.admin.services.edit.form.customizable-options.before', ['service' => $service]) !!}

<v-service-customizable-options :errors="errors"></v-service-customizable-options>

{!! view_render_event('bagisto.admin.services.edit.form.customizable-options.after', ['service' => $service]) !!}

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-customizable-options-template"
    >
        <div class="box-shadow relative rounded bg-white dark:bg-gray-900 mt-4">
            <!-- Option Panel Header -->
            <div class="p-4 flex flex-col mb-2.5">
                <div class="flex justify-between gap-5">
                    <!-- Option Title & Option Info -->
                    <div class="flex flex-col gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.services.customizable-options.title')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('Admin::app.services.services.customizable-options.info')
                        </p>
                    </div>

                    <!-- Add Option Button -->
                    <div class="flex items-center gap-x-1">
                        <div
                            class="secondary-button"
                            @click="resetForm(); $refs.updateCreateOptionModal.open()"
                        >
                            @lang('Admin::app.services.services.customizable-options.add-btn')
                        </div>
                    </div>
                </div>

                <!-- Backend Validations -->
                <x-admin::form.control-group.error control-name="customizable_options" />
            </div>

            <!-- Option Panel Content -->
            <div
                class="grid"
                v-if="options.length"
            >
                <draggable
                    ghost-class="draggable-ghost"
                    v-bind="{ animation: 200 }"
                    handle=".icon-drag"
                    :list="options"
                    item-key="id"
                >
                    <template #item="{ element, index }">
                        <div>
                            <!-- Hidden Attributes -->
                            <input
                                type="hidden"
                                :name="'customizable_options[' + element.id + '][{{$currentLocale->code}}][label]'"
                                :value="element.label"
                            />

                            <input
                                type="hidden"
                                :name="'customizable_options[' + element.id + '][type]'"
                                :value="element.type"
                            />

                            <input
                                type="hidden"
                                :name="'customizable_options[' + element.id + '][is_required]'"
                                :value="element.is_required"
                            />

                            <input
                                type="hidden"
                                :name="'customizable_options[' + element.id + '][sort_order]'"
                                :value="index"
                            />

                            <input
                                type="hidden"
                                :name="'customizable_options[' + element.id + '][max_characters]'"
                                :value="element.max_characters"
                                v-if="['text', 'textarea'].includes(element.type)"
                            />

                            <input
                                type="hidden"
                                :name="'customizable_options[' + element.id + '][supported_file_extensions]'"
                                :value="element.supported_file_extensions"
                                v-if="element.type == 'file'"
                            />

                            <!-- Option Display -->
                            <div class="mb-2.5 flex justify-between gap-5 p-4">
                                <!-- Option Information -->
                                <div class="flex gap-2.5">
                                    <i class="icon-drag cursor-grab text-xl transition-all hover:text-gray-700 dark:text-gray-300"></i>

                                    <p
                                        class="text-base font-semibold text-gray-800 dark:text-white"
                                        :class="{'required': element.is_required == 1}"
                                    >
                                        @{{ (index + 1) + '. ' + element.label + ' - ' + types[element.type].title }}
                                    </p>
                                </div>

                                <!-- Option Action Buttons -->
                                <div class="flex gap-2">
                                    <!-- Edit Option -->
                                    <p
                                        class="cursor-pointer text-blue-600 transition-all hover:underline"
                                        @click="selectedOption = element; $refs.updateCreateOptionModal.open()"
                                    >
                                        @lang('Admin::app.services.services.customizable-options.option.edit-btn')
                                    </p>

                                    <!-- Remove Option -->
                                    <p
                                        class="cursor-pointer text-red-600 transition-all hover:underline"
                                        @click="removeOption(element)"
                                    >
                                        @lang('Admin::app.services.services.customizable-options.option.delete-btn')
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </draggable>
            </div>

            <!-- For Empty Option -->
            <div
                class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10"
                v-else
            >
                <!-- Placeholder Image -->
                <img
                    src="{{ bagisto_asset('images/icon-options.svg') }}"
                    class="h-20 w-20 rounded border border-dashed dark:border-gray-800 dark:mix-blend-exclusion dark:invert"
                />

                <!-- Add Information -->
                <div class="flex flex-col items-center gap-1.5">
                    <p class="text-base font-semibold text-gray-400">
                        @lang('Admin::app.services.services.customizable-options.empty-title')
                    </p>

                    <p class="text-gray-400">
                        @lang('Admin::app.services.services.customizable-options.empty-info')
                    </p>
                </div>

                <!-- Update Create Option Item Modal -->
                <div
                    class="secondary-button text-sm"
                    @click="resetForm(); $refs.updateCreateOptionModal.open()"
                >
                    @lang('Admin::app.services.services.customizable-options.add-btn')
                </div>
            </div>

            <!-- Add Option Form Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, updateOrCreate)">
                    <x-admin::modal ref="updateCreateOptionModal">
                        <!-- Option Form Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.services.customizable-options.update-create.title')
                            </p>
                        </x-slot>

                        <!-- Option Form Modal Content -->
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.customizable-options.update-create.name')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="label"
                                    rules="required"
                                    ::value="selectedOption.label"
                                    :label="trans('Admin::app.services.services.customizable-options.update-create.name')"
                                />

                                <x-admin::form.control-group.error control-name="label" />
                            </x-admin::form.control-group>

                            <div class="flex gap-4">
                                <x-admin::form.control-group class="flex-1">
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.services.customizable-options.update-create.type')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="type"
                                        rules="required"
                                        v-model="selectedOption.type"
                                        :label="trans('Admin::app.services.services.customizable-options.update-create.type')"
                                    >
                                        <option
                                            :value="type.key"
                                            :key="key"
                                            v-for="(type, key) in types"
                                            v-text="type.title"
                                        >
                                        </option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="type" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group class="flex-1">
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.services.customizable-options.update-create.is-required')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="is_required"
                                        rules="required"
                                        ::value="selectedOption.is_required"
                                        :label="trans('Admin::app.services.services.customizable-options.update-create.is-required')"
                                    >
                                        <option value="1">
                                            @lang('Admin::app.services.services.customizable-options.update-create.yes')
                                        </option>

                                        <option value="0">
                                            @lang('Admin::app.services.services.customizable-options.update-create.no')
                                        </option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="is_required" />
                                </x-admin::form.control-group>
                            </div>

                            <x-admin::form.control-group v-if="['text', 'textarea'].includes(selectedOption.type)">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.customizable-options.update-create.max-characters')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="max_characters"
                                    rules="required|numeric|min_value:1"
                                    ::value="selectedOption.max_characters"
                                    :label="trans('Admin::app.services.services.customizable-options.update-create.max-characters')"
                                />

                                <x-admin::form.control-group.error control-name="max_characters" />
                            </x-admin::form.control-group>

                            <x-admin::form.control-group v-else-if="selectedOption.type == 'file'">
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.services.customizable-options.update-create.supported-file-extensions')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="text"
                                    name="supported_file_extensions"
                                    rules="required"
                                    ::value="selectedOption.supported_file_extensions"
                                    :label="trans('Admin::app.services.services.customizable-options.update-create.supported-file-extensions')"
                                />

                                <x-admin::form.control-group.error control-name="supported_file_extensions" />
                            </x-admin::form.control-group>
                        </x-slot>

                        <!-- Option Form Modal Footer -->
                        <x-slot:footer>
                            <!-- Save Button -->
                            <x-admin::button
                                button-type="button"
                                class="primary-button"
                                :title="trans('Admin::app.services.services.customizable-options.update-create.save-btn')"
                            />
                        </x-slot>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        app.component('v-service-customizable-options', {
            template: '#v-service-customizable-options-template',

            props: ['errors'],

            data() {
                return {
                    types: {
                        text: {
                            key: 'text',
                            title: "@lang('Admin::app.services.services.customizable-options.types.text')",
                        },
                        textarea: {
                            key: 'textarea',
                            title: "@lang('Admin::app.services.services.customizable-options.types.textarea')",
                        },
                        checkbox: {
                            key: 'checkbox',
                            title: "@lang('Admin::app.services.services.customizable-options.types.checkbox')",
                        },
                        radio: {
                            key: 'radio',
                            title: "@lang('Admin::app.services.services.customizable-options.types.radio')",
                        },
                        select: {
                            key: 'select',
                            title: "@lang('Admin::app.services.services.customizable-options.types.select')",
                        },
                        multiselect: {
                            key: 'multiselect',
                            title: "@lang('Admin::app.services.services.customizable-options.types.multiselect')",
                        },
                        date: {
                            key: 'date',
                            title: "@lang('Admin::app.services.services.customizable-options.types.date')",
                        },
                        datetime: {
                            key: 'datetime',
                            title: "@lang('Admin::app.services.services.customizable-options.types.datetime')",
                        },
                        time: {
                            key: 'time',
                            title: "@lang('Admin::app.services.services.customizable-options.types.time')",
                        },
                        file: {
                            key: 'file',
                            title: "@lang('Admin::app.services.services.customizable-options.types.file')",
                        },
                    },

                    options: @json($options),

                    selectedOption: {
                        label: '',
                        type: 'text',
                        is_required: 1,
                        max_characters: null,
                        supported_file_extensions: null,
                    },
                };
            },

            methods: {
                updateOrCreate(params) {
                    if (this.selectedOption.id == undefined) {
                        params.id = 'option_' + this.options.length;
                        this.options.push(params);
                    } else {
                        params.id = this.selectedOption.id;

                        const indexToUpdate = this.options.findIndex(option => option.id === this.selectedOption.id);

                        this.options[indexToUpdate] = {
                            ...this.options[indexToUpdate],
                            ...params,
                        };
                    }

                    this.resetForm();

                    this.$refs.updateCreateOptionModal.close();
                },

                removeOption(option) {
                    this.$emitter.emit('open-confirm-modal', {
                        agree: () => {
                            let index = this.options.indexOf(option);
                            this.options.splice(index, 1);
                        }
                    });
                },

                resetForm() {
                    this.selectedOption = {
                        label: '',
                        type: 'text',
                        is_required: 1,
                        max_characters: null,
                        supported_file_extensions: null,
                    };
                },
            },
        });
    </script>
@endPushOnce

