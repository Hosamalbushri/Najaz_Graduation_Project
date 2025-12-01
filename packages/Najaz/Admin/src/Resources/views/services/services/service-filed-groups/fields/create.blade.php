@php
    $currentLocale = core()->getRequestedLocale();
@endphp

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-service-data-group-field-create-template"
    >
        <x-admin::form
            v-slot="{ meta, errors, handleSubmit }"
            as="div"
        >
            <form @submit="handleSubmit($event, create)">
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
                            @lang('Admin::app.services.attribute-groups.edit.field-label') ({{ $currentLocale->name }})
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="text"
                            name="label"
                            rules="required"
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

                    <!-- Hidden field for sort order -->
                    <x-admin::form.control-group.control
                        type="hidden"
                        name="sort_order"
                        ::value="sortOrder"
                    />

                    <x-admin::form.control-group v-if="canHaveDefaultValue">
                        <x-admin::form.control-group.label>
                            @lang('Admin::app.services.attribute-groups.attribute-group-fields.default-value')
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            type="select"
                            name="default_value"
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
                                    v-model="validationOption"
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
                </x-slot:content>

                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-end gap-2">
                        <x-admin::button
                            button-type="button"
                            button-class="secondary-button"
                            :title="trans('Admin::app.common.cancel')"
                            ::disabled="isLoading"
                            @click="$refs.createFieldModal.close()"
                        />

                        <x-admin::button
                            button-type="submit"
                            button-class="primary-button"
                            :title="trans('Admin::app.services.attribute-groups.edit.save-field-btn')"
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
                fileExtensions: {
                    type: Array,
                    default: () => [],
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
                return {
                    isLoading: false,
                    selectedAttributeType: null,
                    validationOption: '',
                    allowedExtensions: [],
                    maxFileSize: '',
                };
            },

            computed: {
                availableAttributeTypes() {
                    return Array.isArray(this.attributeTypes) ? this.attributeTypes : [];
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
                    return this.validationOption === 'regex';
                },

                showCustomField() {
                    return this.validationOption === 'custom';
                },

                canShowFileControls() {
                    return this.selectedAttributeType?.type === 'file';
                },

                canShowImageControls() {
                    return this.selectedAttributeType?.type === 'image';
                },
            },

            methods: {
                handleModalToggle(isOpen) {
                    if (!isOpen) {
                        this.selectedAttributeType = null;
                        this.validationOption = '';
                        this.allowedExtensions = [];
                        this.maxFileSize = '';
                    }
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

                onSelectedAttributeTypeChange(event) {
                    const attributeTypeId = event.target.value;
                    const attributeType = this.getAttributeTypeInfo(attributeTypeId);

                    if (!attributeType) {
                        this.selectedAttributeType = null;
                        return;
                    }

                    this.selectedAttributeType = attributeType;
                },

                async create(params, { resetForm, setErrors }) {
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
                            service_attribute_type_id: params.service_attribute_type_id,
                            label: params.label,
                            locale: params.locale || '{{ $currentLocale->code }}',
                            sort_order: params.sort_order || 0,
                            is_required: normalizeBoolean(params.is_required),
                            validation_rules: validationRule,
                            default_value: normalizeDefaultValue(params.default_value),
                        };

                        const url = `{{ route('admin.services.groups.fields.store', ['serviceId' => ':serviceId', 'pivotId' => ':pivotId']) }}`
                            .replace(':serviceId', this.serviceId)
                            .replace(':pivotId', this.pivotId);
                        const response = await this.$axios.post(url, fieldPayload);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.create-success')",
                        });

                        this.$emit('field-created', response.data?.data);

                        resetForm();
                        this.$refs.createFieldModal.close();
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

                openModal(sortOrder = 0) {
                    this.selectedAttributeType = null;
                    this.$refs.createFieldModal.open();
                },
            },
        });
    </script>
@endPushOnce

