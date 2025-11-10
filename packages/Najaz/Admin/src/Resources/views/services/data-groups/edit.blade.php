<x-admin::layouts>
    <x-slot:title>
        @lang('Admin::app.services.data-groups.edit.title')
    </x-slot>

    <x-admin::form
        :action="route('admin.data-groups.update', $dataGroup->id)"
        method="PUT"
        enctype="multipart/form-data"
    >
        <!-- Actions Buttons -->
        <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
            <p class="text-xl font-bold text-gray-800 dark:text-white">
                @lang('Admin::app.services.data-groups.edit.title')
            </p>

            <div class="flex items-center gap-x-2.5">
                <!-- Back Button -->
                <a
                    href="{{ route('admin.data-groups.index') }}"
                    class="transparent-button hover:bg-gray-200 dark:text-white dark:hover:bg-gray-800"
                >
                    @lang('Admin::app.services.data-groups.edit.back-btn')
                </a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >
                    @lang('Admin::app.services.data-groups.edit.save-btn')
                </button>
            </div>
        </div>

        <v-data-group-edit
            :data-group="{{ $dataGroup->toJson() }}"
            :field-types="{{ $fieldTypes->toJson() }}"
        >
            <x-admin::shimmer.catalog.attributes />
        </v-data-group-edit>
    </x-admin::form>

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-data-group-edit-template">
            <div class="mt-3.5">
                <!-- Data Group Info -->
                <div class="flex gap-2.5 max-xl:flex-wrap">
                    <!-- Left: Name & Description -->
                    <div class="flex flex-1 flex-col gap-2 overflow-auto max-xl:flex-auto">
                        <div class="box-shadow rounded bg-white p-4 dark:bg-gray-900">
                            @foreach (core()->getAllLocales() as $locale)
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        {{ __('Admin::app.services.data-groups.edit.name'). ' (' . strtoupper($locale->code) . ')' }}
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        :name="'name[' . $locale->code . ']'"
                                        :value="old('name.' . $locale->code, $dataGroup->translate($locale->code)?->name ?? '')"
                                        :placeholder="$locale->name"
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        {{ __('Admin::app.services.data-groups.edit.description') .' (' . strtoupper($locale->code) . ')' }}
                                    </x-admin::form.control-group.label>
                                    
                                    <x-admin::form.control-group.control
                                        type="textarea"
                                        :name="'description[' . $locale->code . ']'"
                                        :value="old('description.' . $locale->code, $dataGroup->translate($locale->code)?->description ?? '')"
                                    />
                                </x-admin::form.control-group>
                            @endforeach
                        </div>
                    </div>

                    <!-- Right: Code & Sort Order -->
                    <div class="flex w-[360px] max-w-full flex-col gap-2">
                        <x-admin::accordion>
                            <x-slot:header>
                                <p class="p-2.5 text-base font-semibold text-gray-800 dark:text-white">
                                    @lang('Admin::app.services.data-groups.edit.general')
                                </p>
                            </x-slot>
                            <x-slot:content>
                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label class="required">
                                        @lang('Admin::app.services.data-groups.edit.code')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="text"
                                        name="code"
                                        :value="old('code', $dataGroup->code)"
                                        :placeholder="trans('Admin::app.services.data-groups.edit.code')"
                                        readonly
                                    />
                                </x-admin::form.control-group>

                                <x-admin::form.control-group>
                                    <x-admin::form.control-group.label>
                                        @lang('Admin::app.services.data-groups.edit.sort-order')
                                    </x-admin::form.control-group.label>

                                    <x-admin::form.control-group.control
                                        type="number"
                                        name="sort_order"
                                        :value="old('sort_order', $dataGroup->sort_order)"
                                        :placeholder="trans('Admin::app.services.data-groups.edit.sort-order')"
                                    />
                                </x-admin::form.control-group>
                            </x-slot:content>
                        </x-admin::accordion>
                    </div>
                </div>

                <!-- Fields Section -->
                <div class="mt-4 box-shadow rounded bg-white p-4 dark:bg-gray-900">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-base font-semibold text-gray-800 dark:text-white">
                            @lang('Admin::app.services.data-groups.edit.fields-title')
                        </p>

                        <button
                            type="button"
                            @click="addField"
                            class="primary-button"
                        >
                            @lang('Admin::app.services.data-groups.edit.add-field-btn')
                        </button>
                    </div>

                    <!-- Fields List -->
                    <div v-if="fields.length > 0" class="space-y-4">
                        <div
                            v-for="(field, index) in fields"
                            :key="field.id || 'new-' + index"
                            class="rounded border border-gray-200 p-4 dark:border-gray-800"
                        >
                            <!-- Hidden input for field ID -->
                            <input
                                v-if="field.id"
                                type="hidden"
                                :name="`fields[${index}][id]`"
                                :value="field.id"
                            />

                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <!-- Field Type -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label class="required">
                                            @lang('Admin::app.services.data-groups.data-group-fields.field-type')
                                        </x-admin::form.control-group.label>

                                        <select
                                            v-model="field.service_field_type_id"
                                            :name="`fields[${index}][service_field_type_id]`"
                                            class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                            :disabled="field.id"
                                            @change="onFieldTypeChange(index)"
                                        >
                                            <option value="">@lang('Admin::app.services.data-groups.data-group-fields.select-field-type')</option>
                                            <option
                                                v-for="fieldType in availableFieldTypes(index)"
                                                :key="fieldType.id"
                                                :value="fieldType.id"
                                            >
                                                @{{ getFieldTypeName(fieldType) }}
                                            </option>
                                        </select>
                                    </x-admin::form.control-group>

                                    <!-- Field Label -->
                                    <div v-for="locale in locales" :key="locale.code">
                                        <x-admin::form.control-group>
                                            <x-admin::form.control-group.label class="required">
                                                @lang('Admin::app.services.data-groups.edit.field-label') (@{{ locale.name }})
                                            </x-admin::form.control-group.label>

                                            <input
                                                type="text"
                                                :name="`fields[${index}][label][${locale.code}]`"
                                                v-model="field.labels[locale.code]"
                                                class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                                :placeholder="locale.name"
                                            />
                                        </x-admin::form.control-group>
                                    </div>

                                    <!-- Sort Order -->
                                    <x-admin::form.control-group>
                                        <x-admin::form.control-group.label>
                                            @lang('Admin::app.services.data-groups.edit.sort-order')
                                        </x-admin::form.control-group.label>

                                        <input
                                            type="number"
                                            :name="`fields[${index}][sort_order]`"
                                            v-model="field.sort_order"
                                            class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:border-gray-400 dark:focus:border-gray-400"
                                        />
                                    </x-admin::form.control-group>
                                </div>

                                <!-- Delete Button -->
                                <button
                                    type="button"
                                    @click="removeField(index)"
                                    class="icon-cancel text-2xl text-red-600 hover:text-red-700 dark:text-red-400"
                                >
                                </button>
                            </div>

                            <!-- Field Info (if field type selected) -->
                            <div v-if="field.service_field_type_id" class="mt-2 rounded bg-gray-50 p-2 text-xs text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                <template v-if="getFieldTypeInfo(field.service_field_type_id)">
                                    <p><strong>@lang('Admin::app.services.data-groups.data-group-fields.field-type'):</strong> @{{ getFieldTypeInfo(field.service_field_type_id).type }}</p>
                                    <p v-if="getFieldTypeInfo(field.service_field_type_id) && getFieldTypeInfo(field.service_field_type_id).validation">
                                        <strong>@lang('Admin::app.services.data-groups.field-types.index.datagrid.validation'):</strong> @{{ getFieldTypeInfo(field.service_field_type_id).validation }}
                                    </p>
                                    <p v-if="getFieldTypeInfo(field.service_field_type_id) && getFieldTypeInfo(field.service_field_type_id).is_required">
                                        <strong>@lang('Admin::app.services.field-types.index.datagrid.is-required'):</strong> @lang('Admin::app.services.field-types.index.datagrid.yes')
                                    </p>
                                    <p v-if="getFieldTypeInfo(field.service_field_type_id) && getFieldTypeInfo(field.service_field_type_id).is_unique">
                                        <strong>@lang('Admin::app.services.field-types.index.datagrid.is-unique'):</strong> @lang('Admin::app.services.field-types.index.datagrid.yes')
                                    </p>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="rounded border border-dashed border-gray-300 p-8 text-center dark:border-gray-700">
                        <p class="text-gray-500 dark:text-gray-400">
                            @lang('Admin::app.services.data-groups.edit.no-fields')
                        </p>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-data-group-edit', {
                template: '#v-data-group-edit-template',

                props: {
                    dataGroup: {
                        type: Object,
                        required: true
                    },
                    fieldTypes: {
                        type: Array,
                        required: true
                    }
                },

                data() {
                    return {
                        fields: this.dataGroup.fields ? this.dataGroup.fields.map(field => ({
                            id: field.id,
                            service_field_type_id: field.service_field_type_id || null,
                            labels: field.translations ? field.translations.reduce((acc, trans) => {
                                acc[trans.locale] = trans.label;
                                return acc;
                            }, {}) : {},
                            sort_order: field.sort_order || 0
                        })) : [],
                        locales: @json(core()->getAllLocales()->toArray()),
                        fieldTypesList: this.fieldTypes || []
                    }
                },

                methods: {
                    addField() {
                        const labels = {};
                        this.locales.forEach(locale => {
                            labels[locale.code] = '';
                        });

                        this.fields.push({
                            id: null,
                            service_field_type_id: null,
                            labels: labels,
                            sort_order: this.fields.length
                        });
                    },

                    removeField(index) {
                        const field = this.fields[index];
                        
                        if (field.id) {
                            // Delete existing field via AJAX
                            if (confirm('@lang('Admin::app.services.data-groups.edit.delete-field-confirm')')) {
                                this.$axios.delete(`/admin/data-groups/${this.dataGroup.id}/fields/${field.id}`)
                                    .then(() => {
                                        this.fields.splice(index, 1);
                                        this.$emitter.emit('add-flash', { type: 'success', message: '@lang('Admin::app.data-group-fields.delete-success')' });
                                    })
                                    .catch(error => {
                                        this.$emitter.emit('add-flash', { type: 'error', message: error.response?.data?.message || 'Error deleting field' });
                                    });
                            }
                        } else {
                            // Remove new field
                            this.fields.splice(index, 1);
                        }
                    },

                    availableFieldTypes(currentIndex) {
                        const usedFieldTypeIds = this.fields
                            .filter((field, index) => index !== currentIndex && field.service_field_type_id)
                            .map(field => field.service_field_type_id);

                        return this.fieldTypesList.filter(fieldType => 
                            !usedFieldTypeIds.includes(fieldType.id)
                        );
                    },

                    onFieldTypeChange(index) {
                        const field = this.fields[index];
                        const fieldType = this.getFieldTypeInfo(field.service_field_type_id);
                        
                        if (fieldType && !field.id) {
                            // Auto-fill label with field type name if label is empty
                            this.locales.forEach(locale => {
                                if (!field.labels[locale.code] || field.labels[locale.code] === '') {
                                    if (fieldType.translations && Array.isArray(fieldType.translations)) {
                                        const translation = fieldType.translations.find(t => t.locale === locale.code);
                                        if (translation && translation.name) {
                                            field.labels[locale.code] = translation.name;
                                        } else if (fieldType.translations.length > 0 && fieldType.translations[0].name) {
                                            // Fallback to first available translation
                                            field.labels[locale.code] = fieldType.translations[0].name;
                                        }
                                    }
                                }
                            });
                        }
                    },

                    getFieldTypeInfo(fieldTypeId) {
                        try {
                            if (!fieldTypeId) return null;
                            if (!this.fieldTypesList || !Array.isArray(this.fieldTypesList)) return null;
                            return this.fieldTypesList.find(ft => ft && ft.id === fieldTypeId) || null;
                        } catch (e) {
                            console.error('Error in getFieldTypeInfo:', e);
                            return null;
                        }
                    },

                    getFieldTypeName(fieldType) {
                        if (!fieldType) return '';
                        if (!fieldType.translations || !Array.isArray(fieldType.translations)) return '';
                        const currentLocale = '{{ app()->getLocale() }}';
                        const translation = fieldType.translations.find(t => t.locale === currentLocale);
                        if (translation && translation.name) {
                            return translation.name;
                        }
                        if (fieldType.translations.length > 0 && fieldType.translations[0].name) {
                            return fieldType.translations[0].name;
                        }
                        return '';
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
