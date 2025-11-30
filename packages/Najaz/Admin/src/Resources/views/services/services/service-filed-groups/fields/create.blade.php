@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-create-template"
    >
        <div>
            <x-admin::modal
                ref="createFieldModal"
                @toggle="handleModalToggle"
            >
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.attribute-groups.edit.add-field-title')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.field-type')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="service_attribute_type_id"
                            rules="required"
                            v-model="fieldData.service_attribute_type_id"
                            :label="trans('Admin::app.services.attribute-groups.attribute-group-fields.field-type')"
                            @change="onSelectedAttributeTypeChange"
                        >
                            <option value="">
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.select-field-type')
                            </option>

                            <option
                                v-for="attributeType in availableAttributeTypes"
                                :key="attributeType.id"
                                :value="attributeType.id"
                            >
                                @{{ getAttributeTypeName(attributeType) }}
                            </option>
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="service_attribute_type_id" />
                    </x-admin::form.control-group>

                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="required">
                            @lang('Admin::app.services.services.groups.fields.edit.field-code')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="code"
                            rules="required"
                            v-model="fieldData.code"
                            :label="trans('Admin::app.services.services.groups.fields.edit.field-code')"
                        />

                        <x-admin::form.control-group.error control-name="code" />
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
                                    v-model="fieldData.labels[locale.code]"
                                    ::placeholder="locale.name"
                                    ::label="`${trans('Admin::app.services.attribute-groups.edit.field-label')} (${locale.name})`"
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
                            v-model="fieldData.default_value"
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
                                v-model="fieldData.validation_option"
                                :label="trans('Admin::app.services.attribute-groups.attribute-group-fields.validation')"
                            >
                                <option value="">
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.select-validation')
                                </option>

                                <option
                                    v-for="option in validationsList"
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

                        <x-admin::form.control-group v-if="fieldData.validation_option === 'regex'">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-regex')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="validation_regex"
                                v-model="fieldData.validation_regex"
                                :placeholder="trans('Admin::app.services.attribute-groups.attribute-group-fields.validation-regex-placeholder')"
                            />
                        </x-admin::form.control-group>

                        <x-admin::form.control-group v-if="fieldData.validation_option === 'custom'">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-custom-rule')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="validation_custom"
                                v-model="fieldData.validation_custom"
                                :placeholder="trans('Admin::app.services.attribute-groups.attribute-group-fields.validation-rules-placeholder')"
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
                            type="switch"
                            name="is_required"
                            value="1"
                            v-model="fieldData.is_required"
                        />

                        <x-admin::form.control-group.error control-name="is_required" />

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required-help')
                        </p>
                    </x-admin::form.control-group>
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-admin::button
                            button-type="button"
                            button-class="secondary-button"
                            :title="trans('Admin::app.common.cancel')"
                            ::disabled="isSaving"
                            @click="$refs.createFieldModal.close()"
                        />

                        <x-admin::button
                            button-type="button"
                            button-class="primary-button"
                            :title="trans('Admin::app.services.attribute-groups.edit.save-field-btn')"
                            ::disabled="isSaving"
                            ::loading="isSaving"
                            @click="createField"
                        />
                    </div>
                </x-slot:footer>
            </x-admin::modal>
        </div>
    </script>

    <script type="module">
        const normalizeBoolean = (value) => {
            if (value === null || value === undefined) {
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

        app.component('v-service-data-group-field-create', {
            template: '#v-service-data-group-field-create-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    required: true,
                },
                pivotId: {
                    type: [Number, String],
                    default: null,
                },
                attributeTypes: {
                    type: Array,
                    default: () => [],
                },
                validations: {
                    type: Array,
                    default: () => [],
                },
                validationLabels: {
                    type: Object,
                    default: () => ({}),
                },
                locales: {
                    type: Array,
                    default: () => [],
                },
                sortOrder: {
                    type: Number,
                    default: 0,
                },
            },

            emits: ['field-created'],

            data() {
                const labels = {};
                this.locales.forEach(locale => {
                    labels[locale.code] = '';
                });

                return {
                    isSaving: false,
                    fieldData: {
                        service_attribute_type_id: '',
                        code: '',
                        labels: labels,
                        sort_order: this.sortOrder || 0,
                        is_required: false,
                        default_value: '',
                        validation_option: '',
                        validation_regex: '',
                        validation_custom: '',
                        validation_rules: '',
                    },
                    selectedAttributeType: null,
                };
            },

            computed: {
                availableAttributeTypes() {
                    return Array.isArray(this.attributeTypes) ? this.attributeTypes : [];
                },

                canShowValidationControls() {
                    return this.supportsValidationForType(this.selectedAttributeType);
                },

                canHaveDefaultValue() {
                    return this.selectedAttributeType?.type === 'boolean';
                },
            },

            watch: {
                'fieldData.validation_option'(value) {
                    if (value !== 'regex') {
                        this.fieldData.validation_regex = '';
                    }
                    if (value !== 'custom') {
                        this.fieldData.validation_custom = '';
                    }
                    this.fieldData.validation_rules = formatValidationRuleValue(this.fieldData);
                },
                'fieldData.validation_regex'() {
                    if (this.fieldData.validation_option === 'regex') {
                        this.fieldData.validation_rules = formatValidationRuleValue(this.fieldData);
                    }
                },
                'fieldData.validation_custom'() {
                    if (this.fieldData.validation_option === 'custom') {
                        this.fieldData.validation_rules = formatValidationRuleValue(this.fieldData);
                    }
                },
            },

            methods: {
                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        this.resetForm();
                    }
                },

                resetForm() {
                    const labels = {};
                    this.locales.forEach(locale => {
                        labels[locale.code] = '';
                    });

                    this.fieldData = {
                        service_attribute_type_id: '',
                        code: '',
                        labels: labels,
                        sort_order: this.sortOrder || 0,
                        is_required: false,
                        default_value: '',
                        validation_option: '',
                        validation_regex: '',
                        validation_custom: '',
                        validation_rules: '',
                    };
                    this.selectedAttributeType = null;
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
                        return "@lang('Admin::app.services.attribute-groups.attribute-group-fields.select-validation')";
                    }
                    if (option === 'custom') {
                        return "@lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-custom')";
                    }
                    return this.validationLabels?.[option] ?? option;
                },

                getAttributeTypeInfo(attributeTypeId) {
                    if (!attributeTypeId) return null;
                    if (!this.attributeTypes || !Array.isArray(this.attributeTypes)) return null;
                    return this.attributeTypes.find(at => at && at.id === attributeTypeId) || null;
                },

                getAttributeTypeName(attributeType) {
                    if (!attributeType) return '';
                    if (attributeType.name) return attributeType.name;
                    if (!attributeType.translations || !Array.isArray(attributeType.translations)) return attributeType.code || '';
                    const currentLocale = '{{ app()->getLocale() }}';
                    const translation = attributeType.translations.find(t => t.locale === currentLocale);
                    if (translation && translation.name) {
                        return translation.name;
                    }
                    if (attributeType.translations.length > 0 && attributeType.translations[0].name) {
                        return attributeType.translations[0].name;
                    }
                    return attributeType.code || '';
                },

                onSelectedAttributeTypeChange() {
                    const attributeType = this.getAttributeTypeInfo(this.fieldData.service_attribute_type_id);

                    if (!attributeType) {
                        this.selectedAttributeType = null;
                        return;
                    }

                    this.selectedAttributeType = attributeType;
                    this.fieldData.is_required = normalizeBoolean(attributeType.is_required);
                    this.fieldData.default_value = attributeType.type === 'boolean'
                        ? normalizeDefaultValue(attributeType.default_value)
                        : '';

                    if (!this.supportsValidationForType(attributeType)) {
                        this.fieldData.validation_option = '';
                        this.fieldData.validation_regex = '';
                        this.fieldData.validation_custom = '';
                        this.fieldData.validation_rules = '';
                    } else {
                        const baseValidation = attributeType.validation === 'regex'
                            ? (attributeType.regex ? `regex:${attributeType.regex}` : 'regex:')
                            : attributeType.validation;

                        if (baseValidation && baseValidation.startsWith('regex:')) {
                            this.fieldData.validation_option = 'regex';
                            this.fieldData.validation_regex = baseValidation.substring(6);
                        } else if (baseValidation && this.validations.includes(baseValidation)) {
                            this.fieldData.validation_option = baseValidation;
                            this.fieldData.validation_regex = '';
                            this.fieldData.validation_custom = '';
                        } else if (baseValidation) {
                            this.fieldData.validation_option = 'custom';
                            this.fieldData.validation_custom = baseValidation;
                            this.fieldData.validation_regex = '';
                        }

                        this.fieldData.validation_rules = formatValidationRuleValue(this.fieldData);
                    }

                    // Auto-fill labels from attribute type translations
                    this.locales.forEach(locale => {
                        if (!this.fieldData.labels[locale.code]) {
                            if (Array.isArray(attributeType.translations)) {
                                const translation = attributeType.translations.find(t => t.locale === locale.code);
                                if (translation?.name) {
                                    this.fieldData.labels[locale.code] = translation.name;
                                    return;
                                }
                                if (attributeType.translations[0]?.name) {
                                    this.fieldData.labels[locale.code] = attributeType.translations[0].name;
                                }
                            }
                        }
                    });

                    // Auto-generate code if not set
                    if (!this.fieldData.code && attributeType.code) {
                        this.fieldData.code = attributeType.code;
                    }
                },

                async createField() {
                    // Validate pivotId
                    if (!this.pivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.group-id-required')",
                        });
                        return;
                    }

                    // Validate
                    if (!this.fieldData.service_attribute_type_id) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.missing-required-fields')",
                        });
                        return;
                    }

                    if (!this.fieldData.code || !this.fieldData.code.trim()) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-code-required')",
                        });
                        return;
                    }

                    let hasLabel = false;
                    for (const locale of this.locales) {
                        if (this.fieldData.labels[locale.code] && this.fieldData.labels[locale.code].trim()) {
                            hasLabel = true;
                            break;
                        }
                    }

                    if (!hasLabel) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-label-required')",
                        });
                        return;
                    }

                    this.isSaving = true;

                    try {
                        const validationRule = formatValidationRuleValue(this.fieldData);
                        this.fieldData.validation_rules = validationRule;

                        const fieldPayload = {
                            service_attribute_type_id: this.fieldData.service_attribute_type_id,
                            label: this.fieldData.labels,
                            code: this.fieldData.code,
                            sort_order: this.fieldData.sort_order || 0,
                            is_required: normalizeBoolean(this.fieldData.is_required),
                            validation_rules: validationRule,
                            default_value: normalizeDefaultValue(this.fieldData.default_value),
                        };

                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields`;
                        const response = await this.$axios.post(url, fieldPayload);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.create-success')",
                        });

                        this.$emit('field-created', response.data?.data);

                        this.resetForm();
                        this.$refs.createFieldModal.close();
                    } catch (error) {
                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.groups.fields.error-saving')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isSaving = false;
                    }
                },

                openModal(sortOrder = 0) {
                    this.fieldData.sort_order = sortOrder;
                    this.resetForm();
                    this.$refs.createFieldModal.open();
                },
            },
        });
    </script>
@endPushOnce

