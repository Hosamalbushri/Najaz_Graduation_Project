<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.attribute-groups.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.attribute-groups.update', $attributeGroup->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.attribute-groups.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <a
                    href="{{ route('admin.attribute-groups.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.attribute-groups.edit.back-btn')
                </a>

                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('Admin::app.services.attribute-groups.edit.save-btn')
                </button>
            </div>
        </div>

        <v-attribute-group-edit
            :attribute-group="{{ $attributeGroup->toJson() }}"
            :attribute-types="{{ $attributeTypes->toJson() }}"
            :locales='@json(core()->getAllLocales())'
            :validations='@json($validations)'
            :validation-labels='@json($validationLabels)'
        >
            <x-admin::shimmer.catalog.attributes />
        </v-attribute-group-edit>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-attribute-group-edit-template">
            <div class="mt-3.5">
                <div class="flex gap-2.5 max-xl:flex-wrap">
                    <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                            @foreach (core()->getAllLocales() as $locale)
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        {{ __('Admin::app.services.attribute-groups.edit.name'). ' (' . strtoupper($locale->code) . ')' }}
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        :name="'name[' . $locale->code . ']'"
                                        :value="old('name.' . $locale->code, $attributeGroup->translate($locale->code)?->name ?? '')"
                                        :placeholder="$locale->name"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        {{ __('Admin::app.services.attribute-groups.edit.description') .' (' . strtoupper($locale->code) . ')' }}
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="textarea"
                                        :name="'description[' . $locale->code . ']'"
                                        :value="old('description.' . $locale->code, $attributeGroup->translate($locale->code)?->description ?? '')"
                                    />
                                </x-admin::form.control-group>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex w-[360px] max-w-full flex-col gap-2">
                        <x-admin::accordion>
                            <x-slot:header>
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('Admin::app.services.attribute-groups.edit.general')
                                </p>
                            </x-slot:header>
                            <x-slot:content>
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.attribute-groups.edit.code')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="code"
                                        :value="old('code', $attributeGroup->code)"
                                        :placeholder="trans('Admin::app.services.attribute-groups.edit.code')"
                                        readonly
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.attribute-groups.edit.group-type')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="select"
                                        name="group_type"
                                        :value="old('group_type', $attributeGroup->group_type ?? 'general')"
                                        :label="trans('Admin::app.services.attribute-groups.edit.group-type')"
                                    >
                                        <option value="general" @selected(old('group_type', $attributeGroup->group_type ?? 'general') === 'general')>
                                            @lang('Admin::app.services.attribute-groups.options.group-type.general')
                                        </option>

                                    <option value="citizen" @selected(old('group_type', $attributeGroup->group_type ?? 'general') === 'citizen')>
                                            @lang('Admin::app.services.attribute-groups.options.group-type.citizen')
                                        </option>
                                    </x-admin::form.control-group.control>

                                    <x-admin::form.control-group.error control-name="group_type" />

                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        @lang('Admin::app.services.attribute-groups.edit.group-type-help')
                                    </p>
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('Admin::app.services.attribute-groups.edit.sort-order')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="number"
                                        name="sort_order"
                                        :value="old('sort_order', $attributeGroup->sort_order)"
                                        :placeholder="trans('Admin::app.services.attribute-groups.edit.sort-order')"
                                    />
                                </x-admin::form.control-group>
                            </x-slot:content>
                        </x-admin::accordion>
                    </div>
                </div>

                <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between">
                        <div>
                            <p class="text-base font-semibold text-gray-800 dark:text-white">
                                @lang('Admin::app.services.attribute-groups.edit.fields-title')
                            </p>

                            <p class="text-xs provar font-medium text-gray-500 dark:text-gray-300">
                                @lang('Admin::app.services.attribute-groups.edit.fields-info')
                            </p>
                        </div>

                        <div
                            class="secondary-button"
                            @click="openAddFieldModal"
                        >
                            @lang('Admin::app.services.attribute-groups.edit.add-field-btn')
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
                                    <input
                                        v-if="element.id"
                                        type="hidden"
                                        :name="`fields[${index}][id]`"
                                        :value="element.id"
                                    />

                                    <input
                                        type="hidden"
                                        :name="`fields[${index}][service_attribute_type_id]`"
                                        :value="element.service_attribute_type_id"
                                    />

                                    <input
                                        type="hidden"
                                        :name="`fields[${index}][sort_order]`"
                                        :value="index"
                                    />

                                    <template v-for="locale in locales" :key="`${element.uid}-${locale.code}`">
                                        <input
                                            type="hidden"
                                            :name="`fields[${index}][label][${locale.code}]`"
                                            :value="element.labels?.[locale.code] ?? ''"
                                        />
                                    </template>

                                    <input
                                        type="hidden"
                                        :name="`fields[${index}][is_required]`"
                                        :value="element.is_required ? 1 : 0"
                                    />

                                    <input
                                        type="hidden"
                                        :name="`fields[${index}][default_value]`"
                                        :value="element.default_value ?? ''"
                                    />

                                    <input
                                        type="hidden"
                                        :name="`fields[${index}][validation_rules]`"
                                        :value="formatValidationRule(element)"
                                    />

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

                                                    <p v-if="getAttributeTypeInfo(element.service_attribute_type_id)?.validation">
                                                        <strong>@lang('Admin::app.services.attribute-types.index.datagrid.validation'):</strong>
                                                        @{{ getAttributeTypeInfo(element.service_attribute_type_id).validation }}
                                                    </p>

                                                    <p>
                                                        <strong>@lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required'):</strong>
                                                        @{{ element.is_required ? yesLabel : noLabel }}
                                                    </p>

                                                    <p v-if="formatValidationRule(element)">
                                                        <strong>@lang('Admin::app.services.attribute-groups.attribute-group-fields.validation'):</strong>
                                                        @{{ displayValidationLabel(element) }}
                                                    </p>

                                                    <p v-if="getAttributeTypeInfo(element.service_attribute_type_id)?.is_unique">
                                                        <strong>@lang('Admin::app.services.attribute-types.index.datagrid.is-unique'):</strong>
                                                        @lang('Admin::app.common.yes')
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
                                @lang('Admin::app.services.attribute-groups.edit.no-fields')
                            </p>

                            <p class="text-gray-400">
                                @lang('Admin::app.services.attribute-groups.edit.no-fields-info')
                            </p>
                        </div>

                        <div
                            class="secondary-button text-sm"
                            @click="openAddFieldModal"
                        >
                            @lang('Admin::app.services.attribute-groups.edit.add-field-btn')
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

            <x-admin::form.control-group>
                <x-admin::form.control-group.label>
                    @lang('Admin::app.services.attribute-groups.attribute-group-fields.default-value')
                </x-admin::form.control-group.label>

                <x-admin::form.control-group.control
                    type="text"
                    name="default_value"
                    v-model="selectedField.default_value"
                    placeholder="{{ trans('Admin::app.services.attribute-groups.attribute-group-fields.default-value') }}"
                />

                <x-admin::form.control-group.error control-name="default_value" />
            </x-admin::form.control-group>

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
                </div>
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

            app.component('v-attribute-group-edit', {
                template: '#v-attribute-group-edit-template',

                props: {
                    attributeGroup: {
                        type: Object,
                        required: true
                    },
                    attributeTypes: {
                        type: Array,
                        required: true
                    },
                    locales: {
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
                    }
                },

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
                            service_attribute_type_id: field.service_attribute_type_id ?? '',
                            labels,
                            sort_order: field.sort_order ?? 0,
                            is_required: normalizeBoolean(field.is_required),
                            default_value: field.default_value ?? '',
                            validation_option: parsedValidation.option,
                            validation_regex: parsedValidation.regex,
                            validation_custom: parsedValidation.custom,
                            validation_rules: parsedValidation.value,
                        };
                    };

                    const initialFields = this.attributeGroup.fields
                        ? this.attributeGroup.fields
                            .map(field => normalizeField(field))
                            .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
                        : [];

                    const emptyField = normalizeField({
                        id: null,
                        service_attribute_type_id: '',
                        labels: {},
                        translations: [],
                        sort_order: initialFields.length,
                        is_required: false,
                        default_value: '',
                        validation_rules: '',
                    });

                    return {
                        fields: initialFields,
                        locales: this.locales,
                        attributeTypesList: this.attributeTypes || [],
                        selectedFieldIndex: null,
                        selectedField: emptyField,
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
                },

                methods: {
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
                        this.$refs.updateCreateFieldModal.open();
                    },

                    openEditFieldModal(index) {
                        const field = this.fields[index];
                        this.selectedFieldIndex = index;

                        const parsedValidation = parseValidationRule(field.validation_rules, this.validationOptionsList);

                        this.selectedField = {
                            id: field.id,
                            uid: field.uid,
                            service_attribute_type_id: field.service_attribute_type_id,
                            labels: JSON.parse(JSON.stringify(field.labels)),
                            sort_order: field.sort_order,
                            is_required: normalizeBoolean(field.is_required),
                            default_value: field.default_value ?? '',
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

                        this.$refs.updateCreateFieldModal.open();
                    },

                    updateOrCreateField() {
                        const validationRule = formatValidationRuleValue(this.selectedField);

                        this.selectedField.validation_rules = validationRule;

                        const fieldPayload = {
                            id: this.selectedField.id ?? null,
                            uid: this.selectedField.uid ?? this.generateUid(),
                            service_attribute_type_id: this.selectedField.service_attribute_type_id,
                            labels: JSON.parse(JSON.stringify(this.selectedField.labels)),
                            sort_order: this.selectedField.sort_order ?? 0,
                            is_required: normalizeBoolean(this.selectedField.is_required),
                            default_value: this.selectedField.default_value ?? '',
                            validation_option: this.selectedField.validation_option || '',
                            validation_regex: this.selectedField.validation_regex || '',
                            validation_custom: this.selectedField.validation_custom || '',
                            validation_rules: validationRule,
                        };

                        if (this.selectedFieldIndex === null) {
                            fieldPayload.uid = this.generateUid();
                            this.fields.push(fieldPayload);
                        } else {
                            this.fields.splice(this.selectedFieldIndex, 1, {
                                ...this.fields[this.selectedFieldIndex],
                                ...fieldPayload,
                            });
                        }

                        this.$refs.updateCreateFieldModal.close();

                        this.$nextTick(() => {
                            this.recalculateSortOrders();
                            this.resetFieldForm();
                        });
                    },

                    removeField(field, index) {
                        if (field.id) {
                            this.$emitter.emit('open-confirm-modal', {
                                agree: () => {
                                    this.$axios.delete(`/admin/attribute-groups/${this.attributeGroup.id}/fields/${field.id}`)
                                        .then(() => {
                                            this.fields.splice(index, 1);
                                            this.recalculateSortOrders();
                                            this.$emitter.emit('add-flash', { type: 'success', message: '@lang('Admin::app.services.attribute-groups.attribute-group-fields.delete-success')' });
                                        })
                                        .catch(error => {
                                            this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Error deleting field' });
                                        });
                                }
                            });
                        } else {
                            this.fields.splice(index, 1);
                            this.recalculateSortOrders();
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
                        this.selectedField.default_value = attributeType.default_value ?? '';

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
</x-admin::layouts>


