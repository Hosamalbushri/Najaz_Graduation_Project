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

                                                    <p v-if="getAttributeTypeInfo(element.service_attribute_type_id)?.is_required">
                                                        <strong>@lang('Admin::app.services.attribute-types.index.datagrid.is-required'):</strong>
                                                        @lang('Admin::app.common.yes')
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
                                                    v-model.trim="selectedField.labels[locale.code]"
                                                    ::placeholder="locale.name"
                                                    ::label="`${uiTexts.field_label} (${locale.name})`"
                                                />

                                                <x-admin::form.control-group.error ::control-name="`labels[${locale.code}]`" />
                                            </x-admin::form.control-group>
                                        </div>
                                    </div>
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
                    }
                },

                data() {
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

                        return {
                            id: field.id ?? null,
                            uid: `field_${field.id ?? Math.random().toString(36).slice(2, 11)}`,
                            service_attribute_type_id: field.service_attribute_type_id ?? '',
                            labels,
                            sort_order: field.sort_order ?? 0,
                        };
                    };

                    const initialFields = this.attributeGroup.fields
                        ? this.attributeGroup.fields
                            .map(field => normalizeField(field))
                            .sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0))
                        : [];

                    return {
                        fields: initialFields,
                        locales: this.locales,
                        attributeTypesList: this.attributeTypes || [],
                        selectedFieldIndex: null,
                        selectedField: normalizeField({
                            id: null,
                            service_attribute_type_id: '',
                            labels: {},
                            translations: [],
                            sort_order: initialFields.length,
                        }),
                        currentLocale: '{{ app()->getLocale() }}',
                        defaultFieldName: "{{ __('Admin::app.services.attribute-groups.edit.field-default-name') }}",
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

                methods: {
                    openAddFieldModal() {
                        this.selectedFieldIndex = null;
                        this.selectedField = this.getEmptyField();
                        this.$refs.updateCreateFieldModal.open();
                    },

                    openEditFieldModal(index) {
                        const field = this.fields[index];
                        this.selectedFieldIndex = index;
                        this.selectedField = {
                            id: field.id,
                            uid: field.uid,
                            service_attribute_type_id: field.service_attribute_type_id,
                            labels: JSON.parse(JSON.stringify(field.labels)),
                            sort_order: field.sort_order,
                        };
                        this.$refs.updateCreateFieldModal.open();
                    },

                    updateOrCreateField() {
                        const fieldPayload = {
                            id: this.selectedField.id ?? null,
                            uid: this.selectedField.uid ?? this.generateUid(),
                            service_attribute_type_id: this.selectedField.service_attribute_type_id,
                            labels: JSON.parse(JSON.stringify(this.selectedField.labels)),
                            sort_order: this.selectedField.sort_order ?? 0,
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


