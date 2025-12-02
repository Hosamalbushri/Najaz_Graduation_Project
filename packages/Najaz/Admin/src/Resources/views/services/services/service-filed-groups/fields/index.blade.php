@include('admin::services.services.service-filed-groups.fields.create')
@include('admin::services.services.service-filed-groups.fields.edit')
@include('admin::services.services.service-filed-groups.fields.options.index')
@pushOnce('scripts')
    <script
            type="text/x-template"
            id="v-service-data-group-fields-display-template"
    >
        <div>
            <div class="space-y-2">
                <div
                        v-if="sortedFields.length"
                        class="grid gap-3"
                >
                    <draggable
                            ghost-class="draggable-ghost"
                            v-bind="{ animation: 200 }"
                            handle=".icon-drag"
                            :list="fields"
                            item-key="uid"
                            @start="onFieldDragStart"
                            @end="onFieldDragChange"
                    >
                        <template #item="{ element: field, index: fieldIndex }">
                            <!-- Unified field design for all fields (with/without options) -->
                            <div
                                    class="mb-3 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900 transition-all hover:shadow-md hover:border-gray-300 dark:hover:border-gray-700 overflow-hidden"
                            >
                                <!-- Field Header Section -->
                                <div class="flex items-center justify-between gap-4 p-4">
                                    <div class="flex items-center gap-3 flex-1 min-w-0">
                                        <i class="icon-drag cursor-grab text-lg text-gray-400 transition-all hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 flex-shrink-0 hover:scale-110"></i>

                                        <div class="flex items-center gap-2.5 flex-shrink-0">
                                            <div 
                                                :class="fieldRequiresOptions(field) && pivotId && field.id
                                                    ? 'bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 border-purple-100 dark:border-purple-800/50'
                                                    : 'bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border-blue-100 dark:border-blue-800/50'"
                                                class="flex items-center justify-center w-9 h-9 rounded-lg border"
                                            >
                                                <i 
                                                    :class="fieldRequiresOptions(field) && pivotId && field.id
                                                        ? 'icon-list text-purple-600 dark:text-purple-400'
                                                        : 'icon-attribute text-blue-600 dark:text-blue-400'"
                                                    class="text-base"
                                                ></i>
                                            </div>
                                        </div>

                                        <div class="flex flex-col gap-1.5 min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <p
                                                    v-if="getFieldDisplayName(field)"
                                                    class="text-base font-semibold text-gray-800 dark:text-white break-words"
                                                >
                                                    @{{ getFieldDisplayName(field) }}
                                                </p>

                                                <i
                                                    v-if="normalizeBoolean(field.is_required)"
                                                    class="icon-star text-red-600 dark:text-red-400 text-sm flex-shrink-0"
                                                    title="@lang('Admin::app.services.attribute-groups.attribute-group-fields.is-required')"
                                                ></i>
                                            </div>

                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span
                                                    v-if="!hasFieldTranslationForCurrentLocale(field) && getFirstAvailableFieldTranslation(field)"
                                                    class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-800 dark:text-gray-200"
                                                >
                                                    @{{ getFirstAvailableFieldTranslation(field) }}
                                                </span>

                                                <span
                                                    v-if="field.options && field.options.length && !fieldRequiresOptions(field)"
                                                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full bg-purple-100 dark:bg-purple-900/40 text-xs font-medium text-purple-700 dark:text-purple-300"
                                                >
                                                    @{{ field.options.length }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span
                                                v-if="fieldRequiresOptions(field) && pivotId && field.id"
                                                class="inline-flex items-center gap-1 cursor-pointer text-green-600 dark:text-green-400 transition-all hover:text-green-700 dark:hover:text-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 px-2.5 py-1.5 rounded-md text-sm font-medium whitespace-nowrap"
                                            @click="openAddOptionModalForField(field)"
                                        >
                                            @lang('Admin::app.services.services.groups.fields.options.add-option')
                                        </span>

                                        <span
                                                v-if="pivotId && field.id"
                                                class="inline-flex items-center gap-1 cursor-pointer text-blue-600 dark:text-blue-400 transition-all hover:text-blue-700 dark:hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 px-2.5 py-1.5 rounded-md text-sm font-medium whitespace-nowrap"
                                            @click="openEditFieldModal(field)"
                                        >
                                            @lang('Admin::app.services.attribute-groups.edit.edit-field-btn')
                                        </span>

                                        <span
                                                v-if="pivotId && field.id"
                                                class="inline-flex items-center gap-1 cursor-pointer text-red-600 dark:text-red-400 transition-all hover:text-red-700 dark:hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 px-2.5 py-1.5 rounded-md text-sm font-medium whitespace-nowrap"
                                            @click="deleteField(field)"
                                        >
                                            @lang('Admin::app.services.attribute-groups.edit.delete-field-btn')
                                        </span>
                                    </div>
                                </div>

                                <!-- Options Section (only for fields with options) -->
                                <div
                                        v-if="fieldRequiresOptions(field) && pivotId && field.id"
                                        class="border-t border-gray-200 dark:border-gray-700 bg-gradient-to-br from-gray-50/80 to-purple-50/30 dark:from-gray-800/50 dark:to-purple-900/10"
                                >
                                    <div class="p-4">
                                        <!-- Options Display Component -->
                                        <v-service-data-group-field-options-display
                                                :service-id="serviceId"
                                                :pivot-id="pivotId"
                                                :field="field"
                                                :locales="locales"
                                                :current-locale="currentLocale"
                                                @option-created="(optionData) => onOptionCreated(optionData)"
                                                @option-updated="(optionData) => onOptionUpdated(optionData)"
                                                @option-deleted="(optionId) => onOptionDeleted(optionId)"
                                                @option-reordered="() => onOptionReordered()"
                                                :ref="`optionsDisplay${fieldIndex}`"
                                        ></v-service-data-group-field-options-display>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </draggable>

                    <div
                            v-if="hasFieldOrderChanged"
                            class="mt-4 flex items-center justify-end gap-2 rounded border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20"
                    >
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            @lang('Admin::app.services.services.groups.fields.order-changed')
                        </p>
                        <x-admin::button
                                button-type="button"
                                class="primary-button text-sm"
                                :title="trans('Admin::app.services.services.groups.fields.save-order')"
                                @click="saveFieldOrder"
                        />
                        <x-admin::button
                                button-type="button"
                                class="secondary-button text-sm"
                                :title="trans('Admin::app.common.cancel')"
                                @click="cancelFieldOrderChange"
                        />
                    </div>
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

                    <x-admin::button
                            v-if="pivotId"
                            button-type="button"
                            class="secondary-button text-sm"
                            @click="openCreateFieldModal"
                    >
                        @lang('Admin::app.services.services.groups.fields.edit.add-field-btn')
                    </x-admin::button>
                </div>
            </div>

            <!-- Field Create Component -->
            <v-service-data-group-field-create
                    ref="createFieldComponent"
                    :service-id="serviceId"
                    :pivot-id="pivotId"
                    :attribute-types="attributeTypes"
                    :validations="validations"
                    :validation-labels="validationLabels"
                    :file-extensions="fileExtensionsComputed"
                    :locales="locales"
                    :sort-order="currentFieldSortOrder"
                    @field-created="onFieldCreated"
            ></v-service-data-group-field-create>

            <!-- Field Edit Component -->
            <v-service-data-group-field-edit
                    ref="editFieldComponent"
                    :service-id="serviceId"
                    :pivot-id="pivotId"
                    :field="editingField"
                    :attribute-types="attributeTypes"
                    :validations="validations"
                    :validation-labels="validationLabels"
                    :file-extensions="fileExtensionsComputed"
                    :locales="locales"
                    @field-updated="onFieldUpdated"
            ></v-service-data-group-field-edit>
        </div>
    </script>

    <script type="module">
        app.component('v-service-data-group-fields-display', {
            template: '#v-service-data-group-fields-display-template',

            props: {
                serviceId: {
                    type: [Number, String],
                    required: true,
                },
                pivotId: {
                    type: [Number, String],
                    required: true,
                },
                fields: {
                    type: Array,
                    required: true,
                    default: () => [],
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
                currentLocale: {
                    type: String,
                    default: '{{ app()->getLocale() }}',
                },
            },

            emits: [
                'field-created',
                'field-updated',
                'field-deleted',
                'field-reordered',
                'option-created',
                'option-updated',
                'option-deleted',
                'option-reordered',
            ],

            data() {
                return {
                    editingField: null,
                    currentFieldSortOrder: 0,
                    fieldOrderOriginal: null,
                    hasFieldOrderChanged: false,
                };
            },

            computed: {
                fileExtensionsComputed() {
                    return Array.isArray(this.fileExtensions) ? this.fileExtensions : [];
                },
                sortedFields() {
                    const fieldsArray = Array.isArray(this.fields) ? this.fields : [];
                    if (fieldsArray.length === 0) {
                        return [];
                    }

                    // Check if already sorted
                    let isSorted = true;
                    for (let i = 1; i < fieldsArray.length; i++) {
                        if ((fieldsArray[i - 1].sort_order || 0) > (fieldsArray[i].sort_order || 0)) {
                            isSorted = false;
                            break;
                        }
                    }
                    if (!isSorted) {
                        fieldsArray.sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0));
                    }
                    return fieldsArray;
                },
            },

            methods: {
                normalizeBoolean(value) {
                    if (typeof value === 'string') {
                        return ['1', 'true', 'yes', 'on'].includes(value.toLowerCase());
                    }
                    if (typeof value === 'number') {
                        return value === 1;
                    }
                    return !!value;
                },

                getFieldDisplayName(field) {
                    if (!field) return '';
                    
                    // Get label for current locale from field.labels object
                    if (field.labels && typeof field.labels === 'object') {
                        const label = field.labels[this.currentLocale];
                        if (label && label.trim()) {
                            return label;
                        }
                    }
                    
                    // Fallback to field.label (single string) only, don't show code
                    return field.label || '';
                },

                hasFieldTranslationForCurrentLocale(field) {
                    if (!field) return false;
                    
                    if (field.labels && typeof field.labels === 'object') {
                        const label = field.labels[this.currentLocale];
                        return label && label.trim().length > 0;
                    }
                    
                    // If field.label exists as string, consider it as current locale
                    return !!(field.label && field.label.trim());
                },

                getFirstAvailableFieldTranslation(field) {
                    if (!field || !field.labels || typeof field.labels !== 'object') {
                        return '';
                    }
                    
                    // Find first available translation (excluding current locale)
                    for (const localeCode in field.labels) {
                        if (localeCode !== this.currentLocale) {
                            const translation = field.labels[localeCode];
                            if (translation && translation.trim()) {
                                return translation;
                            }
                        }
                    }
                    
                    return '';
                },

                getFieldTypeName(field) {
                    if (!field || !field.service_attribute_type_id) {
                        return '';
                    }
                    const attributeType = this.getAttributeTypeInfo(field.service_attribute_type_id);
                    if (!attributeType) {
                        return '';
                    }
                    // Try to get name from translations or use code
                    if (attributeType.name) {
                        return attributeType.name;
                    }
                    if (attributeType.translations && Array.isArray(attributeType.translations)) {
                        const translation = attributeType.translations.find(t => t.locale === this.currentLocale);
                        if (translation && translation.name) {
                            return translation.name;
                        }
                        if (attributeType.translations.length > 0 && attributeType.translations[0].name) {
                            return attributeType.translations[0].name;
                        }
                    }
                    return attributeType.code || '';
                },

                getFieldAvailableLocales(field) {
                    if (!field || !field.labels || typeof field.labels !== 'object') {
                        return [];
                    }
                    
                    const availableLocales = [];
                    const localesArray = Array.isArray(this.locales) ? this.locales : [];
                    
                    for (const localeCode in field.labels) {
                        const label = field.labels[localeCode];
                        if (label && label.trim()) {
                            const locale = localesArray.find(l => l.code === localeCode);
                            if (locale && locale.name) {
                                availableLocales.push(locale.name);
                            } else {
                                availableLocales.push(localeCode.toUpperCase());
                            }
                        }
                    }
                    
                    return availableLocales;
                },

                fieldRequiresOptions(field) {
                    if (!field || !field.service_attribute_type_id) {
                        return false;
                    }
                    const attributeType = this.getAttributeTypeInfo(field.service_attribute_type_id);
                    if (!attributeType) {
                        return false;
                    }
                    const optionTypes = ['select', 'multiselect', 'radio', 'checkbox'];
                    return optionTypes.includes(attributeType.type);
                },

                getAttributeTypeInfo(attributeTypeId) {
                    try {
                        if (!attributeTypeId) return null;
                        if (!this.attributeTypes || !Array.isArray(this.attributeTypes)) return null;
                        return this.attributeTypes.find(at => at && at.id === attributeTypeId) || null;
                    } catch (e) {
                        console.error('Error in getAttributeTypeInfo:', e);
                        return null;
                    }
                },


                openCreateFieldModal() {
                    if (!this.pivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.group-id-required')",
                        });
                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    // Set sort order to last
                    const fieldsArray = Array.isArray(this.fields) ? this.fields : [];
                    this.currentFieldSortOrder = fieldsArray.length;

                    this.$refs.createFieldComponent.openModal(this.currentFieldSortOrder);
                },

                onFieldCreated(fieldData) {
                    if (fieldData) {
                        this.$emit('field-created', fieldData);
                    }
                },

                openEditFieldModal(field) {
                    if (!field || !field.id || !this.pivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    this.editingField = field;
                    this.$refs.editFieldComponent.openModal(field);
                },

                onFieldUpdated(fieldData) {
                    if (fieldData) {
                        this.$emit('field-updated', fieldData);
                    }
                    this.editingField = null;
                },

                async deleteField(field) {
                    if (!field || !field.id || !this.pivotId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.field-id-required')",
                        });
                        return;
                    }

                    if (!this.serviceId) {
                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: "@lang('Admin::app.services.services.attribute-groups.service-id-required')",
                        });
                        return;
                    }

                    this.$emitter.emit('open-confirm-modal', {
                        agree: async () => {
                            try {
                                const deleteUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/${field.id}`;
                                await this.$axios.delete(deleteUrl);

                                this.$emit('field-deleted', field.id);

                                this.$emitter.emit('add-flash', {
                                    type: 'success',
                                    message: "@lang('Admin::app.services.services.groups.fields.delete-success')",
                                });
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
                },

                onFieldDragStart() {
                    if (!this.fieldOrderOriginal) {
                        const fieldsArray = Array.isArray(this.fields) ? this.fields : [];
                        this.fieldOrderOriginal = fieldsArray.map(f => ({
                            id: f.id,
                            uid: f.uid,
                            sort_order: f.sort_order
                        }));
                    }
                },

                onFieldDragChange() {
                    const fieldsArray = Array.isArray(this.fields) ? this.fields : [];

                    if (!this.fieldOrderOriginal) {
                        return;
                    }

                    // Check if order actually changed
                    let orderChanged = false;
                    for (let i = 0; i < fieldsArray.length; i++) {
                        if (fieldsArray[i].id !== this.fieldOrderOriginal[i]?.id) {
                            orderChanged = true;
                            break;
                        }
                    }

                    if (!orderChanged) {
                        delete this.fieldOrderOriginal;
                        this.hasFieldOrderChanged = false;
                        return;
                    }

                    // Recalculate sort orders locally
                    fieldsArray.forEach((field, index) => {
                        field.sort_order = index;
                    });

                    // Mark that order has changed
                    this.hasFieldOrderChanged = true;
                },

                async saveFieldOrder() {
                    if (!this.serviceId || !this.pivotId) {
                        return;
                    }

                    const fieldsArray = Array.isArray(this.fields) ? this.fields : [];
                    const fieldIds = fieldsArray
                        .filter(field => field.id)
                        .map(field => field.id);

                    if (fieldIds.length === 0) {
                        return;
                    }

                    try {
                        const reorderUrl = `{{ url('admin/services') }}/${this.serviceId}/groups/${this.pivotId}/fields/reorder`;
                        await this.$axios.post(reorderUrl, {
                            field_ids: fieldIds,
                        });

                        this.hasFieldOrderChanged = false;
                        this.fieldOrderOriginal = null;

                        this.$emit('field-reordered');

                        this.$emitter.emit('add-flash', {
                            type: 'success',
                            message: "@lang('Admin::app.services.services.groups.fields.reorder-success')",
                        });
                    } catch (error) {
                        const message = error.response?.data?.message ||
                            error.message ||
                            "@lang('Admin::app.services.services.groups.fields.reorder-error')";

                        this.$emitter.emit('add-flash', {
                            type: 'error',
                            message: message,
                        });
                    }
                },

                cancelFieldOrderChange() {
                    if (!this.fieldOrderOriginal) {
                        return;
                    }

                    const fieldsArray = Array.isArray(this.fields) ? this.fields : [];
                    const originalOrder = this.fieldOrderOriginal;
                    const fieldsMap = new Map();
                    fieldsArray.forEach(field => {
                        fieldsMap.set(field.id, field);
                    });

                    // Sort fields back to original order
                    const sortedFields = originalOrder
                        .map(item => fieldsMap.get(item.id))
                        .filter(Boolean);

                    // Add any new fields that weren't in original order
                    fieldsArray.forEach(field => {
                        if (!originalOrder.find(item => item.id === field.id)) {
                            sortedFields.push(field);
                        }
                    });

                    // Restore original sort orders
                    sortedFields.forEach((field, index) => {
                        const original = originalOrder.find(item => item.id === field.id);
                        if (original) {
                            field.sort_order = original.sort_order;
                        } else {
                            field.sort_order = index;
                        }
                    });

                    // Emit update to parent
                    this.$emit('fields-order-cancelled', sortedFields);

                    this.hasFieldOrderChanged = false;
                    this.fieldOrderOriginal = null;
                },

                openAddOptionModalForField(field) {
                    if (!field || !field.id) {
                        this.$emitter.emit('add-flash', {
                            type: 'warning',
                            message: "@lang('Admin::app.services.services.groups.fields.options.save-field-first')",
                        });
                        return;
                    }

                    if (!this.pivotId) {
                        return;
                    }

                    // Find the field index
                    const fieldsArray = Array.isArray(this.fields) ? this.fields : [];
                    const fieldIndex = fieldsArray.findIndex(f => f.id === field.id);
                    if (fieldIndex === -1) {
                        return;
                    }

                    // Find the options display component and trigger openCreateOptionModal
                    this.$nextTick(() => {
                        const refKey = `optionsDisplay${fieldIndex}`;
                        const optionsComponent = this.$refs[refKey];
                        if (optionsComponent) {
                            if (typeof optionsComponent.openCreateOptionModal === 'function') {
                                optionsComponent.openCreateOptionModal();
                            } else {
                                console.warn('openCreateOptionModal method not found on options component', optionsComponent);
                            }
                        } else {
                            console.warn('Options component not found with ref:', refKey, 'Available refs:', Object.keys(this.$refs));
                        }
                    });
                },

                onOptionCreated(optionData) {
                    // Options are handled by options component - just forward the event
                    this.$emit('option-created', optionData);
                },

                onOptionUpdated(optionData) {
                    // Options are handled by options component - just forward the event
                    this.$emit('option-updated', optionData);
                },

                onOptionDeleted(optionId) {
                    // Options are handled by options component - just forward the event
                    this.$emit('option-deleted', optionId);
                },

                onOptionReordered() {
                    // Options are handled by options component - just forward the event
                    this.$emit('option-reordered');
                },
            },
        });
    </script>
@endPushOnce

