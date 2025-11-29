<v-attribute-group-fields-manager
    :group-id="{{ $groupId }}"
    :group-type="{{ $groupType ?? 'general' }}"
    :attribute-types='@json($attributeTypes ?? [])'
    :validations='@json($validations ?? [])'
    :validation-labels='@json($validationLabels ?? [])'
    :initial-fields='@json($initialFields ?? [])'
></v-attribute-group-fields-manager>

@pushOnce('scripts')
    <script type="text/x-template" id="v-attribute-group-fields-manager-template">
        <div class="box-shadow relative rounded bg-white dark:bg-gray-900 mt-4">
            <!-- Fields Panel Header -->
            <div class="p-4 flex flex-col mb-2.5">
                <div class="flex justify-between gap-5">
                    <!-- Fields Title & Info -->
                    <div class="flex flex-col gap-2">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.attribute-groups.edit.fields-title')
                        </p>

                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('Admin::app.services.attribute-groups.edit.fields-info')
                        </p>
                    </div>

                    <!-- Add Field Button -->
                    <div class="flex items-center gap-x-1">
                        <div
                            class="secondary-button"
                            @click="resetForm(); $refs.updateCreateFieldModal.open()"
                        >
                            @lang('Admin::app.services.attribute-groups.edit.add-field-btn')
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fields Panel Content -->
            <x-admin::accordion v-if="fields.length">
                <x-slot:header>
                    <p class="text-base font-semibold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.attribute-groups.edit.fields-list')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div class="grid">
                        <draggable
                            ghost-class="draggable-ghost"
                            v-bind="{ animation: 200 }"
                            handle=".icon-drag"
                            :list="fields"
                            item-key="uid"
                            @end="onFieldDragEnd"
                        >
                            <template #item="{ element: field, index }">
                                <div class="mb-2.5 flex justify-between gap-5 p-4">
                                    <!-- Field Information -->
                                    <div class="flex gap-2.5">
                                        <i class="icon-drag cursor-grab text-xl transition-all hover:text-gray-700 dark:text-gray-300"></i>

                                        <p
                                            class="text-base font-semibold text-gray-800 dark:text-white"
                                            :class="{'required': field.is_required}"
                                        >
                                            @{{ (index + 1) + '. ' + displayFieldTitle(field) }}
                                        </p>
                                    </div>

                                    <!-- Field Action Buttons -->
                                    <div class="grid place-content-start gap-1 ltr:text-right rtl:text-left">
                                        <div class="flex gap-2">
                                            <!-- Edit Field -->
                                            <p
                                                class="cursor-pointer text-blue-600 transition-all hover:underline"
                                                @click="selectedField = JSON.parse(JSON.stringify(field)); $refs.updateCreateFieldModal.open()"
                                            >
                                                @lang('Admin::app.services.attribute-groups.edit.edit-field-btn')
                                            </p>

                                    <!-- Remove Field -->
                                    <p
                                        v-if="!isProtectedField(field)"
                                        class="cursor-pointer text-red-600 transition-all hover:underline"
                                        @click="removeField(field)"
                                    >
                                        @lang('Admin::app.services.attribute-groups.edit.delete-field-btn')
                                    </p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </draggable>
                    </div>
                </x-slot:content>
            </x-admin::accordion>

            <!-- For Empty Fields -->
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
                        @lang('Admin::app.services.attribute-groups.edit.no-fields')
                    </p>

                    <p class="text-gray-400">
                        @lang('Admin::app.services.attribute-groups.edit.no-fields-info')
                    </p>
                </div>

                <!-- Add Field Button -->
                <div
                    class="secondary-button text-sm"
                    @click="resetForm(); $refs.updateCreateFieldModal.open()"
                >
                    @lang('Admin::app.services.attribute-groups.edit.add-field-btn')
                </div>
            </div>

            <!-- Add/Edit Field Form Modal -->
            <x-admin::form
                v-slot="{ meta, errors, handleSubmit }"
                as="div"
            >
                <form @submit="handleSubmit($event, updateOrCreate)">
                    <x-admin::modal ref="updateCreateFieldModal">
                        <!-- Field Form Modal Header -->
                        <x-slot:header>
                            <p class="text-lg font-bold text-gray-800 dark:text-white">
                                @{{ selectedField.id == undefined ? uiTexts.add_field_title : uiTexts.edit_field_title }}
                            </p>
                        </x-slot:header>

                        <!-- Field Form Modal Content -->
                        <x-slot:content>
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label class="required">
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.field-type')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="service_attribute_type_id"
                                    rules="required"
                                    v-model="selectedField.service_attribute_type_id"
                                    ::disabled="selectedField.id != undefined"
                                    :label="trans('Admin::app.services.attribute-groups.attribute-group-fields.field-type')"
                                    @change="onSelectedAttributeTypeChange"
                                >
                                    <option value="">
                                        @lang('Admin::app.services.attribute-groups.attribute-group-fields.select-field-type')
                                    </option>

                                    <option
                                        v-for="attributeType in availableAttributeTypesForModal"
                                        :key="attributeType.id"
                                        :value="attributeType.id"
                                    >
                                        @{{ getAttributeTypeName(attributeType) }}
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="service_attribute_type_id" />
                            </x-admin::form.control-group>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div
                                    v-for="locale in locales"
                                    :key="`modal-${locale.code}`"
                                >
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('Admin::app.services.attribute-groups.edit.field-label') (@{{ locale.name }})
                                        </x-admin::form.control-group.label>

                                        <x-admin::form.control-group.control
                                            type="text"
                                            ::name="`labels[${locale.code}]`"
                                            rules="required"
                                            v-model="selectedField.labels[locale.code]"
                                            ::placeholder="locale.name"
                                            ::label="`${uiTexts.field_label} (${locale.name})`"
                                        />

                                        <x-admin::form.control-group.error ::control-name="`labels[${locale.code}]`" />
                                    </x-admin::form.control-group>
                                </div>
                            </div>

                            <x-admin::form.control-group v-if="canHaveDefaultValue">
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.default-value')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="default_value"
                                    v-model="selectedField.default_value"
                                    :label="trans('Admin::app.services.attribute-groups.attribute-group-fields.default-value')"
                                >
                                    <option value="">
                                        @lang('Admin::app.common.select')
                                    </option>

                                    <option value="1">
                                        @lang('Admin::app.common.yes')
                                    </option>

                                    <option value="0">
                                        @lang('Admin::app.common.no')
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="default_value" />
                            </x-admin::form.control-group>

                            <template v-if="canShowValidationControls">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="validation_option"
                                        v-model="selectedField.validation_option"
                                        :label="trans('Admin::app.services.attribute-groups.attribute-group-fields.validation')"
                                    >
                                        <option value="">
                                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.select-validation')
                                        </option>

                                        <option
                                            v-for="option in validationOptionsList"
                                            :key="option"
                                            :value="option"
                                        >
                                            @{{ translateValidationOption(option) }}
                                        </option>

                                        <option value="custom">
                                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-custom')
                                        </option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="validation_rules" />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group v-if="selectedField.validation_option === 'regex'">
                                    <x-admin::form.control-group.label>
                                        @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-regex')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="validation_regex"
                                        v-model="selectedField.validation_regex"
                                        placeholder="{{ trans('Admin::app.services.attribute-groups.attribute-group-fields.validation-regex-placeholder') }}"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group v-if="selectedField.validation_option === 'custom'">
                                    <x-admin::form.control-group.label>
                                        @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-custom-rule')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="validation_custom"
                                        v-model="selectedField.validation_custom"
                                        placeholder="{{ trans('Admin::app.services.attribute-groups.attribute-group-fields.validation-rules-placeholder') }}"
                                    />
                                </x-admin::form.control-group>

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-rules-help')
                                </p>
                            </template>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="select"
                                    name="is_required"
                                    rules="required"
                                    v-model="selectedField.is_required"
                                    :label="trans('Admin::app.services.attribute-groups.attribute-group-fields.is-required')"
                                >
                                    <option value="1">
                                        @lang('Admin::app.common.yes')
                                    </option>

                                    <option value="0">
                                        @lang('Admin::app.common.no')
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="is_required" />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required-help')
                                </p>
                            </x-admin::form.control-group>
                        </x-slot:content>

                        <!-- Field Form Modal Footer -->
                        <x-slot:footer>
                            <!-- Save Button -->
                            <x-admin::button
                                button-type="submit"
                                class="primary-button"
                                :title="trans('Admin::app.services.attribute-groups.edit.save-field-btn')"
                                ::loading="isSavingField"
                                ::disabled="isSavingField"
                            />
                        </x-slot:footer>
                    </x-admin::modal>
                </form>
            </x-admin::form>
        </div>
    </script>

    <script type="module">
        const normalizeBoolean = (value) => {
            if (value === undefined || value === null) {
                return false;
            }

            if (typeof value === 'boolean') {
                return value;
            }

            if (typeof value === 'number') {
                return value === 1;
            }

            if (typeof value === 'string') {
                return ['1', 'true', 'on', 'yes'].includes(value.toLowerCase());
            }

            return false;
        };

        const normalizeDefaultValue = (value) => {
            if (value === null || value === undefined) {
                return '';
            }

            if (typeof value === 'boolean') {
                return value ? '1' : '0';
            }

            if (typeof value === 'number') {
                return value.toString();
            }

            if (typeof value === 'string') {
                return value;
            }

            return '';
        };

        const getValidationString = (value) => {
            if (!value) {
                return '';
            }

            if (typeof value === 'string') {
                return value;
            }

            if (Array.isArray(value)) {
                return value
                    .filter(Boolean)
                    .map(item => (typeof item === 'string' ? item : JSON.stringify(item)))
                    .join('|');
            }

            if (typeof value === 'object') {
                if (value.validation) {
                    return value.validation;
                }

                if (value.rules) {
                    if (Array.isArray(value.rules)) {
                        return value.rules.join('|');
                    }

                    if (typeof value.rules === 'string') {
                        return value.rules;
                    }
                }

                return Object.values(value)
                    .filter(Boolean)
                    .map(item => (typeof item === 'string' ? item : JSON.stringify(item)))
                    .join('|');
            }

            return '';
        };

        const parseValidationRule = (value, options = []) => {
            const stringValue = getValidationString(value);
            const normalizedOptions = Array.isArray(options) ? options : [];

            if (!stringValue) {
                return {
                    value: '',
                    option: '',
                    regex: '',
                    custom: '',
                };
            }

            if (stringValue.startsWith('regex:')) {
                return {
                    value: stringValue,
                    option: 'regex',
                    regex: stringValue.substring(6),
                    custom: '',
                };
            }

            if (normalizedOptions.includes(stringValue)) {
                return {
                    value: stringValue,
                    option: stringValue,
                    regex: '',
                    custom: '',
                };
            }

            return {
                value: stringValue,
                option: 'custom',
                regex: '',
                custom: stringValue,
            };
        };

        const formatValidationRuleValue = (field) => {
            if (!field || !field.validation_option) {
                return '';
            }

            if (field.validation_option === 'regex') {
                return field.validation_regex ? `regex:${field.validation_regex}` : '';
            }

            if (field.validation_option === 'custom') {
                return field.validation_custom ? field.validation_custom : '';
            }

            return field.validation_option;
        };

        const normalizeFieldHelper = (field, locales, validationOptionsList) => {
            const labels = {};

            locales.forEach(locale => {
                let value = '';

                if (field.labels && field.labels[locale.code] !== undefined) {
                    value = field.labels[locale.code] || '';
                } else if (Array.isArray(field.translations)) {
                    const translation = field.translations.find(trans => trans.locale === locale.code);
                    value = translation?.label || '';
                }

                labels[locale.code] = value;
            });

            const parsedValidation = parseValidationRule(field.validation_rules, validationOptionsList);

            return {
                id: field.id ?? null,
                uid: `field_${field.id ?? Math.random().toString(36).slice(2, 11)}`,
                service_attribute_type_id: field.service_attribute_type_id ?? '',
                labels,
                sort_order: field.sort_order ?? 0,
                is_required: normalizeBoolean(field.is_required) ? 1 : 0,
                default_value: normalizeDefaultValue(field.default_value),
                validation_option: parsedValidation.option,
                validation_regex: parsedValidation.regex,
                validation_custom: parsedValidation.custom,
                validation_rules: parsedValidation.value,
            };
        };

        app.component('v-attribute-group-fields-manager', {
            template: '#v-attribute-group-fields-manager-template',

            props: {
                groupId: {
                    type: Number,
                    required: true
                },
                groupType: {
                    type: String,
                    default: 'general'
                },
                attributeTypes: {
                    type: Array,
                    required: true
                },
                validations: {
                    type: Array,
                    required: true
                },
                validationLabels: {
                    type: Object,
                    default: () => ({})
                },
                initialFields: {
                    type: Array,
                    default: () => []
                }
            },

            data() {
                const validationOptions = Array.isArray(this.validations) ? this.validations : [];
                const locales = @json(core()->getAllLocales()->map(fn($locale) => [
                    'code' => $locale->code,
                    'name' => $locale->name,
                ])->values()->toArray());

                // Initialize fields from initialFields prop
                const initialFields = Array.isArray(this.initialFields) ? this.initialFields : [];
                const normalizedFields = initialFields.map(field => normalizeFieldHelper(field, locales, validationOptions))
                    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));

                return {
                    isLoading: false,
                    isSavingField: false,
                    fields: normalizedFields,
                    locales: locales,
                    attributeTypesList: this.attributeTypes || [],
                    selectedField: {
                        id: undefined,
                        uid: '',
                        service_attribute_type_id: '',
                        labels: {},
                        sort_order: 0,
                        is_required: 0,
                        default_value: '',
                        validation_option: '',
                        validation_regex: '',
                        validation_custom: '',
                        validation_rules: '',
                    },
                    currentLocale: '{{ app()->getLocale() }}',
                    defaultFieldName: "{{ __('Admin::app.services.attribute-groups.edit.field-default-name') }}",
                    yesLabel: @json(__('Admin::app.common.yes')),
                    noLabel: @json(__('Admin::app.common.no')),
                    validationOptionsList: validationOptions,
                    validationOptionLabels: this.validationLabels ?? {},
                    validationNoneLabel: "{{ __('Admin::app.services.attribute-groups.attribute-group-fields.select-validation') }}",
                    validationCustomLabel: "{{ __('Admin::app.services.attribute-groups.attribute-group-fields.validation-custom') }}",
                    uiTexts: {
                        field_label: "{{ __('Admin::app.services.attribute-groups.edit.field-label') }}",
                        add_field_title: "{{ __('Admin::app.services.attribute-groups.edit.add-field-title') }}",
                        edit_field_title: "{{ __('Admin::app.services.attribute-groups.edit.edit-field-title') }}",
                        save_field_btn: "{{ __('Admin::app.services.attribute-groups.edit.save-field-btn') }}",
                        update_field_btn: "{{ __('Admin::app.services.attribute-groups.edit.update-field-btn') }}",
                    },
                }
            },

            computed: {
                availableAttributeTypesForModal() {
                    return Array.isArray(this.attributeTypesList) ? this.attributeTypesList : [];
                },

                selectedAttributeType() {
                    if (!this.selectedField || !this.selectedField.service_attribute_type_id) {
                        return null;
                    }

                    return this.getAttributeTypeInfo(this.selectedField.service_attribute_type_id);
                },

                canShowValidationControls() {
                    return this.supportsValidationForType(this.selectedAttributeType);
                },
                canHaveDefaultValue() {
                    return this.selectedAttributeType?.type === 'boolean';
                },
            },

            watch: {
                'selectedField.validation_option'(value) {
                    if (value !== 'regex') {
                        this.selectedField.validation_regex = '';
                    }

                    if (value !== 'custom') {
                        this.selectedField.validation_custom = '';
                    }

                    this.selectedField.validation_rules = formatValidationRuleValue(this.selectedField);
                },
                'selectedField.validation_regex'() {
                    if (this.selectedField.validation_option === 'regex') {
                        this.selectedField.validation_rules = formatValidationRuleValue(this.selectedField);
                    }
                },
                'selectedField.validation_custom'() {
                    if (this.selectedField.validation_option === 'custom') {
                        this.selectedField.validation_rules = formatValidationRuleValue(this.selectedField);
                    }
                },
                canShowValidationControls(value) {
                    if (!value) {
                        this.selectedField.validation_option = '';
                        this.selectedField.validation_regex = '';
                        this.selectedField.validation_custom = '';
                        this.selectedField.validation_rules = '';
                    }
                },

                canHaveDefaultValue(value) {
                    if (!value) {
                        this.selectedField.default_value = '';
                    } else {
                        this.selectedField.default_value = normalizeDefaultValue(this.selectedField.default_value);
                    }
                },
            },

            methods: {
                refreshFieldLocally(fieldDataFromServer) {
                    if (!fieldDataFromServer) {
                        return;
                    }

                    const normalizedField = this.normalizeField(fieldDataFromServer);
                    
                    if (this.selectedField.id) {
                        // Update existing field
                        const index = this.fields.findIndex(f => f.id === this.selectedField.id);
                        if (index > -1) {
                            this.fields[index] = normalizedField;
                        }
                    } else {
                        // Add new field
                        this.fields.push(normalizedField);
                        this.fields.sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
                    }
                },

                normalizeField(field) {
                    const labels = {};

                    this.locales.forEach(locale => {
                        let value = '';

                        if (field.labels && field.labels[locale.code] !== undefined) {
                            value = field.labels[locale.code] || '';
                        } else if (Array.isArray(field.translations)) {
                            const translation = field.translations.find(trans => trans.locale === locale.code);
                            value = translation?.label || '';
                        }

                        labels[locale.code] = value;
                    });

                    const parsedValidation = parseValidationRule(field.validation_rules, this.validationOptionsList);

                    return {
                        id: field.id ?? null,
                        uid: `field_${field.id ?? Math.random().toString(36).slice(2, 11)}`,
                        service_attribute_type_id: field.service_attribute_type_id ?? '',
                        labels,
                        sort_order: field.sort_order ?? 0,
                        is_required: normalizeBoolean(field.is_required) ? 1 : 0,
                        default_value: normalizeDefaultValue(field.default_value),
                        validation_option: parsedValidation.option,
                        validation_regex: parsedValidation.regex,
                        validation_custom: parsedValidation.custom,
                        validation_rules: parsedValidation.value,
                    };
                },

                resetForm() {
                    const labels = {};
                    this.locales.forEach(locale => {
                        labels[locale.code] = '';
                    });

                    this.selectedField = {
                        id: undefined,
                        uid: this.generateUid(),
                        service_attribute_type_id: '',
                        labels,
                        sort_order: this.fields.length,
                        is_required: 0,
                        default_value: '',
                        validation_option: '',
                        validation_regex: '',
                        validation_custom: '',
                        validation_rules: '',
                    };
                },

                generateUid() {
                    return `field_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
                },

                supportsValidationForType(attributeType) {
                    if (!attributeType) {
                        return false;
                    }

                    const supportedTypes = ['text', 'textarea', 'number', 'price', 'email', 'url'];

                    return supportedTypes.includes(attributeType.type);
                },

                translateValidationOption(option) {
                    if (!option) {
                        return this.validationNoneLabel;
                    }

                    if (option === 'custom') {
                        return this.validationCustomLabel;
                    }

                    return this.validationOptionLabels?.[option] ?? option;
                },

                async updateOrCreate(params) {
                    this.isSavingField = true;

                    try {
                        const validationRule = formatValidationRuleValue(this.selectedField);

                        const fieldPayload = {
                            service_attribute_type_id: this.selectedField.service_attribute_type_id,
                            label: this.selectedField.labels,
                            sort_order: this.selectedField.sort_order ?? 0,
                            is_required: normalizeBoolean(this.selectedField.is_required),
                            validation_rules: validationRule,
                            default_value: normalizeDefaultValue(this.selectedField.default_value),
                        };

                        let response;
                        let fieldData = null;
                        if (this.selectedField.id == undefined) {
                            // Create new field
                            response = await this.$axios.post(`{{ url('admin/attribute-groups') }}/${this.groupId}/fields`, fieldPayload);
                            
                            // Get field data from response
                            if (response.data?.data) {
                                fieldData = response.data.data;
                            }
                            
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data?.message || "@lang('Admin::app.services.attribute-groups.attribute-group-fields.create-success')"
                            });
                        } else {
                            // Update existing field
                            const fieldId = this.selectedField.id;
                            response = await this.$axios.put(`{{ url('admin/attribute-groups') }}/${this.groupId}/fields/${fieldId}`, fieldPayload);
                            
                            // Get field data from response if available
                            if (response.data?.data) {
                                fieldData = response.data.data;
                            }
                            
                            this.$emitter.emit('add-flash', {
                                type: 'success',
                                message: response.data?.message || "@lang('Admin::app.services.attribute-groups.attribute-group-fields.update-success')"
                            });
                        }

                        this.$refs.updateCreateFieldModal.close();
                        
                        // Update fields locally without going to server
                        if (fieldData) {
                            this.refreshFieldLocally(fieldData);
                        } else {
                            // If no data in response, construct it from selectedField
                            const fieldToUpdate = {
                                id: this.selectedField.id || null,
                                service_attribute_type_id: this.selectedField.service_attribute_type_id,
                                labels: this.selectedField.labels,
                                sort_order: this.selectedField.sort_order,
                                is_required: normalizeBoolean(this.selectedField.is_required),
                                validation_rules: formatValidationRuleValue(this.selectedField),
                                default_value: normalizeDefaultValue(this.selectedField.default_value),
                                translations: Object.keys(this.selectedField.labels).map(locale => ({
                                    locale: locale,
                                    label: this.selectedField.labels[locale]
                                }))
                            };
                            this.refreshFieldLocally(fieldToUpdate);
                        }
                        
                        this.resetForm();
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.attribute-groups.edit.error-saving-field')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isSavingField = false;
                    }
                },

                isProtectedField(field) {
                    // Check if field is national_id_card in a citizen group
                    if (this.groupType === 'citizen') {
                        const attributeTypeInfo = this.getAttributeTypeInfo(field.service_attribute_type_id);
                        if (attributeTypeInfo && attributeTypeInfo.code === 'national_id_card') {
                            return true;
                        }
                    }
                    return false;
                },

                removeField(field) {
                    // Prevent deletion of protected fields
                    if (this.isProtectedField(field)) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.attribute-groups.edit.cannot-delete-protected-field')"
                        });
                        return;
                    }

                    if (!field.id) {
                        const index = this.fields.indexOf(field);
                        if (index > -1) {
                            this.fields.splice(index, 1);
                        }
                        return;
                    }

                    this.$emitter.emit('open-confirm-modal', {
                        agree: async () => {
                            try {
                                await this.$axios.delete(`{{ url('admin/attribute-groups') }}/${this.groupId}/fields/${field.id}`);
                                
                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: "@lang('Admin::app.services.attribute-groups.attribute-group-fields.delete-success')"
                                });

                                // Remove field locally without going to server
                                const index = this.fields.findIndex(f => f.id === field.id);
                                if (index > -1) {
                                    this.fields.splice(index, 1);
                                }
                            } catch (error) {
                                this.$emitter.emit('add-flash', {
                                    type: 'error',
                                    message: error.response?.data?.message || "@lang('Admin::app.services.attribute-groups.edit.error-deleting-field')"
                                });
                            }
                        }
                    });
                },

                async onFieldDragEnd() {
                    // Update sort orders after drag
                    const sortOrders = this.fields.map((field, index) => ({
                        id: field.id,
                        sort_order: index
                    }));

                    try {
                        await this.$axios.post(`{{ url('admin/attribute-groups') }}/${this.groupId}/fields/reorder`, {
                            fields: sortOrders
                        });
                        
                        // Update sort_order locally
                        this.fields.forEach((field, index) => {
                            field.sort_order = index;
                        });
                    } catch (error) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: error.response?.data?.message || "@lang('Admin::app.services.attribute-groups.edit.error-reordering-fields')"
                        });
                        // Reload page to restore original order
                        window.location.reload();
                    }
                },

                onSelectedAttributeTypeChange() {
                    if (!this.selectedField || this.selectedField.id != undefined) {
                        return;
                    }

                    const attributeType = this.getAttributeTypeInfo(this.selectedField.service_attribute_type_id);

                    if (!attributeType) {
                        return;
                    }

                    this.selectedField.is_required = normalizeBoolean(attributeType.is_required) ? 1 : 0;
                    this.selectedField.default_value = attributeType.type === 'boolean'
                        ? normalizeDefaultValue(attributeType.default_value)
                        : '';

                    if (!this.supportsValidationForType(attributeType)) {
                        this.selectedField.validation_option = '';
                        this.selectedField.validation_regex = '';
                        this.selectedField.validation_custom = '';
                        this.selectedField.validation_rules = '';
                    } else {
                        const baseValidation = attributeType.validation === 'regex'
                            ? (attributeType.regex ? `regex:${attributeType.regex}` : 'regex:')
                            : attributeType.validation;

                        const parsedValidation = parseValidationRule(baseValidation, this.validationOptionsList);

                        this.selectedField.validation_option = parsedValidation.option || '';
                        this.selectedField.validation_regex = parsedValidation.option === 'regex'
                            ? (parsedValidation.regex || attributeType.regex || '')
                            : '';
                        this.selectedField.validation_custom = parsedValidation.option === 'custom'
                            ? (parsedValidation.custom || '')
                            : '';
                        this.selectedField.validation_rules = formatValidationRuleValue(this.selectedField);
                    }

                    this.locales.forEach(locale => {
                        if (!this.selectedField.labels[locale.code]) {
                            if (Array.isArray(attributeType.translations)) {
                                const translation = attributeType.translations.find(t => t.locale === locale.code);

                                if (translation?.name) {
                                    this.selectedField.labels[locale.code] = translation.name;
                                    return;
                                }

                                if (attributeType.translations[0]?.name) {
                                    this.selectedField.labels[locale.code] = attributeType.translations[0].name;
                                }
                            }
                        }
                    });
                },

                displayFieldTitle(field) {
                    const labels = field.labels || {};
                    const primaryLabel = labels[this.currentLocale] && labels[this.currentLocale].trim()
                        ? labels[this.currentLocale].trim()
                        : Object.values(labels).find(label => label && label.trim()) || this.defaultFieldName;

                    return primaryLabel;
                },

                getAttributeTypeInfo(attributeTypeId) {
                    try {
                        if (!attributeTypeId) return null;
                        if (!this.attributeTypesList || !Array.isArray(this.attributeTypesList)) return null;
                        return this.attributeTypesList.find(at => at && at.id === attributeTypeId) || null;
                    } catch (e) {
                        console.error('Error in getAttributeTypeInfo:', e);
                        return null;
                    }
                },

                getAttributeTypeName(attributeType) {
                    if (!attributeType) return '';
                    if (!attributeType.translations || !Array.isArray(attributeType.translations)) return '';
                    const currentLocale = '{{ app()->getLocale() }}';
                    const translation = attributeType.translations.find(t => t.locale === currentLocale);
                    if (translation && translation.name) {
                        return translation.name;
                    }
                    if (attributeType.translations.length > 0 && attributeType.translations[0].name) {
                        return attributeType.translations[0].name;
                    }
                    return '';
                }
            }
        });
    </script>
@endPushOnce
