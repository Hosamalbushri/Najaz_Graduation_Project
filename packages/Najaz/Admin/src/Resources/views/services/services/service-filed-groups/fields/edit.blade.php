@php
    $currentLocale = core()->getRequestedLocale();
@endphp

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-edit-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, update)">
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
                                ::value="fieldData?.service_attribute_type_id"
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

                        <!-- Hidden field for code (not editable) -->
                        <x-admin::form.control-group.control
                            type="hidden"
                            name="code"
                            ::value="fieldData?.code"
                        />

                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.attribute-groups.edit.field-label') ({{ $currentLocale->name }})
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="label"
                                rules="required"
                                ::value="fieldLabel"
                                :label="trans('Admin::app.services.attribute-groups.edit.field-label')"
                                placeholder="{{ trans('Admin::app.services.attribute-groups.edit.field-label') }} ({{ $currentLocale->name }})"
                            />

                            <x-admin::form.control-group.error control-name="label" />
                        </x-admin::form.control-group>

                        <!-- Hidden field for current locale -->
                        <x-admin::form.control-group.control
                            type="hidden"
                            name="locale"
                            value="{{ $currentLocale->code }}"
                        />

                        <x-admin::form.control-group v-if="canHaveDefaultValue">
                            <x-admin::form.control-group.label>
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.default-value')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="select"
                                name="default_value"
                                ::value="fieldData?.default_value"
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

                            <template v-if="showRegexField">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-regex')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="validation_regex"
                                        ::value="fieldData?.validation_regex"
                                        :placeholder="trans('Admin::app.services.attribute-groups.attribute-group-fields.validation-regex-placeholder')"
                                    />
                                </x-admin::form.control-group>
                            </template>

                            <template v-if="showCustomField">
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('Admin::app.services.attribute-groups.attribute-group-fields.validation-custom-rule')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="validation_custom"
                                        ::value="fieldData?.validation_custom"
                                        :placeholder="trans('Admin::app.services.attribute-groups.attribute-group-fields.validation-rules-placeholder')"
                                    />
                                </x-admin::form.control-group>
                            </template>

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
                                ::checked="fieldData?.is_required"
                            />

                            <x-admin::form.control-group.error control-name="is_required" />

                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                @lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required-help')
                            </p>
                        </x-admin::form.control-group>

                        <template v-if="canShowFileControls || canShowImageControls">
                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.allowed-extensions')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="multiselect"
                                    name="allowed_extensions"
                                    v-model="allowedExtensions"
                                    :label="trans('Admin::app.services.attribute-groups.attribute-group-fields.allowed-extensions')"
                                >
                                    <option
                                        v-for="ext in availableExtensions"
                                        :key="ext.value"
                                        :value="ext.value"
                                    >
                                        @{{ ext.label }}
                                    </option>
                                </x-admin::form.control-group.control>

                                <x-admin::form.control-group.error control-name="allowed_extensions" />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.allowed-extensions-help')
                                </p>
                            </x-admin::form.control-group>

                            <x-admin::form.control-group>
                                <x-admin::form.control-group.label>
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.max-file-size')
                                </x-admin::form.control-group.label>

                                <x-admin::form.control-group.control
                                    type="number"
                                    name="max_file_size"
                                    v-model="maxFileSize"
                                    :placeholder="trans('Admin::app.services.attribute-groups.attribute-group-fields.max-file-size-placeholder')"
                                    min="1"
                                    step="1"
                                />

                                <x-admin::form.control-group.error control-name="max_file_size" />

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.max-file-size-help')
                                </p>
                            </x-admin::form.control-group>
                        </template>
                    </div>
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-admin::button
                            button-type="button"
                            button-class="secondary-button"
                            :title="trans('Admin::app.common.cancel')"
                            ::disabled="isLoading"
                            @click="$refs.editFieldModal.close()"
                        />

                        <x-admin::button
                            button-type="submit"
                            button-class="primary-button"
                            :title="trans('Admin::app.services.attribute-groups.edit.update-field-btn')"
                            ::loading="isLoading"
                            ::disabled="isLoading"
                        />
                    </div>
                </x-slot:footer>
            </x-admin::modal>
            </form>
        </x-admin::form>
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
            // Handle JSON format: {"validation": "mimes:pdf,doc,docx|max:2048"}
            let stringValue = '';
            if (typeof value === 'string') {
                stringValue = value;
            } else if (value && typeof value === 'object') {
                // If it's an object, extract the validation string
                stringValue = value.validation || value.rules || '';
            } else {
                stringValue = '';
            }
            const normalizedOptions = Array.isArray(options) ? options : [];

            if (!stringValue) {
                return {
                    value: '',
                    option: '',
                    regex: '',
                    custom: '',
                    allowed_extensions: [],
                    max_file_size: '',
                };
            }

            // Parse Laravel validation rules format: mimes:pdf,doc,docx|max:2048
            let allowedExtensions = [];
            let maxFileSize = '';
            let remainingRules = stringValue;

            // Extract mimes rule
            const mimesMatch = stringValue.match(/mimes:([^|]+)/);
            if (mimesMatch) {
                // Convert to array for v-model
                allowedExtensions = mimesMatch[1].split(',').map(ext => ext.trim()).filter(ext => ext);
                remainingRules = remainingRules.replace(/mimes:[^|]+(\||$)/g, '').trim();
            }

            // Extract max rule
            const maxMatch = stringValue.match(/max:(\d+)/);
            if (maxMatch) {
                maxFileSize = maxMatch[1];
                remainingRules = remainingRules.replace(/max:\d+(\||$)/g, '').trim();
            }

            // If we found file/image rules, return them
            if (allowedExtensions.length > 0 || maxFileSize) {
                // Reconstruct the validation string
                const fileRules = [];
                if (allowedExtensions.length > 0) {
                    fileRules.push(`mimes:${allowedExtensions.join(',')}`);
                }
                if (maxFileSize) {
                    fileRules.push(`max:${maxFileSize}`);
                }
                if (remainingRules) {
                    fileRules.push(remainingRules);
                }
                return {
                    value: fileRules.join('|'),
                    option: '',
                    regex: '',
                    custom: '',
                    allowed_extensions: allowedExtensions, // Already an array
                    max_file_size: maxFileSize,
                };
            }

            // Handle regex rules
            if (stringValue.startsWith('regex:')) {
                return {
                    value: stringValue,
                    option: 'regex',
                    regex: stringValue.substring(6),
                    custom: '',
                    allowed_extensions: [],
                    max_file_size: '',
                };
            }

            // Handle standard validation options
            if (normalizedOptions.includes(stringValue)) {
                return {
                    value: stringValue,
                    option: stringValue,
                    regex: '',
                    custom: '',
                    allowed_extensions: [],
                    max_file_size: '',
                };
            }

            // Handle custom rules
            return {
                value: stringValue,
                option: 'custom',
                regex: '',
                custom: stringValue,
                allowed_extensions: [],
                max_file_size: '',
            };
        };

        const formatValidationRuleValue = (field) => {
            if (!field) {
                return '';
            }

            // Handle file/image specific rules - Laravel format
            const fileRules = [];
            if (field.allowed_extensions) {
                // Handle both array and string formats
                let extensions = [];
                if (Array.isArray(field.allowed_extensions)) {
                    extensions = field.allowed_extensions.filter(ext => ext && ext.trim());
                } else if (typeof field.allowed_extensions === 'string') {
                    extensions = field.allowed_extensions.split(',').map(ext => ext.trim()).filter(ext => ext);
                }
                if (extensions.length > 0) {
                    fileRules.push(`mimes:${extensions.join(',')}`);
                }
            }
            if (field.max_file_size) {
                const maxSize = parseInt(field.max_file_size);
                if (maxSize > 0) {
                    fileRules.push(`max:${maxSize}`);
                }
            }

            // If we have file/image rules, return them
            if (fileRules.length > 0) {
                return fileRules.join('|');
            }

            // Handle text validation rules
            if (!field.validation_option) {
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
                fileExtensions: {
                    type: Array,
                    default: () => [],
                },
                locales: {
                    type: Array,
                    default: () => [],
                },
            },

            emits: ['field-updated'],

            data() {
                return {
                    isLoading: false,
                    fieldData: null,
                    selectedAttributeType: null,
                    allowedExtensions: [],
                    maxFileSize: '',
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
                'fieldData.service_attribute_type_id': {
                    handler(newTypeId) {
                        if (newTypeId) {
                            this.selectedAttributeType = this.getAttributeTypeInfo(newTypeId);
                        } else {
                            this.selectedAttributeType = null;
                        }
                    },
                    immediate: true,
                },
            },

            computed: {
                availableAttributeTypes() {
                    return Array.isArray(this.attributeTypes) ? this.attributeTypes : [];
                },

                validationsList() {
                    return Array.isArray(this.validations) ? this.validations : [];
                },

                availableExtensions() {
                    return Array.isArray(this.fileExtensions) ? this.fileExtensions : [];
                },

                canShowValidationControls() {
                    if (!this.selectedAttributeType) {
                        return false;
                    }
                    const supportedTypes = ['text', 'textarea', 'number', 'price', 'email', 'url'];
                    return supportedTypes.includes(this.selectedAttributeType.type);
                },

                canHaveDefaultValue() {
                    return this.selectedAttributeType?.type === 'boolean';
                },

                showRegexField() {
                    return this.fieldData?.validation_option === 'regex';
                },

                showCustomField() {
                    return this.fieldData?.validation_option === 'custom';
                },

                canShowFileControls() {
                    return this.selectedAttributeType?.type === 'file';
                },

                canShowImageControls() {
                    return this.selectedAttributeType?.type === 'image';
                },

                fieldLabel() {
                    if (!this.fieldData) return '';
                    
                    // Get current locale code from service locale
                    const currentLocaleCode = '{{ $currentLocale->code }}';
                    
                    // Only return label if translation exists for current locale
                    if (this.fieldData.labels && typeof this.fieldData.labels === 'object') {
                        const label = this.fieldData.labels[currentLocaleCode];
                        if (label && label.trim()) {
                            return label;
                        }
                    }
                    
                    // Return empty if no translation for current locale
                    return '';
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

                    const parsedValidation = parseValidationRule(field.validation_rules, this.validations);

                    // Extract labels object from field
                    let labels = {};
                    if (field.labels && typeof field.labels === 'object') {
                        labels = field.labels;
                    } else if (field.translations && Array.isArray(field.translations)) {
                        // Convert translations array to labels object
                        field.translations.forEach(trans => {
                            if (trans.locale && trans.label) {
                                labels[trans.locale] = trans.label;
                            }
                        });
                    }

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

                    // Set file/image specific fields
                    this.allowedExtensions = Array.isArray(parsedValidation.allowed_extensions) 
                        ? parsedValidation.allowed_extensions 
                        : (parsedValidation.allowed_extensions ? [parsedValidation.allowed_extensions] : []);
                    this.maxFileSize = parsedValidation.max_file_size || '';

                    this.selectedAttributeType = this.getAttributeTypeInfo(this.fieldData.service_attribute_type_id);
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
                    const currentLocale = '{{ $currentLocale->code }}';
                    const translation = attributeType.translations.find(t => t.locale === currentLocale);
                    if (translation && translation.name) {
                        return translation.name;
                    }
                    if (attributeType.translations.length > 0 && attributeType.translations[0].name) {
                        return attributeType.translations[0].name;
                    }
                    return attributeType.code || '';
                },

                async update(params, { setErrors }) {
                    if (!this.fieldData || !this.fieldData.id) {
                        return;
                    }

                    if (!this.pivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.group-id-required')",
                        });
                        return;
                    }

                    this.isLoading = true;

                    try {
                        // Merge params with file/image specific fields
                        const paramsWithFileData = {
                            ...params,
                            allowed_extensions: this.allowedExtensions,
                            max_file_size: this.maxFileSize,
                        };
                        const validationRule = formatValidationRuleValue(paramsWithFileData);
                        const fieldPayload = {
                            service_attribute_type_id: this.fieldData.service_attribute_type_id,
                            label: params.label,
                            locale: params.locale || '{{ $currentLocale->code }}',
                            code: this.fieldData.code,
                            sort_order: params.sort_order || this.fieldData.sort_order || 0,
                            is_required: normalizeBoolean(params.is_required),
                            validation_rules: validationRule,
                            default_value: normalizeDefaultValue(params.default_value),
                        };

                        const url = `{{ route('admin.services.groups.fields.update', ['serviceId' => ':serviceId', 'pivotId' => ':pivotId', 'fieldId' => ':fieldId']) }}`
                            .replace(':serviceId', this.serviceId)
                            .replace(':pivotId', this.pivotId)
                            .replace(':fieldId', this.fieldData.id);
                        const response = await this.$axios.put(url, fieldPayload);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.update-success')",
                        });

                        this.$emit('field-updated', response.data?.data || this.fieldData);

                        this.$refs.editFieldModal.close();
                    } catch (error) {
                        if (error.response?.data?.errors) {
                            setErrors(error.response.data.errors);
                        }

                        const message = error.response?.data?.message || 
                            error.message || 
                            "@lang('Admin::app.services.services.groups.fields.error-saving')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isLoading = false;
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

