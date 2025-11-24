<script type="text/x-template" id="v-service-group-fields-manager-template">
    <div>
        <x-admin::modal ref="manageFieldsModal">
            <x-slot:header>
                <p class="text-lg font-bold text-gray-800 dark:text-white">
                    @lang('Admin::app.services.services.groups.fields.edit.title')
                </p>
            </x-slot:header>

            <x-slot:content>
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.services.groups.fields.edit.fields-title')
                        </p>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-300">
                            @lang('Admin::app.services.services.groups.fields.edit.fields-info')
                        </p>
                    </div>

                    <div
                            class="secondary-button cursor-pointer"
                            @click="openAddFieldModal"
                    >
                        @lang('Admin::app.services.services.groups.fields.edit.add-field-btn')
                    </div>
                </div>

                <div v-if="fields.length" class="grid">
                    <draggable
                            ghost-class="draggable-ghost"
                            v-bind="{ animation: 200 }"
                            handle=".icon-drag"
                            :list="fields"
                            item-key="uid"
                            @end="recalculateSortOrders"
                    >
                        <template #item="{ element, index }">
                            <div class="mb-2.5 rounded border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                                <div class="flex items-center justify-between gap-4 p-4">
                                    <div class="flex flex-1 items-start gap-2.5">
                                        <i class="icon-drag cursor-grab text-xl transition-all hover:text-gray-700 dark:text-gray-300"></i>

                                        <div class="flex flex-col gap-1">
                                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                                @{{ displayFieldTitle(element, index) }}
                                            </p>

                                            <p class="text-xs text-gray-500 dark:text-gray-300" v-if="element.labels && Object.keys(element.labels).length">
                                                @{{ displayFieldLocales(element) }}
                                            </p>

                                            <div
                                                    v-if="element.service_attribute_type_id && getAttributeTypeInfo(element.service_attribute_type_id)"
                                                    class="text-xs text-gray-500 dark:text-gray-400"
                                            >
                                                <p>
                                                    <strong>@lang('Admin::app.services.attribute-groups.attribute-group-fields.field-type'):</strong>
                                                    @{{ getAttributeTypeInfo(element.service_attribute_type_id).type }}
                                                </p>

                                                <p>
                                                    <strong>@lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required'):</strong>
                                                    @{{ element.is_required ? yesLabel : noLabel }}
                                                </p>

                                                <p v-if="formatValidationRule(element)">
                                                    <strong>@lang('Admin::app.services.attribute-groups.attribute-group-fields.validation'):</strong>
                                                    @{{ displayValidationLabel(element) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 text-sm font-medium">
                                            <span
                                                    class="cursor-pointer text-blue-600 transition-all hover:underline"
                                                    @click="openEditFieldModal(index)"
                                            >
                                                @lang('Admin::app.services.attribute-groups.edit.edit-field-btn')
                                            </span>

                                        <span
                                                v-if="fieldRequiresOptions(element)"
                                                class="cursor-pointer text-green-600 transition-all hover:underline"
                                                @click="openAddOptionModalForField(element)"
                                        >
                                                @lang('Admin::app.services.services.groups.fields.options.add-option')
                                            </span>

                                        <span
                                                class="cursor-pointer text-red-600 transition-all hover:underline"
                                                @click="removeField(element, index)"
                                        >
                                                @lang('Admin::app.services.attribute-groups.edit.delete-field-btn')
                                            </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </draggable>
                </div>

                <div
                        v-else
                        class="grid justify-center justify-items-center gap-3.5 px-2.5 py-10 text-center"
                >
                    <img
                            src="{{ bagisto_asset('images/icon-options.svg') }}"
                            class="h-20 w-20 rounded border border-dashed dark:border-gray-800 dark:mix-blend-exclusion dark:invert"
                    />

                    <div class="flex flex-col items-center gap-1.5">
                        <p class="text-base font-semibold text-gray-400">
                            @lang('Admin::app.services.services.groups.fields.edit.no-fields')
                        </p>

                        <p class="text-gray-400">
                            @lang('Admin::app.services.services.groups.fields.edit.no-fields-info')
                        </p>
                    </div>

                    <div
                            class="secondary-button text-sm cursor-pointer"
                            @click="openAddFieldModal"
                    >
                        @lang('Admin::app.services.services.groups.fields.edit.add-field-btn')
                    </div>
                </div>

                <x-admin::form as="div" v-slot="{ handleSubmit }">
                    <form @submit="handleSubmit($event, updateOrCreateField)">
                        <x-admin::modal ref="updateCreateFieldModal">
                            <x-slot:header>
                                <p class="text-lg font-bold text-gray-800 dark:text-white">
                                    @{{ selectedFieldIndex === null ? uiTexts.add_field_title : uiTexts.edit_field_title }}
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
                                            v-model="selectedField.service_attribute_type_id"
                                            ::disabled="selectedField.id"
                                            label="{{ trans('Admin::app.services.attribute-groups.attribute-group-fields.field-type') }}"
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

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.services.groups.fields.edit.field-code')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                            type="text"
                                            name="code"
                                            rules="required"
                                            v-model="selectedField.code"
                                            ::disabled="selectedField.id"
                                            label="{{ trans('Admin::app.services.services.groups.fields.edit.field-code') }}"
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
                                            type="switch"
                                            name="is_required"
                                            value="1"
                                            ::checked="selectedField.is_required"
                                            @change="selectedField.is_required = $event.target.checked"
                                    />

                                    <x-admin::form.control-group.error control-name="is_required" />

                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required-help')
                                    </p>
                                </x-admin::form.control-group>

                            </x-slot:content>

                            <x-slot:footer>
                                <button
                                        type="submit"
                                        class="primary-button"
                                >
                                    @{{ selectedFieldIndex === null ? uiTexts.save_field_btn : uiTexts.update_field_btn }}
                                </button>
                            </x-slot:footer>
                        </x-admin::modal>
                    </form>
                </x-admin::form>

                <!-- Add/Edit Option Modal -->
                <x-admin::modal ref="addEditOptionModal">
                    <x-slot:header>
                        <p class="text-lg font-bold text-gray-800 dark:text-white">
                            @{{ selectedOptionIndex === null ? '@lang("Admin::app.services.services.groups.fields.options.add-option")' : '@lang("Admin::app.services.services.groups.fields.options.edit-option")' }}
                        </p>
                    </x-slot:header>

                    <x-slot:content>
                        <x-admin::form.control-group>
                            <x-admin::form.control-group.label class="required">
                                @lang('Admin::app.services.services.groups.fields.options.admin-name')
                            </x-admin::form.control-group.label>

                            <x-admin::form.control-group.control
                                type="text"
                                name="option_admin_name"
                                rules="required"
                                v-model="selectedOption.admin_name"
                                ::placeholder="trans('Admin::app.services.services.groups.fields.options.admin-name-placeholder')"
                            />

                            <x-admin::form.control-group.error control-name="option_admin_name" />
                        </x-admin::form.control-group>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div
                                v-for="locale in locales"
                                :key="`option-${locale.code}`"
                            >
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.services.groups.fields.options.label') (@{{ locale.name }})
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        ::name="`option_labels[${locale.code}]`"
                                        rules="required"
                                        v-model="selectedOption.labels[locale.code]"
                                        ::placeholder="locale.name"
                                    />

                                    <x-admin::form.control-group.error ::control-name="`option_labels[${locale.code}]`" />
                                </x-admin::form.control-group>
                            </div>
                        </div>
                    </x-slot:content>

                    <x-slot:footer>
                        <div class="flex items-center justify-between w-full">
                            <button
                                v-if="selectedFieldForOption && hasOriginalOptionsForField(selectedFieldForOption) && selectedOptionIndex === null"
                                type="button"
                                class="secondary-button text-sm"
                                @click="syncOptionsFromOriginal(selectedFieldForOption.id)"
                            >
                                @lang('Admin::app.services.services.groups.fields.options.sync-from-original')
                            </button>
                            <div class="flex gap-2 ml-auto">
                                <button
                                    type="button"
                                    class="secondary-button"
                                    @click="$refs.addEditOptionModal.close()"
                                >
                                    @lang('Admin::app.common.cancel')
                                </button>

                                <button
                                    type="button"
                                    class="primary-button"
                                    @click="saveOption"
                                    :disabled="isSavingOption"
                                >
                                    @{{ selectedOptionIndex === null ? '@lang("Admin::app.services.services.groups.fields.options.add-option")' : '@lang("Admin::app.services.services.groups.fields.options.update-option")' }}
                                </button>
                            </div>
                        </div>
                    </x-slot:footer>
                </x-admin::modal>
            </x-slot:content>
        </x-admin::modal>
    </div>
</script>

@pushOnce('scripts')
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

        app.component('v-service-group-fields-manager', {
            template: '#v-service-group-fields-manager-template',

            props: {
                pivotRelation: {
                    type: Object,
                    required: true
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
                locales: {
                    type: Array,
                    required: true
                },
                serviceId: {
                    type: [Number, String],
                    required: true
                },
                pivotId: {
                    type: [Number, String],
                    required: true
                },
                autoOpenModal: {
                    type: Boolean,
                    default: false
                }
            },

            emits: ['fields-updated'],

            data() {
                const validationOptions = Array.isArray(this.validations) ? this.validations : [];

                const normalizeField = (field) => {
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

                    const parsedValidation = parseValidationRule(field.validation_rules, validationOptions);

                    return {
                        id: field.id ?? null,
                        uid: `field_${field.id ?? Math.random().toString(36).slice(2, 11)}`,
                        code: field.code || '',
                        service_attribute_type_id: field.service_attribute_type_id ?? '',
                        labels,
                        sort_order: field.sort_order ?? 0,
                        is_required: normalizeBoolean(field.is_required),
                        default_value: normalizeDefaultValue(field.default_value),
                        validation_option: parsedValidation.option,
                        validation_regex: parsedValidation.regex,
                        validation_custom: parsedValidation.custom,
                        validation_rules: parsedValidation.value,
                    };
                };

                const initialFields = (this.pivotRelation?.fields || [])
                    .map(field => normalizeField(field))
                    .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));

                const emptyField = normalizeField({
                    id: null,
                    code: '',
                    service_attribute_type_id: '',
                    labels: {},
                    translations: [],
                    sort_order: initialFields.length,
                    is_required: false,
                    default_value: '',
                    validation_rules: '',
                });

                return {
                    isSaving: false,
                    fields: initialFields,
                    locales: this.locales,
                    attributeTypesList: this.attributeTypes || [],
                    selectedFieldIndex: null,
                    selectedField: emptyField,
                    selectedFieldOptions: [], // Options for the currently selected field
                    selectedOptionIndex: null,
                    selectedOption: {
                        id: null,
                        uid: null,
                        service_attribute_type_option_id: null,
                        admin_name: '',
                        labels: {},
                        sort_order: 0,
                        is_custom: true,
                    },
                    isSavingOption: false,
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

                requiresOptions() {
                    if (!this.selectedAttributeType) {
                        return false;
                    }
                    const optionTypes = ['select', 'multiselect', 'radio', 'checkbox'];
                    const type = this.selectedAttributeType.type;
                    return type && optionTypes.includes(type);
                },

                hasOriginalOptions() {
                    if (!this.selectedAttributeType) {
                        return false;
                    }
                    const options = this.selectedAttributeType.options;
                    return options && Array.isArray(options) && options.length > 0;
                },
            },

            mounted() {
                // Auto-open the internal modal when component is mounted (for use in external modals)
                if (this.autoOpenModal) {
                    this.$nextTick(() => {
                        if (this.$refs.manageFieldsModal) {
                            this.$refs.manageFieldsModal.open();
                        }
                    });
                }
            },

            watch: {
                pivotRelation: {
                    handler(newVal) {
                        if (newVal && Array.isArray(newVal.fields)) {
                            const validationOptions = Array.isArray(this.validations) ? this.validations : [];
                            
                            const normalizeField = (field) => {
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

                                const parsedValidation = parseValidationRule(field.validation_rules, validationOptions);

                                return {
                                    id: field.id ?? null,
                                    uid: `field_${field.id ?? Math.random().toString(36).slice(2, 11)}`,
                                    code: field.code || '',
                                    service_attribute_type_id: field.service_attribute_type_id ?? '',
                                    labels,
                                    sort_order: field.sort_order ?? 0,
                                    is_required: normalizeBoolean(field.is_required),
                                    default_value: normalizeDefaultValue(field.default_value),
                                    validation_option: parsedValidation.option,
                                    validation_regex: parsedValidation.regex,
                                    validation_custom: parsedValidation.custom,
                                    validation_rules: parsedValidation.value,
                                };
                            };

                            this.fields = (newVal.fields || [])
                                .map(field => normalizeField(field))
                                .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
                        } else {
                            // If no fields, set empty array
                            this.fields = [];
                        }
                    },
                    immediate: true,
                    deep: true
                },
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

                formatValidationRule(field) {
                    if (!field) {
                        return '';
                    }

                    if (field.validation_option || field.validation_regex || field.validation_custom) {
                        return formatValidationRuleValue(field);
                    }

                    const parsed = parseValidationRule(field.validation_rules, this.validationOptionsList);

                    return formatValidationRuleValue({
                        validation_option: parsed.option,
                        validation_regex: parsed.regex,
                        validation_custom: parsed.custom,
                    });
                },

                displayValidationLabel(field) {
                    const parsed = parseValidationRule(field?.validation_rules, this.validationOptionsList);

                    if (!parsed.value) {
                        return '';
                    }

                    if (parsed.option === 'regex') {
                        const label = this.translateValidationOption('regex');
                        const regex = parsed.regex || field?.validation_regex || '';
                        return regex ? `${label} (${regex})` : label;
                    }

                    if (parsed.option === 'custom') {
                        return parsed.custom || field?.validation_custom || parsed.value;
                    }

                    return this.translateValidationOption(parsed.option);
                },

                openAddFieldModal() {
                    this.selectedFieldIndex = null;
                    this.selectedField = this.getEmptyField();
                    // Clear options for new field
                    this.selectedFieldOptions = [];
                    this.$refs.updateCreateFieldModal.open();
                },

                openEditFieldModal(index) {
                    const field = this.fields[index];
                    this.selectedFieldIndex = index;

                    const parsedValidation = parseValidationRule(field.validation_rules, this.validationOptionsList);

                    this.selectedField = {
                        id: field.id,
                        uid: field.uid,
                        code: field.code,
                        service_attribute_type_id: field.service_attribute_type_id,
                        labels: JSON.parse(JSON.stringify(field.labels)),
                        sort_order: field.sort_order,
                        is_required: normalizeBoolean(field.is_required),
                        default_value: normalizeDefaultValue(field.default_value),
                        validation_option: parsedValidation.option || field.validation_option || '',
                        validation_regex: parsedValidation.regex || field.validation_regex || '',
                        validation_custom: parsedValidation.option === 'custom'
                            ? (parsedValidation.custom || field.validation_custom || '')
                            : '',
                        validation_rules: formatValidationRuleValue({
                            validation_option: parsedValidation.option || field.validation_option || '',
                            validation_regex: parsedValidation.regex || field.validation_regex || '',
                            validation_custom: parsedValidation.custom || field.validation_custom || '',
                        }),
                    };

                    // Load field options
                    if (field.id) {
                        this.loadFieldOptions(field.id);
                    } else {
                        this.selectedFieldOptions = [];
                    }

                    this.$refs.updateCreateFieldModal.open();
                },

                async openEditFieldModalByField(fieldData, pivotId, serviceId) {
                    // If we only have basic data, fetch full data from server
                    if (!fieldData.service_attribute_type_id || !fieldData.id) {
                        // Fetch data from server
                        const fullData = await this.loadFieldData(fieldData.id, pivotId, serviceId);
                        this.openEditFieldModalWithData(fullData);
                    } else {
                        this.openEditFieldModalWithData(fieldData);
                    }
                },

                openEditFieldModalWithData(fieldData) {
                    // Convert data to required format
                    const parsedValidation = parseValidationRule(fieldData.validation_rules || fieldData.validation_rules, this.validationOptionsList);
                    
                    // Extract labels from translations if needed
                    const labels = {};
                    if (fieldData.labels) {
                        Object.assign(labels, fieldData.labels);
                    } else if (Array.isArray(fieldData.translations)) {
                        this.locales.forEach(locale => {
                            const translation = fieldData.translations.find(t => t.locale === locale.code);
                            labels[locale.code] = translation?.label || '';
                        });
                    } else {
                        this.locales.forEach(locale => {
                            labels[locale.code] = '';
                        });
                    }
                    
                    this.selectedField = {
                        id: fieldData.id,
                        uid: fieldData.uid || `field_${fieldData.id}`,
                        code: fieldData.code || '',
                        service_attribute_type_id: fieldData.service_attribute_type_id || '',
                        labels: labels,
                        sort_order: fieldData.sort_order || 0,
                        is_required: normalizeBoolean(fieldData.is_required),
                        default_value: normalizeDefaultValue(fieldData.default_value),
                        validation_option: parsedValidation.option || '',
                        validation_regex: parsedValidation.regex || '',
                        validation_custom: parsedValidation.custom || '',
                        validation_rules: formatValidationRuleValue({
                            validation_option: parsedValidation.option || '',
                            validation_regex: parsedValidation.regex || '',
                            validation_custom: parsedValidation.custom || '',
                        }),
                    };
                    
                    // Find field index in fields array
                    if (Array.isArray(this.fields)) {
                        this.selectedFieldIndex = this.fields.findIndex(f => f.id === fieldData.id);
                        if (this.selectedFieldIndex === -1) {
                            this.selectedFieldIndex = null;
                        }
                    } else {
                        this.selectedFieldIndex = null;
                    }
                    
                    this.$refs.updateCreateFieldModal.open();
                },

                async loadFieldData(fieldId, pivotId, serviceId) {
                    try {
                        const url = `{{ url('admin/services') }}/${serviceId}/groups/${pivotId}/fields/${fieldId}`;
                        const response = await this.$axios.get(url);
                        return response.data.data || response.data;
                    } catch (error) {
                        console.error('Error loading field data:', error);
                        throw error;
                    }
                },

                async updateOrCreateField() {
                    this.isSaving = true;

                    const validationRule = formatValidationRuleValue(this.selectedField);
                    this.selectedField.validation_rules = validationRule;

                    const fieldPayload = {
                        service_attribute_type_id: this.selectedField.service_attribute_type_id,
                        label: this.selectedField.labels,
                        code: this.selectedField.code,
                        sort_order: this.selectedField.sort_order ?? 0,
                        is_required: normalizeBoolean(this.selectedField.is_required),
                        validation_rules: validationRule,
                        default_value: normalizeDefaultValue(this.selectedField.default_value),
                    };

                    try {
                        let response;
                        if (this.selectedFieldIndex === null) {
                            // Create new field
                            const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields`;
                            response = await this.$axios.post(url, fieldPayload);
                        } else {
                            // Update existing field
                            const fieldId = this.fields[this.selectedFieldIndex].id;
                            const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${fieldId}`;
                            response = await this.$axios.put(url, fieldPayload);
                        }

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || (this.selectedFieldIndex === null 
                                ? "@lang('Admin::app.services.services.groups.fields.create-success')"
                                : "@lang('Admin::app.services.services.groups.fields.update-success')"),
                        });

                        this.$refs.updateCreateFieldModal.close();
                        this.resetFieldForm();

                        // Emit event to parent to reload fields
                        this.$emit('fields-updated');

                        // Reload fields from server
                        await this.reloadFields();
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

                async removeField(field, index) {
                    if (field.id) {
                        this.$emitter.emit('open-confirm-modal', {
                            agree: async () => {
                                try {
                                    const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${field.id}`;
                                    await this.$axios.delete(url);

                                    this.fields.splice(index, 1);
                                    this.recalculateSortOrders();

                                    this.$emitter.emit('add-flash', {
                                        type: 'success',
                                        message: "@lang('Admin::app.services.services.groups.fields.delete-success')",
                                    });

                                    // Emit event to parent
                                    this.$emit('fields-updated');

                                    // Reload fields from server
                                    await this.reloadFields();
                                } catch (error) {
                                    const message = error.response?.data?.message || 
                                        error.message || 
                                        "@lang('Admin::app.services.services.groups.fields.error-deleting')";

                                    this.$emitter.emit('add-flash', {
                                        type: 'error',
                                        message: message,
                                    });
                                }
                            }
                        });
                    } else {
                        this.fields.splice(index, 1);
                        this.recalculateSortOrders();
                    }
                },

                async reloadFields() {
                    try {
                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/data`;
                        const response = await this.$axios.get(url);

                        if (response.data && response.data.pivotRelation && response.data.pivotRelation.fields) {
                            const validationOptions = Array.isArray(this.validations) ? this.validations : [];

                            const normalizeField = (field) => {
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

                                const parsedValidation = parseValidationRule(field.validation_rules, validationOptions);

                                return {
                                    id: field.id ?? null,
                                    uid: `field_${field.id ?? Math.random().toString(36).slice(2, 11)}`,
                                    code: field.code || '',
                                    service_attribute_type_id: field.service_attribute_type_id ?? '',
                                    labels,
                                    sort_order: field.sort_order ?? 0,
                                    is_required: normalizeBoolean(field.is_required),
                                    default_value: normalizeDefaultValue(field.default_value),
                                    validation_option: parsedValidation.option,
                                    validation_regex: parsedValidation.regex,
                                    validation_custom: parsedValidation.custom,
                                    validation_rules: parsedValidation.value,
                                };
                            };

                            this.fields = response.data.pivotRelation.fields
                                .map(field => normalizeField(field))
                                .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
                        }
                    } catch (error) {
                        console.error('Error reloading fields:', error);
                    }
                },

                onSelectedAttributeTypeChange() {
                    if (!this.selectedField || this.selectedField.id) {
                        return;
                    }

                    const attributeType = this.getAttributeTypeInfo(this.selectedField.service_attribute_type_id);

                    if (!attributeType) {
                        return;
                    }

                    this.selectedField.is_required = normalizeBoolean(attributeType.is_required);
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

                recalculateSortOrders() {
                    this.fields = this.fields.map((field, index) => ({
                        ...field,
                        sort_order: index,
                    }));
                },

                displayFieldTitle(field, index) {
                    const labels = field.labels || {};
                    const primaryLabel = labels[this.currentLocale] && labels[this.currentLocale].trim()
                        ? labels[this.currentLocale].trim()
                        : Object.values(labels).find(label => label && label.trim()) || `${this.defaultFieldName} ${index + 1}`;

                    const attributeTypeInfo = this.getAttributeTypeInfo(field.service_attribute_type_id);
                    const attributeTypeName = attributeTypeInfo ? this.getAttributeTypeName(attributeTypeInfo) : '';

                    return attributeTypeName
                        ? `${index + 1}. ${primaryLabel} - ${attributeTypeName}`
                        : `${index + 1}. ${primaryLabel}`;
                },

                displayFieldLocales(field) {
                    if (!field.labels) {
                        return '';
                    }

                    return this.locales
                        .map(locale => {
                            const value = field.labels[locale.code] || '';
                            return `${locale.code.toUpperCase()}: ${value || '-'}`;
                        })
                        .join(' | ');
                },

                resetFieldForm() {
                    this.selectedFieldIndex = null;
                    this.selectedField = this.getEmptyField();
                },

                getEmptyField() {
                    const labels = {};
                    this.locales.forEach(locale => {
                        labels[locale.code] = '';
                    });

                    return {
                        id: null,
                        uid: this.generateUid(),
                        code: '',
                        service_attribute_type_id: '',
                        labels,
                        sort_order: this.fields.length,
                        is_required: false,
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

                // Option Management Methods
                async loadFieldOptions(fieldId) {
                    if (!fieldId || !this.pivotId) {
                        this.selectedFieldOptions = [];
                        return;
                    }

                    try {
                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${fieldId}/show`;
                        const response = await this.$axios.get(url);

                        if (response.data?.data?.options) {
                            this.selectedFieldOptions = response.data.data.options.map(opt => ({
                                ...opt,
                                uid: opt.uid || `option_${opt.id || Date.now()}`,
                            }));
                        } else {
                            this.selectedFieldOptions = [];
                        }
                    } catch (error) {
                        console.error('Error loading field options:', error);
                        this.selectedFieldOptions = [];
                    }
                },

                openAddOptionModal() {
                    if (!this.selectedField || !this.selectedField.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.groups.fields.options.save-field-first')",
                        });
                        return;
                    }

                    this.selectedOptionIndex = null;
                    this.selectedOption = {
                        id: null,
                        uid: `option_${Date.now()}`,
                        service_attribute_type_option_id: null,
                        admin_name: '',
                        labels: {},
                        sort_order: this.selectedFieldOptions.length,
                        is_custom: true,
                    };

                    // Initialize labels for all locales
                    this.locales.forEach(locale => {
                        this.selectedOption.labels[locale.code] = '';
                    });

                    this.$refs.addEditOptionModal.open();
                },

                openEditOptionModal(optionIndex) {
                    if (optionIndex < 0 || optionIndex >= this.selectedFieldOptions.length) {
                        return;
                    }

                    const option = this.selectedFieldOptions[optionIndex];
                    this.selectedOptionIndex = optionIndex;
                    this.selectedOption = {
                        id: option.id || null,
                        uid: option.uid || `option_${option.id || Date.now()}`,
                        service_attribute_type_option_id: option.service_attribute_type_option_id || null,
                        admin_name: option.admin_name || '',
                        labels: {},
                        sort_order: option.sort_order || 0,
                        is_custom: option.is_custom !== undefined ? option.is_custom : true,
                    };

                    // Initialize labels for all locales
                    this.locales.forEach(locale => {
                        this.selectedOption.labels[locale.code] = option.labels?.[locale.code] || '';
                    });

                    this.$refs.addEditOptionModal.open();
                },

                async saveOption() {
                    // Use selectedFieldForOption if available (from standalone modal), otherwise use selectedField (from edit modal)
                    const fieldId = this.selectedFieldForOption?.id || this.selectedField?.id;
                    
                    if (!fieldId || !this.pivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.groups.fields.options.field-required')",
                        });
                        return;
                    }

                    // Validate
                    if (!this.selectedOption.admin_name || !this.selectedOption.admin_name.trim()) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.groups.fields.options.admin-name-required')",
                        });
                        return;
                    }

                    let hasLabel = false;
                    for (const locale of this.locales) {
                        if (this.selectedOption.labels[locale.code] && this.selectedOption.labels[locale.code].trim()) {
                            hasLabel = true;
                            break;
                        }
                    }

                    if (!hasLabel) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.groups.fields.options.label-required')",
                        });
                        return;
                    }

                    this.isSavingOption = true;

                    try {
                        const payload = {
                            admin_name: this.selectedOption.admin_name,
                            label: this.selectedOption.labels,
                            sort_order: this.selectedOption.sort_order,
                            service_attribute_type_option_id: this.selectedOption.service_attribute_type_option_id,
                        };

                        let response;
                        if (this.selectedOptionIndex === null) {
                            // Create new option
                            const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${fieldId}/options`;
                            response = await this.$axios.post(url, payload);
                        } else {
                            // Update existing option
                            const optionId = this.selectedFieldOptions[this.selectedOptionIndex].id;
                            if (!optionId) {
                                throw new Error('Option ID is required for update');
                            }
                            const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${fieldId}/options/${optionId}`;
                            response = await this.$axios.put(url, payload);
                        }

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || (this.selectedOptionIndex === null
                                ? "@lang('Admin::app.services.services.groups.fields.options.create-success')"
                                : "@lang('Admin::app.services.services.groups.fields.options.update-success')"),
                        });

                        this.$refs.addEditOptionModal.close();
                        await this.loadFieldOptions(fieldId);
                        
                        // Reload fields to update options display
                        await this.reloadFields();
                    } catch (error) {
                        const message = error.response?.data?.message ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.options.error-saving')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    } finally {
                        this.isSavingOption = false;
                    }
                },

                async deleteOption(optionIndex, fieldId = null) {
                    if (optionIndex < 0 || optionIndex >= this.selectedFieldOptions.length) {
                        return;
                    }

                    const option = this.selectedFieldOptions[optionIndex];
                    if (!option.id) {
                        // If no ID, just remove from local array
                        this.selectedFieldOptions.splice(optionIndex, 1);
                        return;
                    }

                    if (!confirm("@lang('Admin::app.services.services.groups.fields.options.confirm-delete')")) {
                        return;
                    }

                    // Use provided fieldId or get from selectedFieldForOption or selectedField
                    const targetFieldId = fieldId || this.selectedFieldForOption?.id || this.selectedField?.id;
                    
                    if (!targetFieldId || !this.pivotId) {
                        return;
                    }

                    try {
                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${targetFieldId}/options/${option.id}`;
                        await this.$axios.delete(url);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: "@lang('Admin::app.services.services.groups.fields.options.delete-success')",
                        });

                        await this.loadFieldOptions(targetFieldId);
                        
                        // Reload fields to update options display
                        await this.reloadFields();
                    } catch (error) {
                        const message = error.response?.data?.message ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.options.delete-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    }
                },

                async syncOptionsFromOriginal(fieldId = null) {
                    const targetFieldId = fieldId || this.selectedFieldForOption?.id || this.selectedField?.id;
                    
                    if (!targetFieldId || !this.pivotId) {
                        return;
                    }

                    try {
                        const url = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${targetFieldId}/options/sync-from-original`;
                        const response = await this.$axios.post(url);

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: response.data?.message || "@lang('Admin::app.services.services.groups.fields.options.sync-success')",
                        });

                        await this.loadFieldOptions(targetFieldId);
                        
                        // Reload fields to update options display
                        await this.reloadFields();
                    } catch (error) {
                        const message = error.response?.data?.message ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.options.sync-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    }
                },

                getOptionDisplayLabel(option) {
                    if (!option) return '';
                    if (option.labels && typeof option.labels === 'object') {
                        if (option.labels[this.currentLocale]) {
                            return option.labels[this.currentLocale];
                        }
                        const labelKeys = Object.keys(option.labels);
                        if (labelKeys.length > 0 && option.labels[labelKeys[0]]) {
                            return option.labels[labelKeys[0]];
                        }
                    }
                    return option.admin_name || option.code || '';
                },
            },
        });
    </script>
@endPushOnce

