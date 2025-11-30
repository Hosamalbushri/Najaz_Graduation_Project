@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-edit-template"
    >
        <div>
            <x-admin::modal
                ref="editFieldModal"
                @toggle="handleModalToggle"
            >
                <x-slot:header>
                    <p class="text-lg font-bold text-gray-800 dark:text-white">
                        @lang('Admin::app.services.attribute-groups.edit.edit-field-title')
                    </p>
                </x-slot:header>

                <x-slot:content>
                    <div v-if="fieldData">
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
                                :disabled="true"
                                class="cursor-not-allowed bg-gray-100 dark:bg-gray-800"
                            >
                                <option
                                    v-for="attributeType in availableAttributeTypes"
                                    :key="attributeType.id"
                                    :value="attributeType.id"
                                    :selected="attributeType.id == fieldData.service_attribute_type_id"
                                >
                                    @{{ getAttributeTypeName(attributeType) }}
                                </option>
                            </x-admin::form.control-group.control>
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
                                :readonly="true"
                                class="cursor-not-allowed bg-gray-100 dark:bg-gray-800"
                            />
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
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-admin::button
                            button-type="button"
                            button-class="secondary-button"
                            :title="trans('Admin::app.common.cancel')"
                            ::disabled="isSaving"
                            @click="$refs.editFieldModal.close()"
                        />

                        <x-admin::button
                            button-type="button"
                            button-class="primary-button"
                            :title="trans('Admin::app.services.attribute-groups.edit.update-field-btn')"
                            ::disabled="isSaving"
                            ::loading="isSaving"
                            @click="updateField"
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

        const parseValidationRule = (value, options = []) => {
            const stringValue = typeof value === 'string' ? value : (value?.validation || value?.rules || '');
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

        app.component('v-service-data-group-field-edit', {
            template: '#v-service-data-group-field-edit-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    required: true,
                },
                pivotId: {
                    type: [Number, String],
                    default: null,
                },
                field: {
                    type: Object,
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
            },

            emits: ['field-updated'],

            data() {
                const labels = {};
                this.locales.forEach(locale => {
                    labels[locale.code] = '';
                });

                return {
                    isSaving: false,
                    fieldData: null,
                    selectedAttributeType: null,
                };
            },

            watch: {
                field: {
                    handler(newField) {
                        if (newField) {
                            this.loadFieldData(newField);
                        }
                    },
                    immediate: true,
                    deep: true,
                },
                'fieldData.validation_option'(value) {
                    if (!this.fieldData) return;
                    if (value !== 'regex') {
                        this.fieldData.validation_regex = '';
                    }
                    if (value !== 'custom') {
                        this.fieldData.validation_custom = '';
                    }
                    this.fieldData.validation_rules = formatValidationRuleValue(this.fieldData);
                },
                'fieldData.validation_regex'() {
                    if (!this.fieldData) return;
                    if (this.fieldData.validation_option === 'regex') {
                        this.fieldData.validation_rules = formatValidationRuleValue(this.fieldData);
                    }
                },
                'fieldData.validation_custom'() {
                    if (!this.fieldData) return;
                    if (this.fieldData.validation_option === 'custom') {
                        this.fieldData.validation_rules = formatValidationRuleValue(this.fieldData);
                    }
                },
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

            methods: {
                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        // Keep data loaded for re-opening
                    }
                },

                loadFieldData(field) {
                    if (!field) {
                        return;
                    }

                    const labels = {};
                    this.locales.forEach(locale => {
                        if (field.labels && typeof field.labels === 'object') {
                            labels[locale.code] = field.labels[locale.code] || '';
                        } else if (field.label && locale.code === '{{ app()->getLocale() }}') {
                            labels[locale.code] = field.label || '';
                        } else {
                            labels[locale.code] = '';
                        }
                    });

                    const parsedValidation = parseValidationRule(field.validation_rules, this.validations);

                    this.fieldData = {
                        id: field.id,
                        service_attribute_type_id: field.service_attribute_type_id || '',
                        code: field.code || '',
                        labels: labels,
                        sort_order: field.sort_order || 0,
                        is_required: normalizeBoolean(field.is_required),
                        default_value: normalizeDefaultValue(field.default_value),
                        validation_option: parsedValidation.option || '',
                        validation_regex: parsedValidation.regex || '',
                        validation_custom: parsedValidation.custom || '',
                        validation_rules: parsedValidation.value || '',
                    };

                    this.selectedAttributeType = this.getAttributeTypeInfo(this.fieldData.service_attribute_type_id);
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

                async updateField() {
                    if (!this.fieldData || !this.fieldData.id) {
                        return;
                    }

                    // Validate pivotId
                    if (!this.pivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.group-id-required')",
                        });
                        return;
                    }

                    // Validate
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

                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${this.fieldData.id}`;
                        const response = await this.$axios.put(url, fieldPayload);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.update-success')",
                        });

                        this.$emit('field-updated', response.data?.data || this.fieldData);

                        this.$refs.editFieldModal.close();
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

                openModal(field) {
                    if (field && field.id) {
                        this.loadFieldData(field);
                        this.$refs.editFieldModal.open();
                    } else {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                    }
                },
            },
        });
    </script>
@endPushOnce

